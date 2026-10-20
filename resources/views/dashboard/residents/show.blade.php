@extends('layouts.app')

@section('title', 'Detail Penduduk - SIWIL AI')
@section('page-title', 'Detail Penduduk')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h1 class="page-title" style="margin-bottom: 0;">Profil Warga</h1>
        <a href="{{ route('residents.index') }}" class="btn btn-outline">
            Kembali
        </a>
    </div>

    <div class="data-card" style="max-width: 800px; margin: 0 auto;">
        <div class="data-card-header" style="background: var(--bg-body);">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <div style="width: 64px; height: 64px; background: #cbd5e1; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: 700; color: white;">
                    {{ strtoupper(substr($resident->full_name, 0, 1)) }}
                </div>
                <div>
                    <h2 style="font-size: 1.25rem; margin-bottom: 0.25rem; color: var(--text-primary);">{{ $resident->full_name }}</h2>
                    <p style="color: var(--text-secondary); font-size: 0.9rem;">NIK: <strong style="color: var(--text-primary);">{{ $resident->nik }}</strong></p>
                </div>
                <div style="margin-left: auto;">
                    @if($resident->status === 'AKTIF')
                        <span class="badge badge-green" style="font-size: 0.85rem; padding: 0.4rem 1rem;">Aktif</span>
                    @elseif($resident->status === 'PINDAH')
                        <span class="badge badge-yellow" style="font-size: 0.85rem; padding: 0.4rem 1rem;">Pindah</span>
                    @else
                        <span class="badge badge-red" style="font-size: 0.85rem; padding: 0.4rem 1rem;">Meninggal</span>
                    @endif
                </div>
            </div>
        </div>

        <div style="padding: 1.5rem;">
            <h3 style="font-size: 1rem; color: var(--text-primary); margin-bottom: 1rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">Informasi Pribadi</h3>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 2rem;">
                <div>
                    <span style="display: block; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Tempat, Tanggal Lahir</span>
                    <span style="font-weight: 500;">
                        {{ $resident->birth_place ?? '-' }}, 
                        {{ $resident->birth_date ? \Carbon\Carbon::parse($resident->birth_date)->format('d F Y') : '-' }}
                    </span>
                </div>
                <div>
                    <span style="display: block; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Jenis Kelamin</span>
                    <span style="font-weight: 500;">{{ $resident->gender === 'LAKI_LAKI' ? 'Laki-laki' : ($resident->gender === 'PEREMPUAN' ? 'Perempuan' : '-') }}</span>
                </div>
                <div>
                    <span style="display: block; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Agama</span>
                    <span style="font-weight: 500;">{{ $resident->religion ?? '-' }}</span>
                </div>
                <div>
                    <span style="display: block; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Status Perkawinan</span>
                    <span style="font-weight: 500;">{{ $resident->marital_status ?? '-' }}</span>
                </div>
                <div>
                    <span style="display: block; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Pendidikan</span>
                    <span style="font-weight: 500;">{{ $resident->education ?? '-' }}</span>
                </div>
                <div>
                    <span style="display: block; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Pekerjaan</span>
                    <span style="font-weight: 500;">{{ $resident->occupation ?? '-' }}</span>
                </div>
            </div>

            <h3 style="font-size: 1rem; color: var(--text-primary); margin-bottom: 1rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">Informasi Tempat Tinggal & Keluarga</h3>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div>
                    <span style="display: block; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Nomor KK</span>
                    <span style="font-weight: 500;">{{ $resident->family->kk_number ?? '-' }}</span>
                </div>
                <div>
                    <span style="display: block; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Status dalam Keluarga</span>
                    <span style="font-weight: 500;">
                        @if($resident->is_head_of_family)
                            <span class="badge badge-blue">Kepala Keluarga</span>
                        @else
                            Anggota Keluarga
                        @endif
                    </span>
                </div>
                <div style="grid-column: span 2;">
                    <span style="display: block; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;">Wilayah RT / RW / Desa</span>
                    <span style="font-weight: 500;">
                        @php
                            $rtNum = $resident->rt->number ?? ($resident->family->rt->number ?? '-');
                            $rwNum = $resident->rt->rw->number ?? ($resident->family->rt->rw->number ?? '-');
                            $villageName = $resident->rt->rw->village->name ?? ($resident->family->rt->rw->village->name ?? '-');
                        @endphp
                        RT {{ $rtNum }} / RW {{ $rwNum }} — Desa {{ $villageName }}
                    </span>
                </div>
            </div>

            <div style="margin-top: 2rem; display: flex; gap: 0.75rem;">
                <a href="{{ route('residents.edit', $resident->id) }}" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                    <span>Edit Data Warga</span>
                </a>
            </div>
        </div>
    </div>
@endsection
