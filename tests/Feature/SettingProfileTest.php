<?php

namespace Tests\Feature;

use App\Models\Resident;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\User;
use App\Models\Village;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SettingProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_settings_page_with_address_field(): void
    {
        $village = Village::create(['id' => (string) Str::uuid(), 'name' => 'Desa Sukamaju']);
        $rw = Rw::create(['id' => (string) Str::uuid(), 'village_id' => $village->id, 'number' => '01']);
        $rt = Rt::create(['id' => (string) Str::uuid(), 'rw_id' => $rw->id, 'number' => '01']);

        $user = User::factory()->create([
            'role' => 'WARGA',
            'username' => '3201010101010001',
            'address' => 'Jl. Kenanga No. 45',
        ]);

        $response = $this->actingAs($user)->get(route('settings.index'));

        $response->assertStatus(200);
        $response->assertSee('Alamat Lengkap / Tempat Tinggal');
        $response->assertSee('Jl. Kenanga No. 45');
    }

    public function test_user_can_update_address_in_settings(): void
    {
        $village = Village::create(['id' => (string) Str::uuid(), 'name' => 'Desa Sukamaju']);
        $rw = Rw::create(['id' => (string) Str::uuid(), 'village_id' => $village->id, 'number' => '01']);
        $rt = Rt::create(['id' => (string) Str::uuid(), 'rw_id' => $rw->id, 'number' => '01']);

        $user = User::factory()->create([
            'role' => 'WARGA',
            'username' => '3201010101010002',
            'email' => 'warga@example.com',
            'phone_number' => '081234567890',
            'address' => 'Alamat Lama',
        ]);

        $resident = Resident::create([
            'id' => (string) Str::uuid(),
            'nik' => $user->username,
            'full_name' => 'Budi Santoso',
            'rt_id' => $rt->id,
        ]);

        $response = $this->actingAs($user)->put(route('settings.profile.update'), [
            'full_name' => 'Budi Santoso Updated',
            'email' => 'warga@example.com',
            'phone_number' => '081234567890',
            'address' => 'Jl. Mawar No. 10 RT 01 RW 01',
            'rt_id' => $rt->id,
        ]);

        $response->assertRedirect(route('settings.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'address' => 'Jl. Mawar No. 10 RT 01 RW 01',
        ]);
    }
}
