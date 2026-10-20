<?php

namespace Tests\Feature;

use App\Models\Complaint;
use App\Models\Resident;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\User;
use App\Models\Village;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TelegramComplaintNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.telegram.bot_token' => 'test-token-complaint']);
    }

    public function test_telegram_notification_sent_when_complaint_status_updated_to_processing_and_resolved(): void
    {
        Http::fake([
            'https://api.telegram.org/*' => Http::response(['ok' => true], 200),
        ]);

        $village = Village::create(['id' => (string) \Illuminate\Support\Str::uuid(), 'name' => 'Desa Makmur', 'code' => 'DMK']);
        $rw = Rw::create(['id' => (string) \Illuminate\Support\Str::uuid(), 'number' => '02', 'village_id' => $village->id]);
        $rt = Rt::create(['id' => (string) \Illuminate\Support\Str::uuid(), 'number' => '03', 'rw_id' => $rw->id]);

        $citizen = User::factory()->create([
            'name' => 'Rahmat Hidayat',
            'username' => '3201123456789010',
            'role' => 'WARGA',
            'telegram_id' => '555444333',
            'rt_id' => $rt->id,
            'rw_id' => $rw->id,
            'village_id' => $village->id,
        ]);

        $rtOfficer = User::factory()->create([
            'name' => 'Pak RT Santoso',
            'role' => 'RT',
            'rt_id' => $rt->id,
            'rw_id' => $rw->id,
            'village_id' => $village->id,
        ]);

        $complaint = Complaint::create([
            'user_id' => $citizen->id,
            'title' => 'Saluran Air Tersumbat',
            'description' => 'Saluran air di depan pos ronda meluap saat hujan deras.',
            'location' => 'Gang Melati No. 5',
            'status' => 'PENDING',
            'rt_id' => $rt->id,
            'rw_id' => $rw->id,
            'village_id' => $village->id,
        ]);

        // Officer updates status to PROCESSING
        $this->actingAs($rtOfficer)
            ->patch(route('complaints.update-status', $complaint->id), [
                'status' => 'PROCESSING',
            ])
            ->assertRedirect();

        $this->assertEquals('PROCESSING', $complaint->fresh()->status);

        // Assert Telegram notification was sent for citizen
        Http::assertSent(function ($request) {
            $data = $request->data();
            return str_contains($request->url(), 'sendMessage')
                && ($data['chat_id'] ?? null) === '555444333'
                && str_contains($data['text'] ?? '', 'Saluran Air Tersumbat')
                && str_contains($data['text'] ?? '', 'Pengaduan Sedang Diproses')
                && str_contains($data['text'] ?? '', 'Pak RT Santoso');
        });

        // Officer updates status to RESOLVED
        $this->actingAs($rtOfficer)
            ->patch(route('complaints.update-status', $complaint->id), [
                'status' => 'RESOLVED',
            ])
            ->assertRedirect();

        $this->assertEquals('RESOLVED', $complaint->fresh()->status);

        Http::assertSent(function ($request) {
            $data = $request->data();
            return str_contains($request->url(), 'sendMessage')
                && ($data['chat_id'] ?? null) === '555444333'
                && str_contains($data['text'] ?? '', 'Saluran Air Tersumbat')
                && str_contains($data['text'] ?? '', 'Pengaduan Selesai Ditangani');
        });
    }

    public function test_telegram_notification_sent_when_complaint_created_via_api(): void
    {
        Http::fake([
            'https://api.telegram.org/*' => Http::response(['ok' => true], 200),
        ]);

        $citizen = User::factory()->create([
            'username' => '3201123456789011',
            'role' => 'WARGA',
            'telegram_id' => '777888999',
        ]);

        $response = $this->postJson('/api/telegram/complaints', [
            'telegram_id' => '777888999',
            'title' => 'Lampu Jalan Mati',
            'description' => 'Lampu penerangan jalan di RT 01 mati sudah 2 malam.',
        ]);

        $response->assertStatus(200);

        Http::assertSent(function ($request) {
            $data = $request->data();
            return str_contains($request->url(), 'sendMessage')
                && ($data['chat_id'] ?? null) === '777888999'
                && str_contains($data['text'] ?? '', 'Lampu Jalan Mati')
                && str_contains($data['text'] ?? '', 'PENGADUAN BERHASIL DIAJUKAN');
        });
    }
}
