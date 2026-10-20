@extends('layouts.app')

@section('title', 'Edit Warga - SIWIL AI')
@section('page-title', 'Kependudukan / Edit')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h1 class="page-title" style="margin-bottom: 0;">Edit Data Penduduk</h1>
        <a href="{{ route('residents.index') }}" class="btn btn-outline">Kembali</a>
    </div>

    @if($errors->any())
        <div class="alert alert-error">
            <ul style="padding-left: 1rem; margin:0;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="data-card" style="max-width: 800px; padding: 2rem; margin: 0 auto;">
        <form action="{{ route('residents.update', $resident->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="form-group">
                <label class="form-label">NIK (Nomor Induk Kependudukan)</label>
                <input type="text" name="nik" class="form-input" placeholder="16 Digit NIK" maxlength="16" value="{{ old('nik', $resident->nik) }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" name="full_name" class="form-input" placeholder="Nama Lengkap sesuai KTP" value="{{ old('full_name', $resident->full_name) }}" required>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Tempat Lahir</label>
                    <input type="text" name="birth_place" class="form-input" placeholder="Kota/Kabupaten" value="{{ old('birth_place', $resident->birth_place) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Tanggal Lahir</label>
                    <input type="date" name="birth_date" class="form-input" value="{{ old('birth_date', $resident->birth_date) }}">
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Jenis Kelamin</label>
                    <select name="gender" class="form-select" required>
                        <option value="">-- Pilih Jenis Kelamin --</option>
                        <option value="LAKI_LAKI" {{ old('gender', $resident->gender) === 'LAKI_LAKI' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="PEREMPUAN" {{ old('gender', $resident->gender) === 'PEREMPUAN' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Status Kependudukan</label>
                    <select name="status" class="form-select" required>
                        <option value="AKTIF" {{ old('status', $resident->status) === 'AKTIF' ? 'selected' : '' }}>Aktif</option>
                        <option value="PINDAH" {{ old('status', $resident->status) === 'PINDAH' ? 'selected' : '' }}>Pindah</option>
                        <option value="MENINGGAL" {{ old('status', $resident->status) === 'MENINGGAL' ? 'selected' : '' }}>Meninggal</option>
                    </select>
                </div>
            </div>

            <hr style="border: 0; border-top: 1px solid var(--border); margin: 2rem 0;">
            <h3 style="font-size: 1rem; color: var(--text-primary); margin-bottom: 1rem;">Wilayah & Promosi Jabatan</h3>

            <div class="form-group">
                <label class="form-label">Alamat / Wilayah Tugas (RT)</label>
                <select name="rt_id" class="form-select">
                    <option value="">-- Tidak Diketahui / Belum Diatur --</option>
                    @foreach($rts as $rt)
                        <option value="{{ $rt->id }}" {{ old('rt_id', $resident->rt_id) == $rt->id ? 'selected' : '' }}>
                            RT {{ $rt->number }} / RW {{ $rt->rw->number ?? '-' }} - Desa {{ $rt->rw->village->name ?? '-' }}
                        </option>
                    @endforeach
                </select>
                <small style="color: var(--text-muted); font-size: 0.8rem;">Pilih wilayah RT tempat warga ini tinggal atau bertugas.</small>
            </div>

            <div class="form-group">
                <label class="form-label">Jabatan (Promote Role)</label>
                <select name="role" class="form-select">
                    @foreach($roles as $role)
                        <option value="{{ $role }}" {{ old('role', $currentRole) === $role ? 'selected' : '' }}>
                            {{ $role === 'SUPER_ADMIN' ? 'Super Admin' : ($role === 'ADMIN_DESA' ? 'Admin Desa' : ($role === 'RW' ? 'Ketua RW' : ($role === 'RT' ? 'Ketua RT' : 'Warga Biasa'))) }}
                        </option>
                    @endforeach
                </select>
                <small style="color: var(--text-muted); font-size: 0.8rem;">Ubah role untuk mempromosikan warga. (Jika diubah selain Warga Biasa, akun sistem dengan password default 'password' akan dibuat).</small>
            </div>

            <div style="margin-top: 2rem; border-top: 1px solid var(--border); padding-top: 1.5rem; text-align:right;">
                <button type="submit" class="btn btn-primary">Update Data Warga</button>
            </div>
        </form>
    </div>
@endsection
