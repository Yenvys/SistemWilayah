@extends('layouts.app')

@php
    $isEdit = isset($announcement);
@endphp

@section('title', ($isEdit ? 'Edit Pengumuman' : 'Buat Pengumuman Baru') . ' - SIWIL AI')
@section('page-title', $isEdit ? 'Edit Pengumuman Desa' : 'Buat Pengumuman Desa')

@section('content')
<div style="max-width: 800px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 class="page-title" style="margin-bottom: 0.25rem;">
                {{ $isEdit ? 'Edit Pengumuman & Informasi Desa' : 'Buat Pengumuman & Informasi Desa' }}
            </h1>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0;">
                Informasi yang Anda buat di sini akan disiarkan dan ditampilkan ke seluruh jajaran warga, RT, RW, dan aparat desa.
            </p>
        </div>
        <a href="{{ route('announcements.index') }}" class="btn btn-outline btn-sm" style="display: inline-flex; align-items: center; gap: 0.4rem;">
            &larr; Kembali
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger" style="margin-bottom: 1.5rem;">
            <ul style="margin: 0; padding-left: 1.25rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="data-card">
        <div class="data-card-body" style="padding: 2rem;">
            <form action="{{ $isEdit ? route('announcements.update', $announcement->id) : route('announcements.store') }}" method="POST">
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label class="form-label" for="title" style="font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem; display: block;">
                        Judul Pengumuman <span style="color: var(--danger);">*</span>
                    </label>
                    <input type="text" name="title" id="title" class="form-input" 
                        value="{{ old('title', $announcement->title ?? '') }}" 
                        placeholder="Contoh: Jadwal Penyaluran BLT Dana Desa Tahap 2 / Kerja Bakti Lingkungan" 
                        required autofocus style="width: 100%;">
                </div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label class="form-label" for="content" style="font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem; display: block;">
                        Isi Informasi & Pengumuman Lengkap <span style="color: var(--danger);">*</span>
                    </label>
                    <textarea name="content" id="content" rows="9" class="form-input" 
                        placeholder="Tuliskan detail pengumuman secara rinci, termasuk waktu pelaksanaan, tempat, syarat, atau instruksi bagi warga..." 
                        required style="width: 100%; resize: vertical; line-height: 1.6; font-family: inherit;">{{ old('content', $announcement->content ?? '') }}</textarea>
                </div>

                <div style="background: rgba(37, 99, 235, 0.05); border: 1px solid rgba(37, 99, 235, 0.18); border-radius: 12px; padding: 1.25rem; margin-bottom: 2rem;">
                    <label style="display: flex; align-items: flex-start; gap: 0.75rem; cursor: pointer; user-select: none;">
                        <input type="checkbox" name="is_active" value="1" 
                            {{ old('is_active', $announcement->is_active ?? true) ? 'checked' : '' }} 
                            style="width: 18px; height: 18px; margin-top: 2px; accent-color: var(--accent-1); cursor: pointer;">
                        <div>
                            <div style="font-weight: 700; color: var(--text-primary); font-size: 0.95rem;">
                                Terbitkan Pengumuman Secara Publik
                            </div>
                            <div style="font-size: 0.82rem; color: var(--text-secondary); margin-top: 0.25rem; line-height: 1.45;">
                                Jika dicentang, pengumuman ini akan langsung muncul di dashboard <strong>semua warga, RT, RW, dan perangkat desa</strong>, serta otomatis memicu siaran notifikasi lonceng dan email. Jika tidak dicentang, pengumuman tersimpan sebagai draf.
                            </div>
                        </div>
                    </label>
                </div>

                <div style="display: flex; justify-content: flex-end; align-items: center; gap: 1rem; border-top: 1px solid var(--border-glass); padding-top: 1.5rem;">
                    <a href="{{ route('announcements.index') }}" class="btn btn-outline">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.5rem; font-weight: 600;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        {{ $isEdit ? 'Simpan Perubahan' : 'Terbitkan Pengumuman' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
