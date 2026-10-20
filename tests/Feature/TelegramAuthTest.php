<?php

namespace Tests\Feature;

use App\Models\Resident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TelegramAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_logout_telegram(): void
    {
        $user = User::factory()->create([
            'username' => '3201123456789009',
            'telegram_id' => '999888777',
        ]);

        Resident::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'nik' => $user->username,
            'full_name' => 'Budi Santoso',
        ]);

        $response = $this->postJson('/api/telegram/logout', [
            'telegram_id' => '999888777',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Berhasil logout. Akun Budi Santoso telah diputuskan dari bot Telegram ini.',
            ]);

        $this->assertNull($user->fresh()->telegram_id);
    }

    public function test_logout_fails_if_telegram_not_linked(): void
    {
        $response = $this->postJson('/api/telegram/logout', [
            'telegram_id' => '000000000',
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'error' => 'Akun Telegram Anda belum terhubung dengan akun SIWIL manapun.',
            ]);
    }
}
