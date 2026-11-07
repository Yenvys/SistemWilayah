<?php

namespace Tests\Feature;

use App\Models\SystemConfiguration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SystemConfigurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_view_configuration_page(): void
    {
        $superAdmin = User::factory()->create([
            'role' => 'SUPER_ADMIN',
            'username' => 'admin_super',
        ]);

        $response = $this->actingAs($superAdmin)->get(route('configuration.index'));

        $response->assertStatus(200);
        $response->assertSee('Pusat Konfigurasi Sistem');
        $response->assertSee('Nama Model AI (Ketik Manual)');
        $response->assertSee('Daftar API Key AI (Sistem Fallback Otomatis)');
    }

    public function test_non_super_admin_is_forbidden_from_configuration_page(): void
    {
        $warga = User::factory()->create([
            'role' => 'WARGA',
            'username' => '3201010101010009',
        ]);

        $response = $this->actingAs($warga)->get(route('configuration.index'));

        $response->assertStatus(403);
    }

    public function test_super_admin_can_save_configuration_with_multi_keys_as_json_array(): void
    {
        $superAdmin = User::factory()->create([
            'role' => 'SUPER_ADMIN',
            'username' => 'admin_super_config',
        ]);

        $payload = [
            'ai_provider' => 'gemini',
            'ai_model' => 'gemini-custom-flash-v2',
            'ai_api_keys' => [
                'AIzaSyKeyFirstPrimary12345',
                'AIzaSyKeySecondBackup67890',
            ],
            'whatsapp_bot_url' => 'http://localhost:3001',
            'whatsapp_bot_number' => '6289912345678',
            'whatsapp_api_token' => 'my-wa-secret-token',
            'telegram_bot_token' => '123456789:ABCdefGhIJKlmNoPQRsTUVwxyZ',
            'telegram_bot_username' => 'SiwilTestBot',
            'mail_mailer' => 'smtp',
            'mail_host' => 'smtp.mailtrap.io',
            'mail_port' => 2525,
            'mail_username' => 'testuser',
            'mail_password' => 'testpass',
            'mail_encryption' => 'tls',
            'mail_from_address' => 'noreply@siwil.id',
            'mail_from_name' => 'SIWIL Official',
        ];

        $response = $this->actingAs($superAdmin)->post(route('configuration.update'), $payload);

        $response->assertRedirect(route('configuration.index'));
        $response->assertSessionHas('success');

        $config = SystemConfiguration::first();
        $this->assertNotNull($config);
        $this->assertEquals('gemini-custom-flash-v2', $config->ai_model);
        $this->assertEquals('gemini', $config->ai_provider);
        $this->assertIsArray($config->ai_api_keys);
        $this->assertCount(2, $config->ai_api_keys);
        $this->assertEquals('AIzaSyKeyFirstPrimary12345', $config->ai_api_keys[0]);
        $this->assertEquals('AIzaSyKeySecondBackup67890', $config->ai_api_keys[1]);
        $this->assertEquals('http://localhost:3001', $config->whatsapp_bot_url);
        $this->assertEquals('123456789:ABCdefGhIJKlmNoPQRsTUVwxyZ', $config->telegram_bot_token);
    }

    public function test_masked_secret_does_not_overwrite_existing_database_value(): void
    {
        $superAdmin = User::factory()->create([
            'role' => 'SUPER_ADMIN',
            'username' => 'admin_secret_guard',
        ]);

        SystemConfiguration::create([
            'ai_provider' => 'gemini',
            'ai_model' => 'gemini-2.5-flash',
            'ai_api_keys' => ['AIzaSyVerySecretOriginalKey999'],
            'telegram_bot_token' => '999888777:OriginalTgSecretToken12345',
            'whatsapp_api_token' => 'OriginalWaSecretToken',
            'mail_password' => 'OriginalMailPassword',
        ]);

        // Submit with masked characters
        $payload = [
            'ai_provider' => 'gemini',
            'ai_model' => 'gemini-2.5-flash',
            'ai_api_keys' => [
                'AIzaSy••••••••Key999', // Masked representation
            ],
            'telegram_bot_token' => '999888777••••••••12345',
            'whatsapp_api_token' => 'Origin••••••••oken',
            'mail_password' => 'Or••••••••rd',
        ];

        $response = $this->actingAs($superAdmin)->post(route('configuration.update'), $payload);
        $response->assertRedirect(route('configuration.index'));

        $config = SystemConfiguration::first();
        // Secrets must be retained without being overwritten by the masked dots
        $this->assertEquals('AIzaSyVerySecretOriginalKey999', $config->ai_api_keys[0]);
        $this->assertEquals('999888777:OriginalTgSecretToken12345', $config->telegram_bot_token);
        $this->assertEquals('OriginalWaSecretToken', $config->whatsapp_api_token);
        $this->assertEquals('OriginalMailPassword', $config->mail_password);
    }

    public function test_ai_controller_retries_with_fallback_api_key_when_primary_key_rate_limited(): void
    {
        $superAdmin = User::factory()->create([
            'role' => 'SUPER_ADMIN',
            'username' => 'super_ai_tester',
        ]);

        SystemConfiguration::create([
            'ai_provider' => 'gemini',
            'ai_model' => 'gemini-2.5-flash',
            'ai_api_keys' => [
                'FIRST_KEY_DEAD_429',
                'SECOND_KEY_WORKING_OK',
            ],
        ]);

        // Mock Http to simulate 429 on first key and success on second key
        Http::fake(function ($request) {
            $url = (string) $request->url();
            if (str_contains($url, 'FIRST_KEY_DEAD_429')) {
                return Http::response([
                    'error' => [
                        'code' => 429,
                        'message' => 'Resource has been exhausted (e.g. check quota).',
                        'status' => 'RESOURCE_EXHAUSTED',
                    ],
                ], 429);
            }

            if (str_contains($url, 'SECOND_KEY_WORKING_OK')) {
                return Http::response([
                    'candidates' => [
                        [
                            'content' => [
                                'parts' => [
                                    ['text' => 'Halo dari API Key Kedua! Jawaban berhasil diproses.'],
                                ],
                            ],
                        ],
                    ],
                ], 200);
            }

            return Http::response([], 404);
        });

        $response = $this->actingAs($superAdmin)->postJson(route('ai.chat'), [
            'message' => 'Halo apa kabar sistem?',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertStringContainsString('Halo dari API Key Kedua', $data['message']);
    }
}
