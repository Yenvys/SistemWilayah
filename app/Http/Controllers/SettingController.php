<?php

namespace App\Http\Controllers;

use App\Models\Resident;
use App\Models\Rt;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $resident = $user->resident;
        if ($resident) {
            $resident->load('rt.rw.village', 'family');
        }
        $rts = Rt::with('rw.village')->get();

        return view('dashboard.settings.index', compact('user', 'resident', 'rts'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'full_name' => ['required', 'string', 'max:255'],
            'gender' => ['nullable', 'in:LAKI_LAKI,PEREMPUAN'],
            'birth_place' => ['nullable', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'religion' => ['nullable', 'string', 'max:100'],
            'marital_status' => ['nullable', 'string', 'max:100'],
            'education' => ['nullable', 'string', 'max:100'],
            'occupation' => ['nullable', 'string', 'max:100'],
            'rt_id' => ['nullable', 'exists:rts,id'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone_number' => ['nullable', 'string', 'regex:/^08[0-9]{8,11}$/', Rule::unique('users')->ignore($user->id)],
            'address' => ['nullable', 'string', 'max:500'],
            'ktp_photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'signature_photo' => ['nullable', 'image', 'mimes:png', 'max:1024'],
            'stamp_photo' => ['nullable', 'image', 'mimes:png', 'max:1024'],
        ];

        $validated = $request->validate($rules, [
            'full_name.required' => 'Nama lengkap wajib diisi.',
            'phone_number.regex' => 'Format nomor HP tidak valid. Harus diawali 08 dan berisi 10-13 digit angka.',
            'phone_number.unique' => 'Nomor HP ini sudah digunakan oleh akun lain.',
        ]);

        if (empty($validated['email']) && empty($validated['phone_number']) && $user->role === 'WARGA') {
            return back()->withErrors(['profile' => 'Anda harus mengisi setidaknya salah satu kontak (Email atau No. HP) untuk opsi login.']);
        }

        // 1. Update atau Buat Data Kependudukan (Resident)
        $rtId = $validated['rt_id'] ?? $user->rt_id;
        $residentPayload = [
            'nik' => $user->username,
            'full_name' => $validated['full_name'],
            'gender' => $validated['gender'] ?? null,
            'birth_place' => $validated['birth_place'] ?? null,
            'birth_date' => $validated['birth_date'] ?? null,
            'religion' => $validated['religion'] ?? null,
            'marital_status' => $validated['marital_status'] ?? null,
            'education' => $validated['education'] ?? null,
            'occupation' => $validated['occupation'] ?? null,
            'rt_id' => $rtId,
            'status' => 'AKTIF',
        ];

        if ($request->hasFile('ktp_photo')) {
            if ($user->resident && $user->resident->ktp_photo) {
                Storage::disk('public')->delete($user->resident->ktp_photo);
            }
            $residentPayload['ktp_photo'] = $request->file('ktp_photo')->store('ktp', 'public');
        }

        // Only generate a new UUID if this resident doesn't exist yet
        $existingResident = Resident::where('nik', $user->username)->first();
        if (! $existingResident) {
            $residentPayload['id'] = (string) Str::uuid();
        }

        Resident::updateOrCreate(
            ['nik' => $user->username],
            $residentPayload
        );

        // 2. Sync Wilayah ke User jika ada rt_id
        if ($rtId) {
            $rtObj = Rt::with('rw')->find($rtId);
            if ($rtObj) {
                $validated['rt_id'] = $rtObj->id;
                $validated['rw_id'] = $rtObj->rw_id;
                $validated['village_id'] = $rtObj->rw->village_id ?? null;
            }
        }

        // Hapus field non-user dari validated
        unset(
            $validated['full_name'], $validated['gender'], $validated['birth_place'],
            $validated['birth_date'], $validated['religion'], $validated['marital_status'],
            $validated['education'], $validated['occupation'], $validated['ktp_photo'],
            $validated['signature_photo'], $validated['stamp_photo']
        );

        if ($request->hasFile('signature_photo')) {
            if ($user->signature_path) {
                Storage::disk('public')->delete($user->signature_path);
            }
            $validated['signature_path'] = $request->file('signature_photo')->store('signatures', 'public');
        }

        if ($request->hasFile('stamp_photo')) {
            if ($user->stamp_path) {
                Storage::disk('public')->delete($user->stamp_path);
            }
            $validated['stamp_path'] = $request->file('stamp_photo')->store('stamps', 'public');
        }

        $user->update($validated);

        if (array_key_exists('address', $validated) && $user->resident && $user->resident->family) {
            $user->resident->family->update(['address' => $validated['address']]);
        }

        return redirect()->route('settings.index')->with('success', 'Profil dan Data Diri kependudukan berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = Auth::user();

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password lama tidak sesuai.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('settings.index')->with('success', 'Password berhasil diubah.');
    }

    public function connectWhatsApp(Request $request)
    {
        $request->validate([
            'whatsapp_number' => ['required', 'string'],
        ]);

        $rawNumber = trim($request->input('whatsapp_number'));
        $normalized = WhatsAppService::normalizePhoneNumber($rawNumber);

        if (! $normalized || strlen($normalized) < 10) {
            return response()->json([
                'error' => 'Format nomor WhatsApp tidak valid. Masukkan minimal 10 digit (contoh: 08123456789).',
            ], 422);
        }

        $user = Auth::user();

        $exists = User::where('whatsapp_number', $normalized)
            ->where('id', '!=', $user->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'error' => 'Nomor WhatsApp ini telah digunakan oleh akun lain.',
            ], 422);
        }

        $user->whatsapp_number = $normalized;
        if (empty($user->phone_number)) {
            $user->phone_number = str_starts_with($normalized, '62') ? ('0'.substr($normalized, 2)) : $normalized;
        }
        $user->save();

        $botNumber = config('services.whatsapp.bot_number', env('WHATSAPP_BOT_NUMBER', '628981295171'));
        $greeting = 'Halo SIWIL, saya '.($user->resident?->full_name ?? $user->username);

        return response()->json([
            'success' => true,
            'message' => 'Nomor WhatsApp berhasil dihubungkan ke akun Anda!',
            'whatsapp_number' => $normalized,
            'formatted_number' => '+'.substr($normalized, 0, 2).' '.substr($normalized, 2, 3).'-'.substr($normalized, 5, 4).'-'.substr($normalized, 9),
            'bot_number' => $botNumber,
            'wa_link' => 'https://wa.me/'.$botNumber.'?text='.urlencode($greeting),
        ]);
    }

    public function disconnectWhatsApp()
    {
        $user = Auth::user();
        $user->whatsapp_number = null;
        $user->whatsapp_lid = null;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Koneksi WhatsApp berhasil diputuskan.',
        ]);
    }

    public function disconnectTelegram()
    {
        $user = Auth::user();
        $user->telegram_id = null;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Koneksi Telegram berhasil diputuskan.',
        ]);
    }
}
