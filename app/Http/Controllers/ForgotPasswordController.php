<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    public function __construct(
        protected WhatsAppService $whatsAppService
    ) {}

    /**
     * Show form to request password reset OTP.
     */
    public function showLinkRequestForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }

        return view('auth.forgot-password');
    }

    /**
     * Send OTP via WhatsApp.
     */
    public function sendResetOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'identifier' => ['required', 'string'],
        ], [
            'identifier.required' => 'Masukkan NIK, Email, atau No. WhatsApp Anda.',
        ]);

        $identifier = trim($request->input('identifier'));
        $cleanPhone = WhatsAppService::normalizePhoneNumber($identifier);

        // Cari user berdasarkan username (NIK), email, phone_number, atau whatsapp_number
        $user = User::where('username', $identifier)
            ->orWhere('email', $identifier)
            ->when($cleanPhone, function ($query) use ($identifier, $cleanPhone) {
                $query->orWhere('phone_number', $identifier)
                    ->orWhere('whatsapp_number', $identifier)
                    ->orWhere('phone_number', $cleanPhone)
                    ->orWhere('whatsapp_number', $cleanPhone);
            })
            ->first();

        if (! $user) {
            return back()->withInput()->withErrors([
                'identifier' => 'Akun dengan NIK/Email/No. WhatsApp tersebut tidak ditemukan di sistem SIWIL.',
            ]);
        }

        if ($user->status === 'rejected' || $user->status === 'inactive') {
            return back()->withInput()->withErrors([
                'identifier' => 'Akun Anda berstatus non-aktif atau telah ditolak. Silakan hubungi Administrator.',
            ]);
        }

        $waNumber = $this->whatsAppService->getUserWhatsAppNumber($user);
        if (empty($waNumber)) {
            return back()->withInput()->withErrors([
                'identifier' => 'Akun ditemukan, namun belum ada nomor WhatsApp/HP yang terhubung untuk pengiriman OTP. Silakan hubungi pengurus RT Anda.',
            ]);
        }

        $cooldownKey = "otp_cooldown_{$user->id}";
        if (Cache::has($cooldownKey)) {
            $remaining = Cache::get($cooldownKey) - now()->timestamp;
            if ($remaining > 0) {
                session(['password_reset_user_id' => $user->id]);

                return redirect()->route('password.verify')->with('info', "Kode OTP sebelumnya sudah dikirim. Mohon tunggu {$remaining} detik untuk meminta kode ulang.");
            }
        }

        $otp = (string) random_int(100000, 999999);

        // Simpan OTP di cache selama 10 menit
        $otpKey = "password_otp_{$user->id}";
        Cache::put($otpKey, [
            'otp_hash' => Hash::make($otp),
            'wa_number' => $waNumber,
            'attempts' => 0,
            'created_at' => now()->timestamp,
            'expires_at' => now()->addMinutes(10)->timestamp,
        ], now()->addMinutes(10));

        // Set cooldown 60 detik
        Cache::put($cooldownKey, now()->addSeconds(60)->timestamp, 60);

        Log::info("Password reset OTP generated for user [{$user->id}] {$user->username} ({$waNumber}): {$otp}");

        $sent = $this->whatsAppService->sendPasswordResetOtp($user, $otp);

        session(['password_reset_user_id' => $user->id]);

        $masked = $this->maskPhoneNumber($waNumber);

        if (! $sent) {
            $devNote = app()->environment('local', 'testing')
                ? " (Mode Pengujian Lokal - Kode OTP: {$otp})"
                : '';

            return redirect()->route('password.verify')->with('warning', "Kode OTP telah diproses ke nomor {$masked}. Pastikan Bot WhatsApp lokal sedang berjalan pada port 3000{$devNote}.");
        }

        return redirect()->route('password.verify')->with('success', "Kode OTP 6 digit berhasil dikirimkan ke nomor WhatsApp Anda ({$masked}). Silakan cek pesan WhatsApp.");
    }

    /**
     * Show form to input OTP.
     */
    public function showVerifyForm(): View|RedirectResponse
    {
        $userId = session('password_reset_user_id');
        if (! $userId) {
            return redirect()->route('password.request');
        }

        $user = User::find($userId);
        if (! $user) {
            session()->forget('password_reset_user_id');

            return redirect()->route('password.request');
        }

        $waNumber = $this->whatsAppService->getUserWhatsAppNumber($user);
        $maskedWa = $waNumber ? $this->maskPhoneNumber($waNumber) : 'WhatsApp Terdaftar';

        $cooldownKey = "otp_cooldown_{$user->id}";
        $cooldownSeconds = 0;
        if (Cache::has($cooldownKey)) {
            $cooldownSeconds = max(0, Cache::get($cooldownKey) - now()->timestamp);
        }

        return view('auth.verify-otp', compact('user', 'maskedWa', 'cooldownSeconds'));
    }

    /**
     * Verify the submitted OTP code.
     */
    public function verifyOtp(Request $request): RedirectResponse
    {
        $userId = session('password_reset_user_id');
        if (! $userId) {
            return redirect()->route('password.request')->withErrors(['identifier' => 'Sesi reset password telah berakhir. Silakan ulangi kembali.']);
        }

        $request->validate([
            'otp' => ['required', 'string', 'digits:6'],
        ], [
            'otp.required' => 'Kode OTP wajib diisi.',
            'otp.digits' => 'Kode OTP harus berupa 6 digit angka.',
        ]);

        $user = User::find($userId);
        if (! $user) {
            session()->forget('password_reset_user_id');

            return redirect()->route('password.request');
        }

        $otpKey = "password_otp_{$user->id}";
        $cacheData = Cache::get($otpKey);

        if (! $cacheData) {
            return back()->withErrors(['otp' => 'Kode OTP telah kedaluwarsa atau belum diminta. Silakan klik kirim ulang kode.']);
        }

        if (($cacheData['attempts'] ?? 0) >= 5) {
            Cache::forget($otpKey);

            return back()->withErrors(['otp' => 'Terlalu banyak percobaan salah. Silakan minta kode OTP baru.']);
        }

        if (! Hash::check($request->input('otp'), $cacheData['otp_hash'])) {
            $cacheData['attempts'] = ($cacheData['attempts'] ?? 0) + 1;
            Cache::put($otpKey, $cacheData, now()->addMinutes(10));

            $remaining = 5 - $cacheData['attempts'];

            return back()->withErrors([
                'otp' => "Kode OTP yang Anda masukkan salah. Sisa kesempatan: {$remaining} kali.",
            ]);
        }

        // OTP Valid!
        Cache::forget($otpKey);
        session()->forget('password_reset_user_id');

        // Tandai verifikasi berhasil untuk proses set password baru
        session([
            'password_reset_verified_user_id' => $user->id,
            'password_reset_verified_at' => now()->timestamp,
        ]);

        return redirect()->route('password.reset')->with('success', 'Verifikasi OTP berhasil! Silakan tentukan kata sandi baru Anda.');
    }

    /**
     * Resend OTP code via WhatsApp.
     */
    public function resendOtp(Request $request): RedirectResponse
    {
        $userId = session('password_reset_user_id');
        if (! $userId) {
            return redirect()->route('password.request');
        }

        $user = User::find($userId);
        if (! $user) {
            session()->forget('password_reset_user_id');

            return redirect()->route('password.request');
        }

        $cooldownKey = "otp_cooldown_{$user->id}";
        if (Cache::has($cooldownKey)) {
            $remaining = Cache::get($cooldownKey) - now()->timestamp;
            if ($remaining > 0) {
                return back()->with('info', "Harap tunggu {$remaining} detik sebelum meminta kode OTP kembali.");
            }
        }

        $waNumber = $this->whatsAppService->getUserWhatsAppNumber($user);
        if (empty($waNumber)) {
            return back()->withErrors(['otp' => 'Nomor WhatsApp tidak terdeteksi. Hubungi Admin.']);
        }

        $otp = (string) random_int(100000, 999999);
        $otpKey = "password_otp_{$user->id}";

        Cache::put($otpKey, [
            'otp_hash' => Hash::make($otp),
            'wa_number' => $waNumber,
            'attempts' => 0,
            'created_at' => now()->timestamp,
            'expires_at' => now()->addMinutes(10)->timestamp,
        ], now()->addMinutes(10));

        Cache::put($cooldownKey, now()->addSeconds(60)->timestamp, 60);

        Log::info("Resending password reset OTP for user [{$user->id}] {$user->username}: {$otp}");

        $sent = $this->whatsAppService->sendPasswordResetOtp($user, $otp);

        $masked = $this->maskPhoneNumber($waNumber);

        if (! $sent) {
            $devNote = app()->environment('local', 'testing')
                ? " (Mode Pengujian Lokal - Kode OTP Baru: {$otp})"
                : '';

            return back()->with('warning', "Kode OTP baru telah digenerate ke {$masked}. Bot WA belum merespons{$devNote}.");
        }

        return back()->with('success', "Kode OTP baru telah dikirimkan ke nomor WhatsApp ({$masked}).");
    }

    /**
     * Show form to reset password.
     */
    public function showResetForm(): View|RedirectResponse
    {
        $userId = session('password_reset_verified_user_id');
        $verifiedAt = session('password_reset_verified_at');

        if (! $userId || ! $verifiedAt || (now()->timestamp - $verifiedAt > 900)) {
            session()->forget(['password_reset_verified_user_id', 'password_reset_verified_at']);

            return redirect()->route('password.request')->withErrors(['identifier' => 'Sesi reset kata sandi telah kedaluwarsa. Silakan lakukan verifikasi ulang.']);
        }

        $user = User::find($userId);
        if (! $user) {
            return redirect()->route('password.request');
        }

        return view('auth.reset-password', compact('user'));
    }

    /**
     * Save the new password.
     */
    public function resetPassword(Request $request): RedirectResponse
    {
        $userId = session('password_reset_verified_user_id');
        $verifiedAt = session('password_reset_verified_at');

        if (! $userId || ! $verifiedAt || (now()->timestamp - $verifiedAt > 900)) {
            session()->forget(['password_reset_verified_user_id', 'password_reset_verified_at']);

            return redirect()->route('password.request')->withErrors(['identifier' => 'Sesi reset kata sandi telah kedaluwarsa. Silakan lakukan verifikasi ulang.']);
        }

        $request->validate([
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $user = User::findOrFail($userId);
        $user->update([
            'password' => Hash::make($request->input('password')),
        ]);

        // Kirim notifikasi pengamanan ke WhatsApp
        $this->whatsAppService->sendPasswordChangedNotification($user);

        // Bersihkan session
        session()->forget(['password_reset_verified_user_id', 'password_reset_verified_at']);

        return redirect()->route('login')->with('success', 'Kata sandi Anda berhasil diperbarui! Silakan masuk dengan kata sandi baru.');
    }

    /**
     * Mask phone number for display (e.g. 0812 •••• 7890).
     */
    protected function maskPhoneNumber(string $number): string
    {
        $clean = preg_replace('/[^0-9]/', '', $number);
        $len = strlen($clean);
        if ($len <= 5) {
            return $clean;
        }

        $start = substr($clean, 0, 4);
        $end = substr($clean, -4);

        return "{$start} •••• {$end}";
    }
}
