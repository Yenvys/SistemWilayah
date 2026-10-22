<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\User;
use App\Models\Village;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PollWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_officer_can_create_poll_with_multiple_options_and_citizens_receive_notification(): void
    {
        $village = Village::create(['name' => 'Desa Maju', 'code' => 'DM01']);
        $rw = Rw::create(['village_id' => $village->id, 'number' => '01']);
        $rt = Rt::create(['rw_id' => $rw->id, 'number' => '05']);

        $rtOfficer = User::factory()->create([
            'role' => 'RT',
            'rt_id' => $rt->id,
            'rw_id' => $rw->id,
            'village_id' => $village->id,
        ]);

        $citizenInRt = User::factory()->create([
            'role' => 'WARGA',
            'rt_id' => $rt->id,
            'rw_id' => $rw->id,
            'village_id' => $village->id,
        ]);

        $citizenOtherRt = User::factory()->create([
            'role' => 'WARGA',
            'rt_id' => null,
            'rw_id' => $rw->id,
            'village_id' => $village->id,
        ]);

        $response = $this->actingAs($rtOfficer)->post(route('polls.store'), [
            'title' => 'Kerja Bakti Saluran Air',
            'description' => 'Menentukan jadwal kerja bakti akhir pekan',
            'level' => 'RT',
            'rt_id' => $rt->id,
            'rw_id' => $rw->id,
            'village_id' => $village->id,
            'options' => [
                'Sabtu Pagi (07.00 WIB)',
                'Minggu Pagi (07.00 WIB)',
                'Minggu Sore (15.30 WIB)',
            ],
        ]);

        $response->assertRedirect(route('polls.index'));

        $this->assertDatabaseHas('polls', [
            'title' => 'Kerja Bakti Saluran Air',
            'level' => 'RT',
            'rt_id' => $rt->id,
            'status' => 'ACTIVE',
        ]);

        $poll = Poll::where('title', 'Kerja Bakti Saluran Air')->first();
        $this->assertNotNull($poll);
        $this->assertCount(3, $poll->options);

        // Citizen in RT should receive notification
        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $citizenInRt->id,
            'type' => 'POLL',
            'reference_id' => $poll->id,
        ]);

        // Citizen outside this RT should NOT receive notification
        $this->assertDatabaseMissing('app_notifications', [
            'user_id' => $citizenOtherRt->id,
            'reference_id' => $poll->id,
        ]);
    }

    public function test_citizen_can_vote_in_active_poll_and_cannot_vote_twice(): void
    {
        $village = Village::create(['name' => 'Desa Sejahtera', 'code' => 'DS01']);
        $rw = Rw::create(['village_id' => $village->id, 'number' => '02']);
        $rt = Rt::create(['rw_id' => $rw->id, 'number' => '01']);

        $officer = User::factory()->create(['role' => 'ADMIN_DESA', 'village_id' => $village->id]);
        $citizen = User::factory()->create(['role' => 'WARGA', 'rt_id' => $rt->id, 'village_id' => $village->id]);

        $poll = Poll::create([
            'created_by' => $officer->id,
            'title' => 'Pembangunan Gapura Desa',
            'level' => 'DESA',
            'village_id' => $village->id,
            'status' => 'ACTIVE',
        ]);

        $optA = PollOption::create(['poll_id' => $poll->id, 'text' => 'Desain Minimalis Modern']);
        $optB = PollOption::create(['poll_id' => $poll->id, 'text' => 'Desain Etnik Tradisional']);

        // First vote should succeed
        $response = $this->actingAs($citizen)->post(route('polls.vote', $poll->id), [
            'option_id' => $optA->id,
        ]);
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('poll_votes', [
            'poll_id' => $poll->id,
            'poll_option_id' => $optA->id,
            'user_id' => $citizen->id,
        ]);

        // Second vote attempt should be rejected
        $response2 = $this->actingAs($citizen)->post(route('polls.vote', $poll->id), [
            'option_id' => $optB->id,
        ]);
        $response2->assertSessionHas('error', 'Anda sudah memberikan suara pada musyawarah ini.');

        // Total vote should still be 1
        $this->assertEquals(1, $poll->votes()->count());
    }

    public function test_officer_can_close_poll_and_citizens_receive_result_notification(): void
    {
        $village = Village::create(['name' => 'Desa Harmoni', 'code' => 'DH01']);
        $rw = Rw::create(['village_id' => $village->id, 'number' => '03']);
        $rt = Rt::create(['rw_id' => $rw->id, 'number' => '02']);

        $officer = User::factory()->create(['role' => 'RW', 'rw_id' => $rw->id, 'village_id' => $village->id]);
        $citizen1 = User::factory()->create(['role' => 'WARGA', 'rw_id' => $rw->id, 'rt_id' => $rt->id]);
        $citizen2 = User::factory()->create(['role' => 'WARGA', 'rw_id' => $rw->id, 'rt_id' => $rt->id]);

        $poll = Poll::create([
            'created_by' => $officer->id,
            'title' => 'Iuran Keamanan Lingkungan',
            'level' => 'RW',
            'rw_id' => $rw->id,
            'status' => 'ACTIVE',
        ]);

        $optA = PollOption::create(['poll_id' => $poll->id, 'text' => 'Rp 50.000 / KK']);
        $optB = PollOption::create(['poll_id' => $poll->id, 'text' => 'Rp 35.000 / KK']);

        // Citizen 1 & 2 vote for Opt B
        $this->actingAs($citizen1)->post(route('polls.vote', $poll->id), ['option_id' => $optB->id]);
        $this->actingAs($citizen2)->post(route('polls.vote', $poll->id), ['option_id' => $optB->id]);

        // Close poll
        $response = $this->actingAs($officer)->patch(route('polls.close', $poll->id));
        $response->assertRedirect(route('polls.show', $poll->id));
        $response->assertSessionHas('success');

        $this->assertEquals('CLOSED', $poll->fresh()->status);

        // Citizens should receive final result notification mentioning winning option
        $notification = AppNotification::where('user_id', $citizen1->id)
            ->where('title', 'like', '%Hasil Musyawarah%')
            ->first();

        $this->assertNotNull($notification);
        $this->assertStringContainsString('Rp 35.000 / KK', $notification->message);

        // Result should also be published to Announcements (Informasi Desa)
        $this->assertDatabaseHas('announcements', [
            'title' => 'Hasil Musyawarah: '.$poll->title,
        ]);

        // Citizen cannot vote after poll is closed
        $citizen3 = User::factory()->create(['role' => 'WARGA', 'rw_id' => $rw->id, 'rt_id' => $rt->id]);
        $responseVoteClosed = $this->actingAs($citizen3)->post(route('polls.vote', $poll->id), [
            'option_id' => $optA->id,
        ]);
        $responseVoteClosed->assertSessionHas('error', 'Musyawarah ini telah selesai.');
    }

    public function test_admin_desa_and_rw_polls_send_universal_notifications_to_all_citizens(): void
    {
        $village = Village::create(['name' => 'Desa Makmur', 'code' => 'DM02']);
        $rw1 = Rw::create(['village_id' => $village->id, 'number' => '01']);
        $rw2 = Rw::create(['village_id' => $village->id, 'number' => '02']);
        $rt1 = Rt::create(['rw_id' => $rw1->id, 'number' => '01']);
        $rt2 = Rt::create(['rw_id' => $rw2->id, 'number' => '02']);

        $adminDesa = User::factory()->create(['role' => 'ADMIN_DESA', 'village_id' => $village->id]);
        $rwOfficer = User::factory()->create(['role' => 'RW', 'rw_id' => $rw1->id, 'village_id' => $village->id]);

        $citizenRt1 = User::factory()->create(['role' => 'WARGA', 'rt_id' => $rt1->id, 'rw_id' => $rw1->id, 'village_id' => $village->id]);
        $citizenRt2 = User::factory()->create(['role' => 'WARGA', 'rt_id' => $rt2->id, 'rw_id' => $rw2->id, 'village_id' => $village->id]);

        // 1. Admin Desa creates poll -> universal
        $this->actingAs($adminDesa)->post(route('polls.store'), [
            'title' => 'Musyawarah RKPDes 2027',
            'options' => ['Opsi A', 'Opsi B'],
        ]);

        $desaPoll = Poll::where('title', 'Musyawarah RKPDes 2027')->first();
        $this->assertNotNull($desaPoll);

        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $citizenRt1->id,
            'reference_id' => $desaPoll->id,
        ]);
        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $citizenRt2->id,
            'reference_id' => $desaPoll->id,
        ]);

        // 2. RW creates poll -> universal
        $this->actingAs($rwOfficer)->post(route('polls.store'), [
            'title' => 'Pengadaan Lampu Jalan Lingkungan',
            'options' => ['Jalan Utama', 'Jalan Gang'],
        ]);

        $rwPoll = Poll::where('title', 'Pengadaan Lampu Jalan Lingkungan')->first();
        $this->assertNotNull($rwPoll);

        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $citizenRt1->id,
            'reference_id' => $rwPoll->id,
        ]);
        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $citizenRt2->id,
            'reference_id' => $rwPoll->id,
        ]);
    }

    public function test_rt_poll_only_notifies_citizens_within_that_specific_rt(): void
    {
        $village = Village::create(['name' => 'Desa Berseri', 'code' => 'DB01']);
        $rw = Rw::create(['village_id' => $village->id, 'number' => '01']);
        $rt1 = Rt::create(['rw_id' => $rw->id, 'number' => '01']);
        $rt2 = Rt::create(['rw_id' => $rw->id, 'number' => '02']);

        $rt1Officer = User::factory()->create(['role' => 'RT', 'rt_id' => $rt1->id, 'rw_id' => $rw->id, 'village_id' => $village->id]);

        $citizenInRt1 = User::factory()->create(['role' => 'WARGA', 'rt_id' => $rt1->id, 'rw_id' => $rw->id, 'village_id' => $village->id]);
        $citizenInRt2 = User::factory()->create(['role' => 'WARGA', 'rt_id' => $rt2->id, 'rw_id' => $rw->id, 'village_id' => $village->id]);

        $this->actingAs($rt1Officer)->post(route('polls.store'), [
            'title' => 'Jadwal Ronda Malam RT 01',
            'options' => ['Sistem Giliran Harian', 'Sistem Tim Mingguan'],
        ]);

        $rtPoll = Poll::where('title', 'Jadwal Ronda Malam RT 01')->first();
        $this->assertNotNull($rtPoll);

        // Citizen in RT 1 MUST receive notification
        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $citizenInRt1->id,
            'reference_id' => $rtPoll->id,
        ]);

        // Citizen in RT 2 MUST NOT receive notification
        $this->assertDatabaseMissing('app_notifications', [
            'user_id' => $citizenInRt2->id,
            'reference_id' => $rtPoll->id,
        ]);
    }

    public function test_dashboard_displays_completed_musyawarah_results_in_information_section(): void
    {
        $village = Village::create(['name' => 'Desa Cerdas', 'code' => 'DC01']);
        $rw = Rw::create(['village_id' => $village->id, 'number' => '01']);
        $rt = Rt::create(['rw_id' => $rw->id, 'number' => '01']);

        $officer = User::factory()->create(['role' => 'ADMIN_DESA', 'village_id' => $village->id]);
        $citizen = User::factory()->create(['role' => 'WARGA', 'rt_id' => $rt->id, 'rw_id' => $rw->id, 'village_id' => $village->id]);

        $poll = Poll::create([
            'created_by' => $officer->id,
            'title' => 'Renovasi Balai Warga',
            'level' => 'DESA',
            'village_id' => $village->id,
            'status' => 'CLOSED',
        ]);

        $optA = PollOption::create(['poll_id' => $poll->id, 'text' => 'Tahun Ini']);
        $optB = PollOption::create(['poll_id' => $poll->id, 'text' => 'Tahun Depan']);

        $poll->votes()->create(['poll_option_id' => $optA->id, 'user_id' => $citizen->id]);

        // Warga dashboard should display completed musyawarah in village information section
        $responseWarga = $this->actingAs($citizen)->get(route('dashboard'));
        $responseWarga->assertOk();
        $responseWarga->assertSee('Renovasi Balai Warga');
        $responseWarga->assertSee('Hasil Musyawarah');
        $responseWarga->assertSee('Tahun Ini');

        // Admin Desa dashboard should also display it
        $responseDesa = $this->actingAs($officer)->get(route('dashboard'));
        $responseDesa->assertOk();
        $responseDesa->assertSee('Renovasi Balai Warga');
        $responseDesa->assertSee('Hasil Musyawarah Desa');
    }
}
