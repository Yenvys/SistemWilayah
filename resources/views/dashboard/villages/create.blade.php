@extends('layouts.app')

@section('title', 'Tambah Desa - SIWIL AI')
@section('page-title', 'Tambah Desa')

@section('content')
    <h1 class="page-title">Tambah Desa</h1>

    <div class="data-card">
        <div class="data-card-header">
            <span class="data-card-title">Form Data Desa</span>
        </div>
        <div style="padding: 1.25rem;">
            <form method="POST" action="{{ route('villages.store') }}">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="name">Nama Desa <span style="color: var(--danger);">*</span></label>
                    <input type="text" id="name" name="name" class="form-input" value="{{ old('name') }}" required>
                    @error('name')
                        <span style="color: var(--danger); font-size: 0.8rem;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="code">Kode Desa</label>
                    <input type="text" id="code" name="code" class="form-input" value="{{ old('code') }}" placeholder="Contoh: 3201010001">
                    @error('code')
                        <span style="color: var(--danger); font-size: 0.8rem;">{{ $message }}</span>
                    @enderror
                </div>

                <div style="display: flex; gap: 0.75rem; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('villages.index') }}" class="btn btn-outline">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
