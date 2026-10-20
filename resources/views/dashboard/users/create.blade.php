@extends('layouts.app')

@section('title', 'Tambah User - SIWIL AI')
@section('page-title', 'Tambah User')

@section('content')
    <h1 class="page-title">Tambah User</h1>

    <div class="data-card">
        <div class="data-card-header">
            <span class="data-card-title">Form Tambah User</span>
        </div>
        <div style="padding: 1.25rem;">
            <form method="POST" action="{{ route('users.store') }}">
                @csrf

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="username">NIK (Nomor Induk Kependudukan) <span style="color: var(--danger);">*</span></label>
                        <input type="text" id="username" name="username" class="form-input" value="{{ old('username') }}" placeholder="16 Digit NIK" required>
                        @error('username')
                            <span style="color: var(--danger); font-size: 0.8rem;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="full_name">Nama Lengkap</label>
                        <input type="text" id="full_name" name="full_name" class="form-input" value="{{ old('full_name') }}" placeholder="Nama Lengkap User">
                        @error('full_name')
                            <span style="color: var(--danger); font-size: 0.8rem;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="phone_number">No. HP</label>
                        <input type="text" id="phone_number" name="phone_number" class="form-input" value="{{ old('phone_number') }}" placeholder="Contoh: 08123456789">
                        @error('phone_number')
                            <span style="color: var(--danger); font-size: 0.8rem;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-input" value="{{ old('email') }}" placeholder="warga@email.com">
                        @error('email')
                            <span style="color: var(--danger); font-size: 0.8rem;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group" style="grid-column: span 2;">
                        <label class="form-label" for="address">Alamat</label>
                        <textarea id="address" name="address" class="form-input" rows="2" placeholder="Alamat lengkap (nama jalan, no rumah, RT/RW)...">{{ old('address') }}</textarea>
                        @error('address')
                            <span style="color: var(--danger); font-size: 0.8rem;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password">Password <span style="color: var(--danger);">*</span></label>
                        <input type="password" id="password" name="password" class="form-input" required>
                        @error('password')
                            <span style="color: var(--danger); font-size: 0.8rem;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password_confirmation">Konfirmasi Password <span style="color: var(--danger);">*</span></label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="role">Role <span style="color: var(--danger);">*</span></label>
                        <select id="role" name="role" class="form-select" required>
                            <option value="">Pilih Role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role }}" {{ old('role') === $role ? 'selected' : '' }}>{{ $role }}</option>
                            @endforeach
                        </select>
                        @error('role')
                            <span style="color: var(--danger); font-size: 0.8rem;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="village_id">Desa</label>
                        <select id="village_id" name="village_id" class="form-select">
                            <option value="">-- Pilih Desa --</option>
                            @foreach($villages as $village)
                                <option value="{{ $village->id }}" {{ old('village_id') === $village->id ? 'selected' : '' }}>{{ $village->name }}</option>
                            @endforeach
                        </select>
                        @error('village_id')
                            <span style="color: var(--danger); font-size: 0.8rem;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="rw_id">RW</label>
                        <select id="rw_id" name="rw_id" class="form-select">
                            <option value="">-- Pilih RW --</option>
                            @foreach($rws as $rw)
                                <option value="{{ $rw->id }}" {{ old('rw_id') === $rw->id ? 'selected' : '' }}>RW {{ $rw->number }} - {{ $rw->village->name ?? '' }}</option>
                            @endforeach
                        </select>
                        @error('rw_id')
                            <span style="color: var(--danger); font-size: 0.8rem;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="rt_id">RT</label>
                        <select id="rt_id" name="rt_id" class="form-select">
                            <option value="">-- Pilih RT --</option>
                            @foreach($rts as $rt)
                                <option value="{{ $rt->id }}" {{ old('rt_id') === $rt->id ? 'selected' : '' }}>RT {{ $rt->number }} - RW {{ $rt->rw->number ?? '' }}</option>
                            @endforeach
                        </select>
                        @error('rt_id')
                            <span style="color: var(--danger); font-size: 0.8rem;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div style="display: flex; gap: 0.75rem; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('users.index') }}" class="btn btn-outline">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
