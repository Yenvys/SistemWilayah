@extends('layouts.app')

@section('title', 'Pengaturan - SIWIL AI')
@section('page-title', 'Pengaturan')

@section('styles')
<style>
    .settings-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    @media (min-width: 1024px) {
        .settings-grid {
            grid-template-columns: 1fr 1fr;
        }
        .col-span-2 { grid-column: span 2; }
    }
    .form-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    @media (min-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr 1fr;
        }
    }
    .settings-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
    }
    .settings-card-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--border);
        display: flex; align-items: center; gap: 0.75rem;
    }
    .settings-card-header svg { width: 20px; height: 20px; color: var(--accent); }
    .settings-card-title { font-size: 0.95rem; font-weight: 600; }
    .settings-card-body { padding: 1.25rem; }
    .profile-info {
        display: flex; align-items: center; gap: 1rem;
        padding: 1rem; background: var(--bg-body); border-radius: 12px;
        margin-bottom: 1.25rem;
    }
    .profile-avatar {
        width: 56px; height: 56px;
        background: linear-gradient(135deg, #3b82f6, #8b5cf6);
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        color: white; font-weight: 700; font-size: 1.25rem;
    }
    .profile-meta .profile-name { font-weight: 600; font-size: 1rem; color: var(--text-primary); }
    .profile-meta .profile-role {
        font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;
        letter-spacing: 0.5px; margin-top: 2px;
    }
</style>
@endsection

@section('content')
    <h1 class="page-title">Pengaturan</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @error('profile')
        <div class="alert alert-error">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            {{ $message }}
        </div>
    @enderror

    @if(empty($user->email) && empty($user->phone_number) && $user->role === 'WARGA')
        <div class="alert alert-info">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
            <strong>Lengkapi Profil Anda:</strong> Harap isi Email ATAU Nomor HP agar Anda bisa menggunakannya untuk login selain menggunakan NIK.
        </div>
    @endif

    <div class="settings-grid">
        {{-- Profile & Data Diri Settings --}}
        <div class="settings-card col-span-2">
            <div class="settings-card-header">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                <span class="settings-card-title">Profil Akun & Data Diri Kependudukan</span>
            </div>
            <div class="settings-card-body">
                <div class="profile-info">
                    <div class="profile-avatar">
                        {{ strtoupper(substr($resident->full_name ?? $user->username, 0, 2)) }}
                    </div>
                    <div class="profile-meta">
                        <div class="profile-name">{{ $resident->full_name ?? $user->username }}</div>
                        <div class="profile-role">{{ $user->role }}</div>
                    </div>
                </div>

                <form method="POST" action="{{ route('settings.profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="form-grid">
                        {{-- NIK (Permanen - Tidak bisa diubah) --}}
                        <div class="form-group col-span-2">
                            <label class="form-label" for="nik">NIK (Nomor Induk Kependudukan - Permanen)</label>
                            <input type="text" id="nik" class="form-input" value="{{ $user->username }}" readonly disabled style="background: var(--bg-body); opacity: 0.85; cursor: not-allowed; font-family: monospace; font-weight: 600; letter-spacing: 0.5px;">
                            <small style="margin-top: 6px; display: block; font-size: 0.8rem;">
                                @if($resident)
                                    <span style="color: #10b981; font-weight: 500;">✓ Terdaftar atas nama <strong>{{ $resident->full_name }}</strong> (Siap Pengajuan Surat)</span>
                                @else
                                    <span style="color: #f59e0b; font-weight: 500; display: inline-flex; align-items: center; gap: 4px;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg> NIK {{ $user->username }} belum ada di data kependudukan. Isi data diri di bawah ini untuk mendaftarkan NIK Anda.</span>
                                @endif
                            </small>
                        </div>

                        {{-- Nama Lengkap --}}
                        <div class="form-group col-span-2">
                            <label class="form-label" for="full_name">Nama Lengkap <span style="color: var(--danger);">*</span></label>
                            <input type="text" id="full_name" name="full_name" class="form-input" value="{{ old('full_name', $resident->full_name ?? '') }}" placeholder="Nama Lengkap sesuai KTP" required>
                            @error('full_name')
                                <span style="color: var(--danger); font-size: 0.8rem;">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Jenis Kelamin --}}
                        <div class="form-group">
                            <label class="form-label" for="gender">Jenis Kelamin</label>
                            <select id="gender" name="gender" class="form-select">
                                <option value="">-- Pilih Jenis Kelamin --</option>
                                <option value="LAKI_LAKI" {{ old('gender', $resident->gender ?? '') === 'LAKI_LAKI' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="PEREMPUAN" {{ old('gender', $resident->gender ?? '') === 'PEREMPUAN' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>

                        {{-- Tempat Lahir --}}
                        <div class="form-group">
                            <label class="form-label" for="birth_place">Tempat Lahir</label>
                            <input type="text" id="birth_place" name="birth_place" class="form-input" value="{{ old('birth_place', $resident->birth_place ?? '') }}" placeholder="Kota / Kabupaten tempat lahir">
                        </div>

                        {{-- Tanggal Lahir --}}
                        <div class="form-group">
                            <label class="form-label" for="birth_date">Tanggal Lahir</label>
                            <input type="date" id="birth_date" name="birth_date" class="form-input" value="{{ old('birth_date', $resident->birth_date ?? '') }}">
                        </div>

                        {{-- Agama --}}
                        <div class="form-group">
                            <label class="form-label" for="religion">Agama</label>
                            <select id="religion" name="religion" class="form-select">
                                <option value="">-- Pilih Agama --</option>
                                @foreach(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Khonghucu', 'Lainnya'] as $agm)
                                    <option value="{{ $agm }}" {{ old('religion', $resident->religion ?? '') === $agm ? 'selected' : '' }}>{{ $agm }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Status Perkawinan --}}
                        <div class="form-group">
                            <label class="form-label" for="marital_status">Status Perkawinan</label>
                            <select id="marital_status" name="marital_status" class="form-select">
                                <option value="">-- Pilih Status Perkawinan --</option>
                                <option value="BELUM_MENIKAH" {{ old('marital_status', $resident->marital_status ?? '') === 'BELUM_MENIKAH' ? 'selected' : '' }}>Belum Menikah</option>
                                <option value="MENIKAH" {{ old('marital_status', $resident->marital_status ?? '') === 'MENIKAH' ? 'selected' : '' }}>Menikah</option>
                                <option value="CERAI_HIDUP" {{ old('marital_status', $resident->marital_status ?? '') === 'CERAI_HIDUP' ? 'selected' : '' }}>Cerai Hidup</option>
                                <option value="CERAI_MATI" {{ old('marital_status', $resident->marital_status ?? '') === 'CERAI_MATI' ? 'selected' : '' }}>Cerai Mati</option>
                            </select>
                        </div>

                        {{-- Pekerjaan --}}
                        <div class="form-group">
                            <label class="form-label" for="occupation">Pekerjaan</label>
                            <input type="text" id="occupation" name="occupation" class="form-input" value="{{ old('occupation', $resident->occupation ?? '') }}" placeholder="Contoh: Karyawan Swasta, Wiraswasta, Pelajar">
                        </div>

                        {{-- Pendidikan --}}
                        <div class="form-group">
                            <label class="form-label" for="education">Pendidikan</label>
                            <input type="text" id="education" name="education" class="form-input" value="{{ old('education', $resident->education ?? '') }}" placeholder="Contoh: SMA / S1">
                        </div>

                        {{-- Wilayah RT / RW --}}
                        <div class="form-group col-span-2">
                            <label class="form-label" for="rt_id">Wilayah Tempat Tinggal (RT / RW)</label>
                            <select id="rt_id" name="rt_id" class="form-select">
                                <option value="">-- Pilih Wilayah RT / RW --</option>
                                @foreach($rts as $rt)
                                    <option value="{{ $rt->id }}" {{ old('rt_id', $user->rt_id ?? ($resident->rt_id ?? '')) == $rt->id ? 'selected' : '' }}>
                                        RT {{ $rt->number }} / RW {{ $rt->rw->number ?? '-' }} (Desa {{ $rt->rw->village->name ?? '-' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Alamat Lengkap --}}
                        <div class="form-group col-span-2">
                            <label class="form-label" for="address">Alamat Lengkap / Tempat Tinggal</label>
                            <textarea id="address" name="address" class="form-textarea" rows="2" style="width: 100%; min-height: 75px; resize: vertical;" placeholder="Contoh: Jl. Melati No. 12, Blok B">{{ old('address', $user->address ?? ($resident->family->address ?? '')) }}</textarea>
                            @error('address')
                                <span style="color: var(--danger); font-size: 0.8rem;">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- No HP --}}
                        <div class="form-group">
                            <label class="form-label" for="phone_number">No. HP (Opsional jika sudah isi Email)</label>
                            <input type="text" id="phone_number" name="phone_number" class="form-input" value="{{ old('phone_number', $user->phone_number) }}" placeholder="Contoh: 08123456789">
                            @error('phone_number')
                                <span style="color: var(--danger); font-size: 0.8rem;">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div class="form-group">
                            <label class="form-label" for="email">Email</label>
                            <input type="email" id="email" name="email" class="form-input" value="{{ old('email', $user->email) }}" placeholder="Contoh: warga@email.com">
                            @error('email')
                                <span style="color: var(--danger); font-size: 0.8rem;">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Foto KTP --}}
                        <div class="form-group col-span-2">
                            <label class="form-label" for="ktp_photo">Foto KTP (Opsional, format JPG/PNG, max 2MB)</label>
                            @if($resident && $resident->ktp_photo)
                                <div style="margin-bottom: 0.5rem;">
                                    <img src="{{ asset('storage/' . $resident->ktp_photo) }}" alt="Foto KTP" style="max-height: 150px; border-radius: 8px; border: 1px solid var(--border);">
                                </div>
                            @endif
                            <input type="file" id="ktp_photo" name="ktp_photo" class="form-input" accept="image/jpeg,image/png,image/jpg" style="padding: 0.5rem;">
                            @error('ktp_photo')
                                <span style="color: var(--danger); font-size: 0.8rem;">{{ $message }}</span>
                            @enderror
                        </div>

                        @if(in_array($user->role, ['RT', 'RW', 'ADMIN_DESA', 'SUPER_ADMIN']))
                        {{-- Tanda Tangan --}}
                        <div class="form-group">
                            <label class="form-label" for="signature_photo">Tanda Tangan Pejabat (Format PNG, Transparan, Max 1MB)</label>
                            @if($user->signature_path)
                                <div style="margin-bottom: 0.5rem;">
                                    <img src="{{ asset('storage/' . $user->signature_path) }}" alt="Tanda Tangan" style="max-height: 80px; border-radius: 8px; border: 1px solid var(--border);">
                                </div>
                            @endif
                            <input type="file" id="signature_photo" name="signature_photo" class="form-input" accept="image/png" style="padding: 0.5rem;">
                            @error('signature_photo')
                                <span style="color: var(--danger); font-size: 0.8rem;">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Stempel --}}
                        <div class="form-group">
                            <label class="form-label" for="stamp_photo">Stempel Resmi (Format PNG, Transparan, Max 1MB)</label>
                            @if($user->stamp_path)
                                <div style="margin-bottom: 0.5rem;">
                                    <img src="{{ asset('storage/' . $user->stamp_path) }}" alt="Stempel" style="max-height: 80px; border-radius: 8px; border: 1px solid var(--border);">
                                </div>
                            @endif
                            <input type="file" id="stamp_photo" name="stamp_photo" class="form-input" accept="image/png" style="padding: 0.5rem;">
                            @error('stamp_photo')
                                <span style="color: var(--danger); font-size: 0.8rem;">{{ $message }}</span>
                            @enderror
                        </div>
                        @endif
                    </div>

                    <div style="margin-top: 1rem; border-top: 1px solid var(--border); padding-top: 1rem; text-align: right;">
                        <button type="submit" class="btn btn-primary">Simpan Profil & Data Diri</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Password Settings --}}
        <div class="settings-card col-span-2">
            <div class="settings-card-header">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                <span class="settings-card-title">Ganti Password</span>
            </div>
            <div class="settings-card-body">
                <form method="POST" action="{{ route('settings.password.update') }}">
                    @csrf
                    @method('PUT')

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label" for="current_password">Password Saat Ini</label>
                            <input type="password" id="current_password" name="current_password" class="form-input" required>
                            @error('current_password')
                                <span style="color: var(--danger); font-size: 0.8rem;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="password">Password Baru</label>
                            <input type="password" id="password" name="password" class="form-input" required>
                            @error('password')
                                <span style="color: var(--danger); font-size: 0.8rem;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="password_confirmation">Konfirmasi Password Baru</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" required>
                        </div>
                    </div>

                    <div style="text-align: right;">
                        <button type="submit" class="btn btn-primary">Ubah Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Telegram Settings --}}
    {{-- WhatsApp & Telegram Integration Cards --}}
    <div class="settings-card col-span-2" style="margin-top: 1.5rem;">
        <div class="settings-card-header" style="justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor" style="color: #25D366;">
                    <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91C2.13 13.66 2.59 15.36 3.45 16.86L2.05 22L7.3 20.62C8.75 21.41 10.38 21.83 12.04 21.83C17.5 21.83 21.95 17.38 21.95 11.92C21.95 9.27 20.92 6.78 19.05 4.91C17.18 3.03 14.69 2 12.04 2M12.05 3.67C14.25 3.67 16.31 4.53 17.87 6.09C19.42 7.65 20.28 9.72 20.28 11.92C20.28 16.46 16.58 20.15 12.04 20.15C10.56 20.15 9.11 19.76 7.85 19L7.55 18.83L4.43 19.65L5.26 16.61L5.06 16.29C4.24 15 3.8 13.47 3.8 11.91C3.81 7.37 7.5 3.67 12.05 3.67M9.06 7.04C8.88 7.04 8.58 7.11 8.33 7.38C8.08 7.65 7.37 8.32 7.37 9.68C7.37 11.04 8.36 12.35 8.5 12.53C8.64 12.71 10.43 15.48 13.18 16.67C13.84 16.95 14.34 17.11 14.74 17.24C15.4 17.45 16.01 17.42 16.49 17.35C17.03 17.27 18.14 16.68 18.37 16.03C18.6 15.38 18.6 14.83 18.53 14.71C18.46 14.59 18.28 14.53 18.01 14.39C17.74 14.25 16.42 13.6 16.17 13.51C15.92 13.42 15.74 13.38 15.56 13.65C15.38 13.92 14.86 14.53 14.7 14.71C14.54 14.89 14.38 14.91 14.11 14.78C13.84 14.64 12.97 14.36 11.94 13.44C11.14 12.72 10.6 11.84 10.44 11.57C10.28 11.3 10.42 11.15 10.56 11.02C10.68 10.9 10.83 10.71 10.97 10.55C11.11 10.39 11.15 10.28 11.24 10.1C11.33 9.92 11.29 9.76 11.22 9.63C11.15 9.5 10.62 8.2 10.4 7.67C10.19 7.15 9.97 7.22 9.8 7.21C9.65 7.2 9.47 7.04 9.06 7.04Z"/>
                </svg>
                <span class="settings-card-title">Koneksi Bot WhatsApp</span>
            </div>
            @if(!empty($user->whatsapp_number) || !empty($user->whatsapp_lid))
                <span class="badge badge-green">Terkoneksi</span>
            @else
                <span class="badge badge-gray">Belum Terkoneksi</span>
            @endif
        </div>
        <div class="settings-card-body">
            @php 
                $botWaNumber = config('services.whatsapp.bot_number', env('WHATSAPP_BOT_NUMBER', '628981295171'));
                $displayWa = $user->whatsapp_number;
                if (empty($displayWa) && $user->whatsapp_lid && $user->phone_number) {
                    $displayWa = \App\Services\WhatsAppService::normalizePhoneNumber($user->phone_number);
                } elseif ($displayWa && strlen($displayWa) >= 12 && !str_starts_with($displayWa, '62') && !str_starts_with($displayWa, '0') && $user->phone_number) {
                    $displayWa = \App\Services\WhatsAppService::normalizePhoneNumber($user->phone_number);
                }
                $isWaConnected = !empty($user->whatsapp_number) || !empty($user->whatsapp_lid);
                $formattedWa = ($displayWa && str_starts_with($displayWa, '62') && strlen($displayWa) >= 10) ? ('+' . substr($displayWa, 0, 2) . ' ' . substr($displayWa, 2, 3) . '-' . substr($displayWa, 5, 4) . '-' . substr($displayWa, 9)) : '';
            @endphp
            @if($isWaConnected)
                <div class="alert alert-success" style="margin-bottom: 1rem;">
                    <strong>Terkoneksi!</strong> Akun Anda terhubung dengan nomor WhatsApp: <code>{{ $displayWa ?? $user->whatsapp_number }}</code>{{ $formattedWa ? " ($formattedWa)" : '' }}. Anda akan menerima notifikasi status pengajuan surat, laporan pengaduan, dan musyawarah via WhatsApp.
                </div>
                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                    <a href="https://wa.me/{{ $botWaNumber }}?text={{ urlencode('Halo SIWIL, saya ' . ($resident->full_name ?? $user->username)) }}" target="_blank" class="btn" style="background: #25D366; color: white; display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none;">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91C2.13 13.66 2.59 15.36 3.45 16.86L2.05 22L7.3 20.62C8.75 21.41 10.38 21.83 12.04 21.83C17.5 21.83 21.95 17.38 21.95 11.92C21.95 9.27 20.92 6.78 19.05 4.91C17.18 3.03 14.69 2 12.04 2M12.05 3.67C14.25 3.67 16.31 4.53 17.87 6.09C19.42 7.65 20.28 9.72 20.28 11.92C20.28 16.46 16.58 20.15 12.04 20.15C10.56 20.15 9.11 19.76 7.85 19L7.55 18.83L4.43 19.65L5.26 16.61L5.06 16.29C4.24 15 3.8 13.47 3.8 11.91C3.81 7.37 7.5 3.67 12.05 3.67M9.06 7.04C8.88 7.04 8.58 7.11 8.33 7.38C8.08 7.65 7.37 8.32 7.37 9.68C7.37 11.04 8.36 12.35 8.5 12.53C8.64 12.71 10.43 15.48 13.18 16.67C13.84 16.95 14.34 17.11 14.74 17.24C15.4 17.45 16.01 17.42 16.49 17.35C17.03 17.27 18.14 16.68 18.37 16.03C18.6 15.38 18.6 14.83 18.53 14.71C18.46 14.59 18.28 14.53 18.01 14.39C17.74 14.25 16.42 13.6 16.17 13.51C15.92 13.42 15.74 13.38 15.56 13.65C15.38 13.92 14.86 14.53 14.7 14.71C14.54 14.89 14.38 14.91 14.11 14.78C13.84 14.64 12.97 14.36 11.94 13.44C11.14 12.72 10.6 11.84 10.44 11.57C10.28 11.3 10.42 11.15 10.56 11.02C10.68 10.9 10.83 10.71 10.97 10.55C11.11 10.39 11.15 10.28 11.24 10.1C11.33 9.92 11.29 9.76 11.22 9.63C11.15 9.5 10.62 8.2 10.4 7.67C10.19 7.15 9.97 7.22 9.8 7.21C9.65 7.2 9.47 7.04 9.06 7.04Z"/></svg>
                        Buka Chat Bot WhatsApp
                    </a>
                    <button type="button" onclick="openConnectModal('whatsapp')" class="btn btn-outline">Ubah Nomor WhatsApp</button>
                    <button type="button" onclick="disconnectWhatsApp()" class="btn btn-outline" style="color: var(--danger); border-color: rgba(239, 68, 68, 0.3);">Putuskan Koneksi</button>
                </div>
            @else
                <p style="margin-bottom: 1rem; color: var(--text-muted); font-size: 0.9rem;">Hubungkan akun Anda dengan Bot WhatsApp SIWIL (<code>+{{ $botWaNumber }}</code>) untuk menerima notifikasi cepat secara real-time dan akses layanan langsung via WhatsApp.</p>
                <button type="button" class="btn" style="background: #25D366; color: white; display: inline-flex; align-items: center; gap: 0.5rem;" onclick="openConnectModal('whatsapp')">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91C2.13 13.66 2.59 15.36 3.45 16.86L2.05 22L7.3 20.62C8.75 21.41 10.38 21.83 12.04 21.83C17.5 21.83 21.95 17.38 21.95 11.92C21.95 9.27 20.92 6.78 19.05 4.91C17.18 3.03 14.69 2 12.04 2M12.05 3.67C14.25 3.67 16.31 4.53 17.87 6.09C19.42 7.65 20.28 9.72 20.28 11.92C20.28 16.46 16.58 20.15 12.04 20.15C10.56 20.15 9.11 19.76 7.85 19L7.55 18.83L4.43 19.65L5.26 16.61L5.06 16.29C4.24 15 3.8 13.47 3.8 11.91C3.81 7.37 7.5 3.67 12.05 3.67M9.06 7.04C8.88 7.04 8.58 7.11 8.33 7.38C8.08 7.65 7.37 8.32 7.37 9.68C7.37 11.04 8.36 12.35 8.5 12.53C8.64 12.71 10.43 15.48 13.18 16.67C13.84 16.95 14.34 17.11 14.74 17.24C15.4 17.45 16.01 17.42 16.49 17.35C17.03 17.27 18.14 16.68 18.37 16.03C18.6 15.38 18.6 14.83 18.53 14.71C18.46 14.59 18.28 14.53 18.01 14.39C17.74 14.25 16.42 13.6 16.17 13.51C15.92 13.42 15.74 13.38 15.56 13.65C15.38 13.92 14.86 14.53 14.7 14.71C14.54 14.89 14.38 14.91 14.11 14.78C13.84 14.64 12.97 14.36 11.94 13.44C11.14 12.72 10.6 11.84 10.44 11.57C10.28 11.3 10.42 11.15 10.56 11.02C10.68 10.9 10.83 10.71 10.97 10.55C11.11 10.39 11.15 10.28 11.24 10.1C11.33 9.92 11.29 9.76 11.22 9.63C11.15 9.5 10.62 8.2 10.4 7.67C10.19 7.15 9.97 7.22 9.8 7.21C9.65 7.2 9.47 7.04 9.06 7.04Z"/></svg>
                    Hubungkan ke WhatsApp
                </button>
            @endif
        </div>
    </div>

    {{-- Telegram Settings --}}
    <div class="settings-card col-span-2" style="margin-top: 1.5rem;">
        <div class="settings-card-header" style="justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" style="color: #229ED9;"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
                <span class="settings-card-title">Koneksi Bot Telegram</span>
            </div>
            @if($user->telegram_id)
                <span class="badge badge-green">Terkoneksi</span>
            @else
                <span class="badge badge-gray">Belum Terkoneksi</span>
            @endif
        </div>
        <div class="settings-card-body">
            @php $botTeleName = config('services.telegram.bot_username', env('TELEGRAM_BOT_USERNAME', 'cevltBot')); @endphp
            @if($user->telegram_id)
                <div class="alert alert-success" style="margin-bottom: 1rem;">
                    <strong>Terkoneksi!</strong> Akun Anda sudah terhubung dengan bot Telegram (&#64;{{ $botTeleName }}) dengan ID Telegram: <code>{{ $user->telegram_id }}</code>.
                </div>
                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                    <a href="https://t.me/{{ $botTeleName }}" target="_blank" class="btn" style="background: #229ED9; color: white; display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none;">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
                        Buka Bot di Telegram
                    </a>
                    <button type="button" class="btn btn-outline" onclick="startTelegramConnect()">Hubungkan Ulang</button>
                    <button type="button" class="btn btn-outline" onclick="disconnectTelegram()" style="color: var(--danger); border-color: rgba(239, 68, 68, 0.3);">Putuskan Koneksi</button>
                </div>
            @else
                <p style="margin-bottom: 1rem; color: var(--text-muted); font-size: 0.9rem;">Hubungkan akun Anda dengan bot Telegram resmi (&#64;{{ $botTeleName }}) untuk mengakses layanan kependudukan dan pengaduan langsung dari Telegram.</p>
                
                <button type="button" class="btn" style="background: #229ED9; color: white; display: inline-flex; align-items: center; gap: 0.5rem;" id="btnConnectTelegram" onclick="startTelegramConnect()">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
                    Hubungkan ke Telegram
                </button>
            @endif
        </div>
    </div>


    {{-- Account Info & Data Diri Summary --}}
    <div class="data-card" style="margin-top: 1.5rem;">
        <div class="data-card-header">
            <span class="data-card-title">Informasi Akun & Ringkasan Data Diri</span>
        </div>
        <table>
            <tbody>
                <tr>
                    <td style="font-weight: 500; width: 220px;">NIK (ID Kependudukan)</td>
                    <td>
                        <code style="font-size: 0.95rem; font-weight: 700; padding: 3px 8px; background: rgba(59, 130, 246, 0.1); color: var(--accent); border-radius: 6px; letter-spacing: 0.5px;">{{ $user->username }}</code>
                        @if($resident)
                            <span style="margin-left: 8px; padding: 2px 8px; background: #d1fae5; color: #065f46; font-size: 0.75rem; font-weight: 600; border-radius: 9999px;">✓ Terdaftar di Master KTP</span>
                        @else
                            <span style="margin-left: 8px; padding: 2px 8px; background: #fef3c7; color: #92400e; font-size: 0.75rem; font-weight: 600; border-radius: 9999px; display: inline-flex; align-items: center; gap: 3px;"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg> Belum Ada Data KTP</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: 500;">Nama Lengkap</td>
                    <td style="font-weight: 600;">{{ $resident->full_name ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="font-weight: 500;">Jenis Kelamin</td>
                    <td>{{ isset($resident->gender) ? ($resident->gender === 'LAKI_LAKI' ? 'Laki-laki' : 'Perempuan') : '-' }}</td>
                </tr>
                <tr>
                    <td style="font-weight: 500;">Tempat, Tanggal Lahir</td>
                    <td>
                        {{ $resident->birth_place ?? '-' }}, 
                        {{ isset($resident->birth_date) ? \Carbon\Carbon::parse($resident->birth_date)->format('d F Y') : '-' }}
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: 500;">Agama & Status Perkawinan</td>
                    <td>{{ $resident->religion ?? '-' }} | {{ $resident->marital_status ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="font-weight: 500;">Pekerjaan & Pendidikan</td>
                    <td>{{ $resident->occupation ?? '-' }} ({{ $resident->education ?? '-' }})</td>
                </tr>
                <tr>
                    <td style="font-weight: 500;">Wilayah Tempat Tinggal</td>
                    <td>
                        @php
                            $rtNum = $user->rt->number ?? ($resident->rt->number ?? '-');
                            $rwNum = $user->rw->number ?? ($resident->rt->rw->number ?? '-');
                            $vName = $user->village->name ?? ($resident->rt->rw->village->name ?? '-');
                        @endphp
                        RT {{ $rtNum }} / RW {{ $rwNum }} — Desa {{ $vName }}
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: 500;">No. HP & Email</td>
                    <td>{{ $user->phone_number ?? '-' }} | {{ $user->email ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="font-weight: 500;">Status</td>
                    <td><span class="badge badge-blue">{{ $user->role }}</span></td>
                </tr>
                <tr>
                    <td style="font-weight: 500;">Akun Dibuat</td>
                    <td>{{ $user->created_at->format('d M Y H:i') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
@endsection
