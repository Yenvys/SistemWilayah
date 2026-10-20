@extends('layouts.app')

@section('title', 'Tambah Warga - SIWIL AI')
@section('page-title', 'Kependudukan / Tambah')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h1 class="page-title" style="margin-bottom: 0;">Tambah Data Penduduk</h1>
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

    <div class="data-card" style="max-width: 800px; padding: 2rem;">
        <form action="{{ route('residents.store') }}" method="POST">
            @csrf
            
            <div class="form-group">
                <label class="form-label">NIK (Nomor Induk Kependudukan)</label>
                <input type="text" name="nik" class="form-input" placeholder="16 Digit NIK" maxlength="16" value="{{ old('nik') }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" name="full_name" class="form-input" placeholder="Nama Lengkap sesuai KTP" value="{{ old('full_name') }}" required>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Tempat Lahir</label>
                    <input type="text" name="birth_place" class="form-input" placeholder="Kota/Kabupaten" value="{{ old('birth_place') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Tanggal Lahir</label>
                    <input type="date" name="birth_date" class="form-input" value="{{ old('birth_date') }}">
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Jenis Kelamin</label>
                    <select name="gender" class="form-select" required>
                        <option value="">-- Pilih Jenis Kelamin --</option>
                        <option value="LAKI_LAKI" {{ old('gender') === 'LAKI_LAKI' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="PEREMPUAN" {{ old('gender') === 'PEREMPUAN' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Status Kependudukan</label>
                    <select name="status" class="form-select" required>
                        <option value="AKTIF" {{ old('status') === 'AKTIF' ? 'selected' : '' }}>Aktif</option>
                        <option value="PINDAH" {{ old('status') === 'PINDAH' ? 'selected' : '' }}>Pindah</option>
                        <option value="MENINGGAL" {{ old('status') === 'MENINGGAL' ? 'selected' : '' }}>Meninggal</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Wilayah RT / RW</label>
                <select name="rt_id" class="form-select">
                    <option value="">-- Pilih Wilayah RT / RW --</option>
                    @foreach($rts as $rt)
                        <option value="{{ $rt->id }}" {{ old('rt_id') == $rt->id ? 'selected' : '' }}>
                            RT {{ $rt->name }} / RW {{ $rt->rw->name ?? '-' }} ({{ $rt->rw->village->name ?? 'Desa' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="margin-top: 2rem; border-top: 1px solid var(--border); padding-top: 1.5rem; text-align:right;">
                <button type="submit" class="btn btn-primary">Simpan Data Warga</button>
            </div>
        </form>
    </div>
@endsection
