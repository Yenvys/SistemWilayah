@extends('layouts.app')

@section('title', 'Data Kependudukan - SIWIL AI')
@section('page-title', 'Kependudukan')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h1 class="page-title" style="margin-bottom: 0;">Data Penduduk</h1>
        <a href="{{ route('residents.create') }}" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Warga
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="data-card">
        <div class="data-card-header">
            <form action="{{ route('residents.index') }}" method="GET" data-ajax-filter="true" data-ajax-target="#residentsTableContainer" style="display: flex; gap: 0.5rem; width: 100%; max-width: 380px;">
                <input type="text" name="search" class="form-input" placeholder="Cari NIK atau Nama..." value="{{ request('search') }}" style="padding: 0.5rem; font-size:0.85rem;">
                <button type="submit" class="btn btn-primary btn-sm">Cari</button>
                @if(request('search'))
                    <a href="{{ route('residents.index') }}" class="btn btn-outline btn-sm reset-filter-btn">Reset</a>
                @endif
            </form>
        </div>

        <div id="residentsTableContainer">
        @if($residents->count() > 0)
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>NIK</th>
                            <th>Nama Lengkap</th>
                            <th>Gender</th>
                            <th>Jabatan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($residents as $resident)
                        <tr>
                            <td>{{ $resident->nik }}</td>
                            <td style="font-weight:500; color:var(--text-primary);">{{ $resident->full_name }}</td>
                            <td>{{ $resident->gender === 'LAKI_LAKI' ? 'Laki-laki' : 'Perempuan' }}</td>
                            <td>
                                @if($resident->user && $resident->user->role !== 'WARGA')
                                    @php
                                        $r = $resident->user->role;
                                        $label = $r === 'SUPER_ADMIN' ? 'Super Admin' : ($r === 'ADMIN_DESA' ? 'Admin Desa' : ($r === 'RW' ? 'Ketua RW' : 'Ketua RT'));
                                        $color = $r === 'SUPER_ADMIN' ? 'badge-red' : ($r === 'ADMIN_DESA' ? 'badge-blue' : ($r === 'RW' ? 'badge-green' : 'badge-yellow'));
                                    @endphp
                                    <span class="badge {{ $color }}">{{ $label }}</span>
                                @else
                                    <span style="color: var(--text-muted); font-size: 0.85rem;">Warga Biasa</span>
                                @endif
                            </td>
                            <td>
                                @if($resident->status === 'AKTIF')
                                    <span class="badge badge-green">Aktif</span>
                                @elseif($resident->status === 'PINDAH')
                                    <span class="badge badge-yellow">Pindah</span>
                                @else
                                    <span class="badge badge-red">Meninggal</span>
                                @endif
                            </td>
                            <td>
                                <div style="display:inline-flex; gap:0.4rem; align-items:center;">
                                    <a href="{{ route('residents.show', $resident->id) }}" class="btn-icon btn-icon-view" title="Detail Data Penduduk" aria-label="Detail Data Penduduk">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="padding: 1rem; border-top: 1px solid var(--border);">
                {{ $residents->links() }}
            </div>
        @else
            <div class="empty-state">
                Data penduduk tidak ditemukan.
            </div>
        @endif
        </div>
    </div>
@endsection
