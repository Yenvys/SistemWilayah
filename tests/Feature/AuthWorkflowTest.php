<?php

namespace Tests\Feature;

use App\Models\Family;
use App\Models\Resident;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\User;
use App\Models\Village;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class AuthWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders_with_password_eye_toggle_and_forgot_password_link(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee('Lupa Password?');
        $response->assertSee('togglePasswordVisibility');
        $response->assertSee('toggle-password-btn');
        $response->assertSee('login-password');
    }

    public function test_login_page_renders_sign_up_form_with_all_required_fields_and_eye_toggles(): void
    {
        $response = $this->get(route('login', ['tab' => 'register']));

        $response->assertStatus(200);
        $response->assertSee('NIK (Nomor Induk Kependudukan)');
        $response->assertSee('Nomor Kartu Keluarga (KK)');
        $response->assertSee('No. WhatsApp / HP');
        $response->assertSee('reg-password');
        $response->assertSee('reg-password-confirm');
        $response->assertSee('Daftar Akun Warga');
    }

    public function test_register_route_renders_unified_auth_page(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertSee('Daftar Akun Warga');
        $response->assertSee('togglePasswordVisibility');
    }

    public function test_citizen_can_register_with_valid_nik_and_kk(): void
    {
        $village = Village::create(['id' => (string) Str::uuid(), 'name' => 'Desa Sukamaju']);
        $rw = Rw::create(['id' => (string) Str::uuid(), 'village_id' => $village->id, 'number' => '01']);
        $rt = Rt::create(['id' => (string) Str::uuid(), 'rw_id' => $rw->id, 'number' => '01']);

        $family = Family::create([
            'id' => (string) Str::uuid(),
            'kk_number' => '3201010101010001',
            'address' => 'Jl. Mawar No. 1',
            'rt_id' => $rt->id,
        ]);

        $resident = Resident::create([
            'id' => (string) Str::uuid(),
            'nik' => '3201010101010002',
            'full_name' => 'Ahmad Warga',
            'family_id' => $family->id,
            'rt_id' => $rt->id,
        ]);

        $response = $this->post(url('/register'), [
            'nik' => '3201010101010002',
            'kk_number' => '3201010101010001',
            'phone_number' => '081234567890',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'username' => '3201010101010002',
            'kk_number' => '3201010101010001',
            'phone_number' => '081234567890',
            'status' => 'pending',
            'role' => 'WARGA',
        ]);
    }

    public function test_user_can_request_forgot_password_and_otp_is_sent_via_whatsapp(): void
    {
        $user = User::factory()->create([
            'username' => '3201010101010003',
            'phone_number' => '081234567890',
            'whatsapp_number' => '6281234567890',
            'status' => 'active',
        ]);

        $mockWa = Mockery::mock(WhatsAppService::class);
        $mockWa->shouldReceive('getUserWhatsAppNumber')->andReturn('6281234567890');
        $mockWa->shouldReceive('sendPasswordResetOtp')->once()->andReturn(true);
        $this->app->instance(WhatsAppService::class, $mockWa);

        $response = $this->post(route('password.otp.send'), [
            'identifier' => '3201010101010003',
        ]);

        $response->assertRedirect(route('password.verify'));
        $this->assertEquals($user->id, session('password_reset_user_id'));

        $cachedOtp = Cache::get("password_otp_{$user->id}");
        $this->assertNotNull($cachedOtp);
        $this->assertArrayHasKey('otp_hash', $cachedOtp);
    }

    public function test_user_cannot_request_otp_if_account_has_no_whatsapp_number(): void
    {
        $user = User::factory()->create([
            'username' => '3201010101010004',
            'phone_number' => null,
            'whatsapp_number' => null,
            'status' => 'active',
        ]);

        $response = $this->from(route('password.request'))->post(route('password.otp.send'), [
            'identifier' => '3201010101010004',
        ]);

        $response->assertRedirect(route('password.request'));
        $response->assertSessionHasErrors('identifier');
    }

    public function test_user_can_verify_valid_otp_and_is_redirected_to_reset_form(): void
    {
        $user = User::factory()->create([
            'username' => '3201010101010005',
            'whatsapp_number' => '6281234567890',
            'status' => 'active',
        ]);

        $otp = '123456';
        Cache::put("password_otp_{$user->id}", [
            'otp_hash' => Hash::make($otp),
            'wa_number' => '6281234567890',
            'attempts' => 0,
            'expires_at' => now()->addMinutes(10)->timestamp,
        ], now()->addMinutes(10));

        $response = $this->withSession(['password_reset_user_id' => $user->id])
            ->post(route('password.otp.verify'), [
                'otp' => '123456',
            ]);

        $response->assertRedirect(route('password.reset'));
        $this->assertEquals($user->id, session('password_reset_verified_user_id'));
        $this->assertNull(Cache::get("password_otp_{$user->id}"));
    }

    public function test_user_cannot_verify_invalid_otp(): void
    {
        $user = User::factory()->create([
            'username' => '3201010101010006',
            'whatsapp_number' => '6281234567890',
            'status' => 'active',
        ]);

        $otp = '123456';
        Cache::put("password_otp_{$user->id}", [
            'otp_hash' => Hash::make($otp),
            'wa_number' => '6281234567890',
            'attempts' => 0,
            'expires_at' => now()->addMinutes(10)->timestamp,
        ], now()->addMinutes(10));

        $response = $this->withSession(['password_reset_user_id' => $user->id])
            ->from(route('password.verify'))
            ->post(route('password.otp.verify'), [
                'otp' => '999999',
            ]);

        $response->assertRedirect(route('password.verify'));
        $response->assertSessionHasErrors('otp');

        $cache = Cache::get("password_otp_{$user->id}");
        $this->assertEquals(1, $cache['attempts']);
    }

    public function test_user_can_reset_password_after_otp_verification(): void
    {
        $user = User::factory()->create([
            'username' => '3201010101010007',
            'password' => Hash::make('oldpassword'),
            'status' => 'active',
        ]);

        $mockWa = Mockery::mock(WhatsAppService::class);
        $mockWa->shouldReceive('sendPasswordChangedNotification')->once()->andReturn(true);
        $this->app->instance(WhatsAppService::class, $mockWa);

        $response = $this->withSession([
            'password_reset_verified_user_id' => $user->id,
            'password_reset_verified_at' => now()->timestamp,
        ])->post(route('password.update'), [
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
        $this->assertFalse(Hash::check('oldpassword', $user->password));
    }
}
