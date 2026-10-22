<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterWargaRequest;
use App\Models\Resident;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }

        return view('auth.login');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }

        return view('auth.login', ['initialTab' => 'register']);
    }

    public function register(RegisterWargaRequest $request)
    {
        $validated = $request->validated();

        // Cari data resident untuk mendapatkan relasi RT/RW/Village
        $resident = Resident::where('nik', $validated['nik'])->first();
        $phone = ! empty($validated['phone_number']) ? trim($validated['phone_number']) : null;
        $normalizedWa = $phone ? WhatsAppService::normalizePhoneNumber($phone) : null;

        $user = User::create([
            'name' => $resident?->full_name,
            'username' => $validated['nik'],
            'kk_number' => $validated['kk_number'],
            'phone_number' => $phone,
            'whatsapp_number' => $normalizedWa,
            'password' => Hash::make($validated['password']),
            'role' => 'WARGA',
            'status' => 'pending',
            'rt_id' => $resident?->rt_id,
            'rw_id' => $resident?->rt?->rw_id,
            'village_id' => $resident?->rt?->rw?->village_id,
        ]);

        $role = Role::firstOrCreate(['name' => 'warga', 'guard_name' => 'web']);
        $user->assignRole($role);

        return redirect()->route('login')->with('success', 'Registrasi berhasil! Akun Anda sedang menunggu verifikasi oleh RT/Admin. Silakan cek kembali nanti.');
    }

    public function authenticate(Request $request)
    {
        $request->validate([
            'username' => ['required'], // ini bisa berupa NIK, email, atau no HP
            'password' => ['required'],
        ]);

        $loginInput = $request->input('username');
        $password = $request->input('password');

        // Cari user yang cocok dengan username(NIK), email, atau phone_number
        $user = User::where('username', $loginInput)
            ->orWhere('email', $loginInput)
            ->orWhere('phone_number', $loginInput)
            ->first();

        if ($user && Hash::check($password, $user->password)) {
            // Blokir akun yang masih pending
            if ($user->status === 'pending') {
                return back()->withErrors([
                    'username' => 'Akun Anda masih menunggu verifikasi oleh RT/Admin. Silakan hubungi RT setempat.',
                ])->onlyInput('username');
            }

            // Blokir akun yang ditolak/nonaktif
            if ($user->status === 'rejected' || $user->status === 'inactive') {
                return back()->withErrors([
                    'username' => 'Akun Anda tidak aktif atau telah ditolak. Silakan hubungi Admin.',
                ])->onlyInput('username');
            }

            Auth::login($user);
            $request->session()->regenerate();

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'username' => 'Akun tidak ditemukan atau password salah.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
