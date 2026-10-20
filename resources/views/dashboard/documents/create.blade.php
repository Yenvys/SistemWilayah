@extends('layouts.app')

@section('title', 'Buat Pengajuan - SIWIL AI')
@section('page-title', 'Pengajuan Surat / Buat')

@section('content')
<div style="max-width: 800px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-xl);">
        <h1 class="page-title" style="margin-bottom: 0;">Buat Pengajuan Surat Baru</h1>
        <a href="{{ route('documents.index') }}" class="btn btn-outline">Kembali</a>
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

    <div class="data-card" style="padding: var(--space-xl);">
        <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            @if(Auth::user()->role !== 'WARGA')
            <div class="form-group">
                <label class="form-label">NIK Pemohon</label>
                <input type="text" name="nik" class="form-input" placeholder="Masukkan NIK Warga yang mengajukan" maxlength="16" required>
            </div>
            @endif

            <div class="form-group">
                <label class="form-label">Jenis Surat</label>
                <select name="type" class="form-select" required>
                    <option value="">-- Pilih Jenis Surat --</option>
                    
                    <optgroup label="Tingkat RT (Berhenti di RT)">
                        <option value="IZIN_RENOVASI_RUMAH">Izin Renovasi Rumah Skala Kecil</option>
                        <option value="PENDATAAN_TAMU_MENGINAP">Pendataan Warga Baru / Tamu Menginap</option>
                        <option value="IURAN_KAS_RT">Iuran Lingkungan / Kas RT</option>
                        <option value="SURAT_REKOMENDASI_INTERNAL">Surat Rekomendasi Internal (Fasilitas RT)</option>
                    </optgroup>

                    <optgroup label="Tingkat RW (Berhenti di RW)">
                        <option value="SURAT_IZIN_KERAMAIAN">Surat Izin Keramaian (Hajatan/Acara)</option>
                        <option value="SURAT_PENGANTAR_KEHILANGAN">Surat Pengantar Kehilangan Internal</option>
                        <option value="MEDIASI_SENGKETA_WARGA">Mediasi Sengketa Antar-Warga</option>
                    </optgroup>

                    <optgroup label="Tingkat Kelurahan/Desa (Resmi & Legal)">
                        <option value="SURAT_KETERANGAN_USAHA">Surat Keterangan Usaha (SKU)</option>
                        <option value="SURAT_KETERANGAN_TIDAK_MAMPU">Surat Keterangan Tidak Mampu (SKTM)</option>
                        <option value="SURAT_PENGANTAR_NIKAH">Surat Pengantar Nikah (Formulir N1-N4)</option>
                        <option value="SURAT_KETERANGAN_DOMISILI">Surat Keterangan Domisili Perusahaan/Lembaga</option>
                        <option value="SURAT_KETERANGAN_WARIS">Surat Keterangan Waris (SKW)</option>
                    </optgroup>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Keperluan / Keterangan Tambahan</label>
                <textarea name="notes" class="form-textarea" placeholder="Tuliskan alasan pengajuan secara detail (contoh: Untuk melamar kerja)"></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Dokumen Persyaratan (Opsional)</label>
                <input type="file" name="attachment" class="form-input" accept=".pdf,image/*" style="padding: 0.5rem;">
                <small style="color: var(--text-muted); display: block; margin-top: 0.25rem;">Format: PDF/JPG/PNG. Maks: 5MB.</small>
            </div>

            <div style="margin-top: 2rem; text-align:right;">
                <button type="submit" class="btn btn-primary">Kirim Pengajuan</button>
            </div>
        </form>
    </div>
</div>
@endsection
