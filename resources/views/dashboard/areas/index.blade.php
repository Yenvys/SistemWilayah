@extends('layouts.app')

@section('title', (Auth::user()->role === 'RT' ? 'Data & Akun Warga RT' : (Auth::user()->role === 'RW' ? 'Wilayah & Warga RW' : 'Wilayah & Kependudukan')) . ' - SIWIL AI')
@section('page-title', Auth::user()->role === 'RT' ? 'Data & Akun Warga RT' : (Auth::user()->role === 'RW' ? 'Wilayah & Warga RW' : 'Wilayah & Kependudukan'))

@section('styles')
<style>
    .hub-twin-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
        align-items: stretch;
    }

    .hierarchy-twin-tables {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.25rem;
        flex: 1;
    }

    .card-header-actions {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    @media (max-width: 1100px) {
        .hub-twin-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .hub-twin-grid {
            gap: 1.25rem;
        }

        .hierarchy-twin-tables {
            grid-template-columns: 1fr !important;
            gap: 1.25rem !important;
        }

        .card-header-actions {
            width: 100% !important;
            display: flex !important;
            gap: 0.5rem !important;
        }

        .card-header-actions .btn {
            flex: 1 1 0 !important;
            width: auto !important;
            justify-content: center !important;
            white-space: nowrap !important;
            font-size: 0.8rem !important;
            padding: 0.45rem 0.65rem !important;
        }

        .data-card-header {
            gap: 0.75rem !important;
        }

        .table-responsive {
            margin: 0 -0.25rem;
            width: calc(100% + 0.5rem);
            -webkit-overflow-scrolling: touch;
        }

        .table-responsive th,
        .table-responsive td {
            padding: 0.65rem 0.5rem !important;
        }

        .filter-bar form > div {
            width: 100% !important;
            max-width: 100% !important;
            flex: 1 1 100% !important;
        }

        .filter-bar form button,
        .filter-bar form a.btn {
            flex: 1 1 auto;
            justify-content: center;
        }
    }

    .filter-bar {
        display: flex;
        gap: 0.75rem;
        align-items: center;
        flex-wrap: wrap;
        padding: 0.75rem 1.25rem;
        background: var(--bg-body);
        border: 1px solid var(--border-glass);
        border-radius: 10px;
        margin-bottom: 1rem;
    }

    .filter-dropdown-col {
        width: 160px;
        flex: 0 0 160px;
        box-sizing: border-box;
    }
    .filter-dropdown-col .custom-dropdown-container {
        width: 100% !important;
    }
    @media (max-width: 991px) {
        .filter-dropdown-col {
            flex: 1 1 calc(50% - 0.5rem);
            min-width: 140px;
            max-width: 100%;
        }
    }
    @media (max-width: 576px) {
        .filter-dropdown-col {
            width: 100% !important;
            flex: 1 1 100% !important;
            max-width: 100% !important;
        }
    }

    /* Modal System */
    .siwil-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.75);
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.25s ease, visibility 0.25s ease;
        padding: 1rem;
    }

    .siwil-modal-overlay.active {
        opacity: 1;
        visibility: visible;
    }

    .siwil-modal-dialog {
        background: var(--bg-card);
        border: 1px solid var(--border-glass, rgba(255, 255, 255, 0.1));
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
        border-radius: 16px;
        width: 100%;
        max-width: 500px;
        max-height: 90vh;
        display: flex;
        flex-direction: column;
        transform: scale(0.95) translateY(10px);
        transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        overflow: hidden;
    }

    .siwil-modal-overlay.active .siwil-modal-dialog {
        transform: scale(1) translateY(0);
    }

    .siwil-modal-header {
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid var(--border-glass, rgba(255, 255, 255, 0.08));
        background: var(--bg-card);
    }

    .siwil-modal-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0;
    }

    .siwil-modal-close {
        background: var(--bg-body);
        border: 1px solid var(--border-glass);
        color: var(--text-muted);
        cursor: pointer;
        width: 34px;
        height: 34px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .siwil-modal-close:hover {
        background: rgba(239, 68, 68, 0.12);
        border-color: rgba(239, 68, 68, 0.35);
        color: var(--danger, #ef4444);
    }

    .siwil-modal-body {
        padding: 1.25rem 1.5rem;
        overflow-y: auto;
        flex: 1;
    }

    .siwil-modal-footer {
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.75rem;
        padding: 1rem 1.5rem;
        border-top: 1px solid var(--border-glass, rgba(255, 255, 255, 0.08));
        background: var(--bg-card);
    }
</style>
@endsection

@section('content')
    {{-- Header --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 class="page-title" style="margin-bottom: 0.25rem; font-size: 1.75rem; font-weight: 800; color: var(--text-primary);">
                @if(Auth::user()->role === 'RT')
                    Data Warga RT {{ $rtObj->number ?? (Auth::user()->resident->rt->number ?? '') }}
                @elseif(Auth::user()->role === 'RW')
                    Data Warga RW {{ $rwObj->number ?? (Auth::user()->resident->rt->rw->number ?? '') }}
                @else
                    Wilayah & Kependudukan
                @endif
            </h1>
            <p style="color: var(--text-secondary); font-size: 0.92rem; margin: 0; font-weight: 500;">
                @if(in_array(Auth::user()->role, ['RT', 'RW']))
                    Pusat integrasi data kependudukan resmi dan pengelolaan akun login aplikasi SIWIL untuk warga {{ Auth::user()->role }} Anda.
                @else
                    Monitoring terpadu: Wilayah Desa di sisi kiri, Hierarki RW/RT di sisi kanan, serta Data Kependudukan di bagian bawah.
                @endif
            </p>
        </div>

        <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
            @if(Auth::user()->role === 'SUPER_ADMIN')
                <a href="{{ route('villages.create') }}" class="btn btn-outline" style="background: var(--bg-card); border: 1px solid var(--border-glass); color: var(--text-primary); font-weight: 600;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;"><path d="M12 5v14M5 12h14"/></svg>
                    Tambah Desa
                </a>
            @endif
            @if($canAccessPenduduk)
                <a href="{{ route('residents.create') }}" class="btn btn-primary" style="font-weight: 600;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Tambah Warga
                </a>
            @endif
        </div>
    </div>

    {{-- Session Alerts --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif
    @if(session('info'))
        <div class="alert alert-info" style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); color: #3b82f6; padding: 0.75rem 1.25rem; border-radius: 8px; margin-bottom: 1rem;">
            {{ session('info') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-error" style="margin-bottom: 1.25rem;">
            <ul style="margin: 0; padding-left: 1.25rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Ringkasan Statistik Utama --}}
    <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); margin-bottom: 1.5rem;">
        @if($canAccessDesa)
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Total Desa</span>
                <span class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/></svg>
                </span>
            </div>
            <div class="stat-value">{{ $stats['villages'] ?? 0 }} <span style="font-size: 0.85rem; font-weight: 500; color: var(--text-muted);">Desa</span></div>
        </div>
        @endif

        @if($canAccessRwRt)
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Total RW & RT</span>
                <span class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                </span>
            </div>
            <div class="stat-value" style="display: flex; align-items: baseline; gap: 0.5rem;">
                {{ $stats['rws'] ?? 0 }} <span style="font-size: 0.9rem; color: var(--text-muted); font-weight: 500;">RW</span>
                <span style="color: var(--border-glass);">|</span>
                {{ $stats['rts'] ?? 0 }} <span style="font-size: 0.9rem; color: var(--text-muted); font-weight: 500;">RT</span>
            </div>
        </div>
        @elseif(Auth::user()->role === 'RT')
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Wilayah RT Anda</span>
                <span class="stat-icon" style="color: var(--accent-1);">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                </span>
            </div>
            <div class="stat-value" style="font-size: 1.25rem;">
                RT {{ $rtObj->number ?? (Auth::user()->resident->rt->number ?? '-') }} / RW {{ $rtObj->rw->number ?? (Auth::user()->resident->rt->rw->number ?? '-') }}
            </div>
            <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 0.25rem;">
                Desa {{ $rtObj->rw->village->name ?? '-' }}
            </div>
        </div>
        @endif

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">{{ Auth::user()->role === 'RT' ? 'Total Penduduk RT' : 'Total Penduduk' }}</span>
                <span class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </span>
            </div>
            <div class="stat-value">{{ $stats['residents'] ?? 0 }} <span style="font-size: 0.85rem; font-weight: 500; color: var(--text-muted);">Jiwa</span></div>
            <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 0.25rem;">
                {{ $stats['male'] ?? 0 }} Laki-laki &bull; {{ $stats['female'] ?? 0 }} Perempuan
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">{{ Auth::user()->role === 'RT' ? 'Kepala Keluarga RT' : 'Kepala Keluarga' }}</span>
                <span class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                </span>
            </div>
            <div class="stat-value">{{ $stats['families'] ?? 0 }} <span style="font-size: 0.85rem; font-weight: 500; color: var(--text-muted);">KK</span></div>
        </div>

        @if(in_array(Auth::user()->role, ['RT', 'RW']))
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Akun Login Warga</span>
                <span class="stat-icon" style="color: var(--accent-1);">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
                </span>
            </div>
            <div class="stat-value">{{ $stats['total_users'] ?? 0 }} <span style="font-size: 0.85rem; font-weight: 500; color: var(--text-muted);">Akun</span></div>
            <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 0.25rem;">
                <span style="color: var(--success); font-weight: 600;">{{ $stats['active_users'] ?? 0 }}</span> Aktif &bull; {{ ($stats['residents'] ?? 0) - ($stats['total_users'] ?? 0) }} Belum Punya
            </div>
        </div>

        <div class="stat-card" style="{{ ($stats['pending_users'] ?? 0) > 0 ? 'border: 1px solid rgba(234, 179, 8, 0.4); background: rgba(234, 179, 8, 0.05);' : '' }}">
            <div class="stat-header">
                <span class="stat-title">Akun Menunggu Verifikasi</span>
                <span class="stat-icon" style="color: var(--warning);">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </span>
            </div>
            <div class="stat-value" style="{{ ($stats['pending_users'] ?? 0) > 0 ? 'color: var(--warning);' : '' }}">
                {{ $stats['pending_users'] ?? 0 }} <span style="font-size: 0.85rem; font-weight: 500; color: var(--text-muted);">Pending</span>
            </div>
            <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 0.25rem;">
                @if(($stats['pending_users'] ?? 0) > 0)
                    <span style="color: var(--warning); font-weight: 600;">Ada akun warga baru!</span>
                @else
                    Semua akun warga terverifikasi
                @endif
            </div>
        </div>
        @endif
    </div>

    {{-- BARIS ATAS: Wilayah Desa (Kiri) & Hierarki RW/RT (Kanan). Jika Desa tidak diakses (e.g. RW), Hierarki RW/RT otomatis FULL WIDTH --}}
    @if($canAccessDesa || $canAccessRwRt)
    <div class="{{ $canAccessDesa ? 'hub-twin-grid' : '' }}" style="{{ !$canAccessDesa ? 'margin-bottom: 1.5rem;' : '' }}">
        
        {{-- BOX 1: DATA WILAYAH DESA (KIRI) --}}
        @if($canAccessDesa)
        <div class="data-card" style="margin-bottom: 0; display: flex; flex-direction: column;">
            <div class="data-card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.65rem;">
                <div style="display: flex; align-items: center; gap: 0.6rem;">
                    <div style="width: 34px; height: 34px; border-radius: 8px; background: rgba(59, 130, 246, 0.12); color: #3b82f6; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/></svg>
                    </div>
                    <div>
                        <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                            <h3 class="data-card-title" style="margin: 0;">Wilayah Desa</h3>
                            <span id="villageCountBadge" class="badge badge-blue" style="font-size: 0.72rem; font-weight: 700; padding: 2px 7px;">{{ $villages->total() }} Desa</span>
                        </div>
                        <div style="font-size: 0.78rem; color: var(--text-secondary); margin-top: 2px;">Daftar data desa terdaftar</div>
                    </div>
                </div>
                @if(Auth::user()->role === 'SUPER_ADMIN')
                <div class="card-header-actions">
                    <a href="{{ route('villages.create') }}" class="btn btn-primary btn-sm" style="font-size: 0.8rem; padding: 0.45rem 0.75rem; display: inline-flex; align-items: center; gap: 0.4rem; justify-content: center;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:13px;height:13px;"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        <span>Tambah Desa</span>
                    </a>
                </div>
                @endif
            </div>

            <div class="data-card-body" style="flex: 1; display: flex; flex-direction: column;">
                {{-- Filter Desa --}}
                <div style="margin-bottom: 0.85rem;">
                    <form method="GET" action="{{ route('areas.index') }}" data-ajax-filter="true" data-ajax-target="#villageTableContainer" data-count-badge="#villageCountBadge" style="display: flex; gap: 0.5rem; align-items: center; width: 100%;">
                        <input type="text" name="search_village" class="form-input" placeholder="Cari nama atau kode desa..." value="{{ request('search_village') }}" style="flex: 1; min-width: 0; font-size: 0.85rem; padding: 0.45rem 0.75rem;">
                        <button type="submit" class="btn btn-primary btn-sm" style="flex-shrink: 0; padding: 0.45rem 0.85rem;">Cari</button>
                        @if(request('search_village'))
                            <a href="{{ route('areas.index') }}" class="btn btn-outline btn-sm reset-filter-btn" style="flex-shrink: 0;">Reset</a>
                        @endif
                    </form>
                </div>

                <div id="villageTableContainer" style="flex: 1; display: flex; flex-direction: column;">
                    {{-- Tabel Desa --}}
                    <div class="table-responsive" style="flex: 1;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Desa</th>
                                    <th>Kode</th>
                                    <th>RW</th>
                                    <th>Akun</th>
                                    @if(Auth::user()->role === 'SUPER_ADMIN')
                                    <th style="text-align: right;">Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($villages as $village)
                                <tr>
                                    <td style="font-weight: 600; color: var(--text-primary); font-size: 0.88rem;">{{ $village->name }}</td>
                                    <td><span class="badge badge-gray" style="font-family: monospace; font-size: 0.72rem;">{{ $village->code ?? '-' }}</span></td>
                                    <td><span class="badge badge-blue" style="font-size: 0.7rem;">{{ $village->rws_count }} RW</span></td>
                                    <td style="font-size: 0.8rem; color: var(--text-secondary); white-space: nowrap;">{{ $village->users_count }} Akun</td>
                                    @if(Auth::user()->role === 'SUPER_ADMIN')
                                    <td style="text-align: right;">
                                        <div style="display: inline-flex; gap: 0.35rem; align-items: center; justify-content: flex-end;">
                                            <a href="{{ route('villages.edit', $village) }}" class="btn-icon btn-icon-edit" title="Edit Desa" aria-label="Edit Desa">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                </svg>
                                            </a>
                                            <form method="POST" action="{{ route('villages.destroy', $village) }}" data-confirm="Yakin ingin menghapus desa ini?" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-icon btn-icon-danger" title="Hapus Desa" aria-label="Hapus Desa">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <polyline points="3 6 5 6 21 6"></polyline>
                                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                    @endif
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="empty-state" style="padding: 1.5rem 1rem;">
                                        <div>Belum ada data desa terdaftar.</div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div style="margin-top: 0.75rem;">
                        {{ $villages->links() }}
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- BOX 2: DATA RW & RT (KANAN) --}}
        <div class="data-card" style="margin-bottom: 0; display: flex; flex-direction: column;">
            <div class="data-card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.65rem;">
                <div style="display: flex; align-items: center; gap: 0.6rem;">
                    <div style="width: 34px; height: 34px; border-radius: 8px; background: rgba(16, 185, 129, 0.12); color: #10b981; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                    </div>
                    <div>
                        <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                            <h3 class="data-card-title" style="margin: 0;">Hierarki RW & RT</h3>
                            <span class="badge badge-green" style="font-size: 0.72rem; font-weight: 700; padding: 2px 7px;">{{ $rws->total() }} RW / {{ $rts->total() }} RT</span>
                        </div>
                        <div style="font-size: 0.78rem; color: var(--text-secondary); margin-top: 2px;">Pembagian rukun warga & rukun tetangga</div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                @if(in_array(Auth::user()->role, ['SUPER_ADMIN', 'ADMIN_DESA']))
                <div class="card-header-actions">
                    <button type="button" class="btn btn-primary btn-sm" onclick="openCreateRwModal()" style="font-size: 0.8rem; padding: 0.45rem 0.75rem; display: inline-flex; align-items: center; gap: 0.4rem; justify-content: center;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:13px;height:13px;"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        <span>Tambah RW</span>
                    </button>
                    <button type="button" class="btn btn-primary btn-sm" onclick="openCreateRtModal()" style="font-size: 0.8rem; padding: 0.45rem 0.75rem; display: inline-flex; align-items: center; gap: 0.4rem; justify-content: center;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:13px;height:13px;"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        <span>Tambah RT</span>
                    </button>
                </div>
                @endif
            </div>

            <div class="data-card-body" style="flex: 1; display: flex; flex-direction: column;">
                {{-- Tabel RW & RT Berdampingan di Desktop, Vertikal Rapi di Mobile --}}
                <div class="hierarchy-twin-tables">
                    {{-- Sub Tabel RW --}}
                    <div style="background: var(--bg-body); border: 1px solid var(--border-glass); border-radius: 10px; padding: 0.85rem;">
                        <div style="font-weight: 700; font-size: 0.82rem; color: var(--text-primary); margin-bottom: 0.65rem; display: flex; justify-content: space-between; align-items: center;">
                            <span style="display: inline-flex; align-items: center; gap: 0.35rem;">
                                <span style="width: 7px; height: 7px; border-radius: 50%; background: var(--accent-1);"></span>
                                DAFTAR RW
                            </span>
                            <span style="font-size: 0.72rem; color: var(--text-secondary); background: var(--bg-card); border: 1px solid var(--border-glass); padding: 1px 7px; border-radius: 4px;">{{ $rws->total() }} Total</span>
                        </div>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>RW</th>
                                        <th>Jml RT</th>
                                        @if(in_array(Auth::user()->role, ['SUPER_ADMIN', 'ADMIN_DESA']))
                                        <th style="text-align: right;">Aksi</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($rws as $rw)
                                    <tr>
                                        <td style="font-weight: 600; font-size: 0.85rem; color: var(--text-primary);">RW {{ $rw->number }}</td>
                                        <td><span class="badge badge-green" style="font-size: 0.68rem; padding: 1px 6px;">{{ $rw->rts_count }} RT</span></td>
                                        @if(in_array(Auth::user()->role, ['SUPER_ADMIN', 'ADMIN_DESA']))
                                        <td style="text-align: right;">
                                            <form method="POST" action="{{ route('areas.rw.destroy', $rw) }}" data-confirm="Hapus RW ini beserta RT di dalamnya?" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-icon btn-icon-danger" title="Hapus RW" aria-label="Hapus RW">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                                </button>
                                            </form>
                                        </td>
                                        @endif
                                    </tr>
                                    @empty
                                    <tr><td colspan="3" class="empty-state" style="padding: 1rem 0.5rem; font-size: 0.8rem;">Belum ada data RW</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div style="margin-top: 0.5rem;">
                            {{ $rws->links() }}
                        </div>
                    </div>

                    {{-- Sub Tabel RT --}}
                    <div style="background: var(--bg-body); border: 1px solid var(--border-glass); border-radius: 10px; padding: 0.85rem;">
                        <div style="font-weight: 700; font-size: 0.82rem; color: var(--text-primary); margin-bottom: 0.65rem; display: flex; justify-content: space-between; align-items: center;">
                            <span style="display: inline-flex; align-items: center; gap: 0.35rem;">
                                <span style="width: 7px; height: 7px; border-radius: 50%; background: #10b981;"></span>
                                DAFTAR RT
                            </span>
                            <span style="font-size: 0.72rem; color: var(--text-secondary); background: var(--bg-card); border: 1px solid var(--border-glass); padding: 1px 7px; border-radius: 4px;">{{ $rts->total() }} Total</span>
                        </div>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>RT</th>
                                        <th>RW Induk</th>
                                        @if(in_array(Auth::user()->role, ['SUPER_ADMIN', 'ADMIN_DESA']))
                                        <th style="text-align: right;">Aksi</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($rts as $rt)
                                    <tr>
                                        <td style="font-weight: 600; font-size: 0.85rem; color: var(--text-primary);">RT {{ $rt->number }}</td>
                                        <td style="font-size: 0.8rem; color: var(--text-secondary);">RW {{ $rt->rw->number ?? '-' }}</td>
                                        @if(in_array(Auth::user()->role, ['SUPER_ADMIN', 'ADMIN_DESA']))
                                        <td style="text-align: right;">
                                            <form method="POST" action="{{ route('areas.rt.destroy', $rt) }}" data-confirm="Hapus RT ini?" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-icon btn-icon-danger" title="Hapus RT" aria-label="Hapus RT">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                                </button>
                                            </form>
                                        </td>
                                        @endif
                                    </tr>
                                    @empty
                                    <tr><td colspan="3" class="empty-state" style="padding: 1rem 0.5rem; font-size: 0.8rem;">Belum ada data RT</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div style="margin-top: 0.5rem;">
                            {{ $rts->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    @endif

    {{-- BARIS BAWAH: DATA KEPENDUDUKAN (LEBAR PENUH) --}}
    @if($canAccessPenduduk)
    <div class="data-card" style="margin-bottom: 1.5rem;">
        <div class="data-card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.65rem;">
            <div style="display: flex; align-items: center; gap: 0.6rem;">
                <div style="width: 34px; height: 34px; border-radius: 8px; background: rgba(99, 102, 241, 0.12); color: #6366f1; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                        <h3 class="data-card-title" style="margin: 0;">
                            @if(Auth::user()->role === 'RT')
                                Data Warga RT {{ $rtObj->number ?? (Auth::user()->resident->rt->number ?? '') }}
                            @elseif(Auth::user()->role === 'RW')
                                Data Warga RW {{ $rwObj->number ?? (Auth::user()->resident->rt->rw->number ?? '') }}
                            @else
                                Data Kependudukan Terpadu
                            @endif
                        </h3>
                        <span id="residentCountBadge" class="badge badge-blue" style="font-size: 0.72rem; font-weight: 700; padding: 2px 7px;">{{ $residents->total() }} Penduduk</span>
                    </div>
                    <div style="font-size: 0.78rem; color: var(--text-secondary); margin-top: 2px;">
                        {{ in_array(Auth::user()->role, ['RT', 'RW']) ? 'Pusat integrasi data kependudukan resmi dan pengelolaan akun login aplikasi SIWIL untuk warga ' . Auth::user()->role . ' Anda' : 'Pencatatan data warga, identitas NIK, peranan, serta status kependudukan' }}
                    </div>
                </div>
            </div>

            <div class="card-header-actions">
                <a href="{{ route('residents.create') }}" class="btn btn-primary btn-sm" style="display: inline-flex; align-items: center; gap: 0.4rem; justify-content: center; padding: 0.45rem 0.85rem; font-size: 0.8rem;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:13px;height:13px"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    <span>Tambah Warga</span>
                </a>
            </div>
        </div>

        <div class="data-card-body">
            {{-- Filter Bar Kependudukan --}}
            <div class="filter-bar" style="margin-bottom: 1.25rem;">
                <form method="GET" action="{{ route('areas.index') }}" data-ajax-filter="true" data-ajax-target="#residentTableContainer" data-count-badge="#residentCountBadge" style="display: flex; gap: 0.75rem; align-items: center; width: 100%; flex-wrap: wrap;">
                    <div style="flex: 1 1 200px; min-width: 180px; max-width: 280px;">
                        <input type="text" name="search_resident" class="form-input" placeholder="Cari NIK, Nama, atau No HP..." value="{{ request('search_resident') }}" style="width: 100%; font-size: 0.85rem; padding: 0.5rem 0.85rem; height: 38px; border-radius: 8px; box-sizing: border-box;">
                    </div>

                    @if(!in_array(Auth::user()->role, ['RT', 'RW']))
                    <div class="filter-dropdown-col">
                        <select name="rw_filter" id="kependudukanRwFilter" class="form-select" style="width: 100%; font-size: 0.85rem;">
                            <option value="">Semua RW</option>
                            @foreach($filterRws as $rw)
                                <option value="{{ $rw->id }}" {{ request('rw_filter') == $rw->id ? 'selected' : '' }}>RW {{ $rw->number }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    @if(Auth::user()->role !== 'RT')
                    <div class="filter-dropdown-col">
                        <select name="rt_filter" id="kependudukanRtFilter" class="form-select" style="width: 100%; font-size: 0.85rem;">
                            <option value="">Semua RT</option>
                            @foreach($filterRts as $rt)
                                <option value="{{ $rt->id }}" data-rw-id="{{ $rt->rw_id }}" {{ request('rt_filter') == $rt->id ? 'selected' : '' }}>RT {{ $rt->number }}{{ !in_array(Auth::user()->role, ['RT', 'RW']) ? ' (RW ' . ($rt->rw->number ?? '-') . ')' : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    
                    <div class="filter-dropdown-col">
                        <select name="gender_filter" class="form-select" style="width: 100%; font-size: 0.85rem;">
                            <option value="">Semua Gender</option>
                            <option value="LAKI_LAKI" {{ request('gender_filter') === 'LAKI_LAKI' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="PEREMPUAN" {{ request('gender_filter') === 'PEREMPUAN' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>

                    <div class="filter-dropdown-col">
                        <select name="status_filter" class="form-select" style="width: 100%; font-size: 0.85rem;">
                            <option value="">Semua Status</option>
                            <option value="AKTIF" {{ request('status_filter') === 'AKTIF' ? 'selected' : '' }}>Warga Aktif</option>
                            <option value="PINDAH" {{ request('status_filter') === 'PINDAH' ? 'selected' : '' }}>Pindah</option>
                            <option value="MENINGGAL" {{ request('status_filter') === 'MENINGGAL' ? 'selected' : '' }}>Meninggal</option>
                        </select>
                    </div>

                    <div class="filter-dropdown-col">
                        <select name="account_filter" class="form-select" style="width: 100%; font-size: 0.85rem;">
                            <option value="">Semua Akun</option>
                            <option value="HAS_ACCOUNT" {{ request('account_filter') === 'HAS_ACCOUNT' ? 'selected' : '' }}>Sudah Punya Akun</option>
                            <option value="NO_ACCOUNT" {{ request('account_filter') === 'NO_ACCOUNT' ? 'selected' : '' }}>Belum Ada Akun</option>
                            <option value="pending" {{ request('account_filter') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="active" {{ request('account_filter') === 'active' ? 'selected' : '' }}>Akun Aktif</option>
                            <option value="inactive" {{ request('account_filter') === 'inactive' ? 'selected' : '' }}>Akun Nonaktif</option>
                            <option value="rejected" {{ request('account_filter') === 'rejected' ? 'selected' : '' }}>Akun Ditolak</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm" style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.5rem 1.1rem; height: 38px; border-radius: 8px; font-weight: 600; box-sizing: border-box;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        Filter
                    </button>
                    @if(request('search_resident') || request('rw_filter') || request('rt_filter') || request('gender_filter') || request('status_filter') || request('account_filter'))
                        <a href="{{ route('areas.index') }}" class="btn btn-outline btn-sm reset-filter-btn" style="padding: 0.5rem 0.85rem; height: 38px; display: inline-flex; align-items: center; border-radius: 8px; box-sizing: border-box;">Reset</a>
                    @endif
                </form>
            </div>

            {{-- Tabel Data Penduduk & Akun Terpadu --}}
            <div id="residentTableContainer">
            @if($residents->count() > 0)
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 45px;">No</th>
                                <th>NIK & Nama</th>
                                <th>Jenis Kelamin</th>
                                <th>Wilayah</th>
                                <th>Status Warga</th>
                                <th>Akun Login (SIWIL)</th>
                                <th style="text-align: right; min-width: 130px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($residents as $i => $resident)
                            <tr>
                                <td>{{ $residents->firstItem() + $i }}</td>
                                <td>
                                    <div style="display: flex; flex-direction: column; gap: 2px;">
                                        <span style="font-weight: 600; color: var(--text-primary); font-size: 0.92rem;">
                                            {{ $resident->full_name }}
                                        </span>
                                        <span style="font-size: 0.78rem; color: var(--text-muted); font-family: monospace;">
                                            {{ $resident->nik }}
                                        </span>
                                    </div>
                                </td>
                                <td>{{ $resident->gender === 'LAKI_LAKI' ? 'Laki-laki' : 'Perempuan' }}</td>
                                <td>
                                    @if($resident->rt)
                                        RT {{ $resident->rt->number }} / RW {{ $resident->rt->rw->number ?? '-' }}
                                    @elseif($resident->family && $resident->family->rt)
                                        RT {{ $resident->family->rt->number }} / RW {{ $resident->family->rt->rw->number ?? '-' }}
                                    @else
                                        -
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
                                    @if($resident->user)
                                        @php
                                            $u = $resident->user;
                                            $statusColors = [
                                                'pending' => 'badge-yellow',
                                                'active' => 'badge-green',
                                                'inactive' => 'badge-gray',
                                                'rejected' => 'badge-red',
                                            ];
                                            $statusLabels = [
                                                'pending' => 'Menunggu Verifikasi',
                                                'active' => 'Aktif',
                                                'inactive' => 'Nonaktif',
                                                'rejected' => 'Ditolak',
                                            ];
                                        @endphp
                                        <div style="display: flex; flex-direction: column; gap: 4px;">
                                            @if($u->status !== 'active' || $u->role !== 'WARGA')
                                                <div style="display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;">
                                                    @if($u->status !== 'active')
                                                        <span class="badge {{ $statusColors[$u->status] ?? 'badge-gray' }}" style="font-size: 0.72rem;">
                                                            {{ $statusLabels[$u->status] ?? ucfirst($u->status) }}
                                                        </span>
                                                    @endif
                                                    @if($u->role !== 'WARGA')
                                                        <span class="badge badge-blue" style="font-size: 0.68rem;">{{ $u->role }}</span>
                                                    @endif
                                                </div>
                                            @endif
                                            <div style="display: flex; flex-direction: column; gap: 2px;">
                                                @if($u->phone_number)
                                                    <span style="font-weight: 600; color: var(--text-primary); font-size: 0.875rem; font-family: monospace; letter-spacing: 0.3px;">
                                                        {{ $u->phone_number }}
                                                    </span>
                                                @endif
                                                @if($u->email)
                                                    <span style="font-size: 0.8rem; color: var(--text-muted); line-height: 1.25;">
                                                        {{ $u->email }}
                                                    </span>
                                                @endif
                                                @if(!$u->phone_number && !$u->email)
                                                    <span style="font-size: 0.8rem; color: var(--text-muted); font-family: monospace;">
                                                        {{ $u->username }}
                                                    </span>
                                                @endif
                                            </div>
                                            @if($u->status === 'pending')
                                                <div style="display: flex; gap: 0.35rem; margin-top: 3px;">
                                                    <form action="{{ route('users.verify', $u->id) }}" method="POST" style="margin: 0;">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="active">
                                                        <button type="submit" class="btn btn-sm btn-primary" style="padding: 0.2rem 0.5rem; font-size: 0.7rem; background: var(--success); border-color: var(--success);" title="Setujui Akun Warga">
                                                            ✓ Setujui
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('users.verify', $u->id) }}" method="POST" data-confirm="Tolak pendaftaran akun warga ini?" style="margin: 0;">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="rejected">
                                                        <button type="submit" class="btn btn-sm btn-outline" style="padding: 0.2rem 0.5rem; font-size: 0.7rem; color: var(--danger); border-color: var(--danger);" title="Tolak Akun Warga">
                                                            ✕ Tolak
                                                        </button>
                                                    </form>
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <div style="display: flex; flex-direction: column; gap: 4px; align-items: flex-start;">
                                            <span class="badge badge-gray" style="font-size: 0.72rem; opacity: 0.85;">Belum Ada Akun</span>
                                            <button type="button" 
                                                class="btn btn-sm btn-outline" 
                                                style="padding: 0.2rem 0.55rem; font-size: 0.72rem; border-color: var(--accent-1); color: var(--accent-1); font-weight: 600;"
                                                onclick="openCreateCitizenAccountModal('{{ $resident->nik }}', '{{ addslashes($resident->full_name) }}', '{{ $resident->rt_id ?? ($resident->family->rt_id ?? '') }}', '{{ $resident->rt->rw_id ?? ($resident->family->rt->rw_id ?? '') }}', '{{ $resident->rt->rw->village_id ?? ($resident->family->rt->rw->village_id ?? '') }}')">
                                                + Buat Akun
                                            </button>
                                        </div>
                                    @endif
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: inline-flex; gap: 0.35rem; align-items: center; justify-content: flex-end;">
                                        <a href="{{ route('residents.show', $resident->id) }}" class="btn-icon btn-icon-view" title="Detail Data Penduduk" aria-label="Detail Data Penduduk">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"></path>
                                                <circle cx="12" cy="12" r="3"></circle>
                                            </svg>
                                        </a>
                                        <a href="{{ route('residents.edit', $resident->id) }}" class="btn-icon btn-icon-edit" title="Edit Data Penduduk" aria-label="Edit Data Penduduk">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                            </svg>
                                        </a>
                                        @if($resident->user)
                                            <button type="button" 
                                                class="btn-icon" 
                                                style="color: #6366f1; border-color: rgba(99, 102, 241, 0.2); background: rgba(99, 102, 241, 0.05);"
                                                title="Edit Akun Login SIWIL" 
                                                aria-label="Edit Akun Login SIWIL"
                                                onclick="openEditCitizenAccountModal('{{ $resident->user->id }}', '{{ $resident->user->username }}', '{{ addslashes($resident->full_name) }}', '{{ $resident->user->phone_number ?? '' }}', '{{ $resident->user->email ?? '' }}', '{{ $resident->user->status }}', '{{ route('users.update', $resident->user->id) }}')">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                                    <path d="m9 12 2 2 4-4"/>
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div style="margin-top: 1rem;">
                    {{ $residents->links() }}
                </div>
            @else
                <div class="empty-state">Data penduduk tidak ditemukan.</div>
            @endif
            </div>
        </div>
    </div>
    @endif

    {{-- MODAL 1: TAMBAH DATA RW --}}
    @if(in_array(Auth::user()->role, ['SUPER_ADMIN', 'ADMIN_DESA']))
    <div id="createRwModal" class="siwil-modal-overlay">
        <div class="siwil-modal-dialog">
            <div class="siwil-modal-header">
                <h3 class="siwil-modal-title">Tambah Rukun Warga (RW)</h3>
                <button type="button" class="siwil-modal-close" onclick="closeCreateRwModal()" aria-label="Tutup">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('areas.rw.store') }}" onsubmit="const btn = this.querySelector('button[type=submit]'); btn.disabled = true; btn.innerText = 'Menyimpan...';">
                @csrf
                <div class="siwil-modal-body">
                    <div class="form-group">
                        <label class="form-label">Nomor RW *</label>
                        <input type="text" name="number" class="form-input" placeholder="Contoh: 01" required>
                        <small style="color: var(--text-muted); font-size: 0.75rem; margin-top: 4px; display: block;">Cukup masukkan angka saja, misalnya <strong>01</strong> atau <strong>02</strong>.</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Wilayah Desa *</label>
                        <select name="village_id" class="form-select" required>
                            <option value="">-- Pilih Desa --</option>
                            @foreach($allVillages as $v)
                                <option value="{{ $v->id }}">{{ $v->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="siwil-modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeCreateRwModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan RW</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL 2: TAMBAH DATA RT --}}
    <div id="createRtModal" class="siwil-modal-overlay">
        <div class="siwil-modal-dialog">
            <div class="siwil-modal-header">
                <h3 class="siwil-modal-title">Tambah Rukun Tetangga (RT)</h3>
                <button type="button" class="siwil-modal-close" onclick="closeCreateRtModal()" aria-label="Tutup">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('areas.rt.store') }}" onsubmit="const btn = this.querySelector('button[type=submit]'); btn.disabled = true; btn.innerText = 'Menyimpan...';">
                @csrf
                <div class="siwil-modal-body">
                    <div class="form-group">
                        <label class="form-label">Nomor RT *</label>
                        <input type="text" name="number" class="form-input" placeholder="Contoh: 01" required>
                        <small style="color: var(--text-muted); font-size: 0.75rem; margin-top: 4px; display: block;">Cukup masukkan angka saja, misalnya <strong>01</strong> atau <strong>03</strong>.</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">RW Induk *</label>
                        <select name="rw_id" class="form-select" required>
                            <option value="">-- Pilih RW Induk --</option>
                            @foreach($rws as $rw)
                                <option value="{{ $rw->id }}">RW {{ $rw->number }} - {{ $rw->village->name ?? '' }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="siwil-modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeCreateRtModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan RT</button>
                </div>
            </form>
        </div>
    </div>
    @endif
    {{-- MODAL 3: BUAT AKUN WARGA BARU --}}
    <div id="createCitizenAccountModal" class="siwil-modal-overlay">
        <div class="siwil-modal-dialog">
            <div class="siwil-modal-header">
                <div>
                    <h3 class="siwil-modal-title">Buat Akun Login Warga</h3>
                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;">Daftarkan akses akun aplikasi SIWIL untuk warga ini</div>
                </div>
                <button type="button" class="siwil-modal-close" onclick="closeCreateCitizenAccountModal()" aria-label="Tutup">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('users.store') }}">
                @csrf
                <input type="hidden" name="role" value="WARGA">
                <input type="hidden" name="rt_id" id="create_rt_id">
                <input type="hidden" name="rw_id" id="create_rw_id">
                <input type="hidden" name="village_id" id="create_village_id">
                <input type="hidden" name="redirect_to" value="{{ route('areas.index') }}">

                <div class="siwil-modal-body">
                    <div class="form-group">
                        <label class="form-label">NIK / Username <small style="color: var(--text-muted);">(Terkunci sesuai NIK KTP)</small></label>
                        <input type="text" name="username" id="create_username" class="form-input" readonly required style="background: var(--bg-body); opacity: 0.85; font-family: monospace; font-weight: 600;">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nama Warga</label>
                        <input type="text" name="full_name" id="create_full_name" class="form-input" readonly style="background: var(--bg-body); opacity: 0.85;">
                    </div>
                    <div class="form-group">
                        <label class="form-label">No. HP / WhatsApp <small style="color: var(--text-muted);">(Contoh: 08123456789)</small></label>
                        <input type="text" name="phone_number" id="create_phone_number" class="form-input" placeholder="08xxxxxxxxxx">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email <small style="color: var(--text-muted);">(Opsional)</small></label>
                        <input type="email" name="email" id="create_email" class="form-input" placeholder="warga@email.com">
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                        <div class="form-group">
                            <label class="form-label">Password *</label>
                            <input type="password" name="password" class="form-input" placeholder="Min. 6 karakter" required minlength="6">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ulangi Password *</label>
                            <input type="password" name="password_confirmation" class="form-input" placeholder="Ulangi password" required minlength="6">
                        </div>
                    </div>
                </div>
                <div class="siwil-modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeCreateCitizenAccountModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Aktifkan & Buat Akun</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL 4: EDIT AKUN WARGA --}}
    <div id="editCitizenAccountModal" class="siwil-modal-overlay">
        <div class="siwil-modal-dialog">
            <div class="siwil-modal-header">
                <div>
                    <h3 class="siwil-modal-title">Kelola Akun Login Warga</h3>
                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;">Perbarui data kontak, status aktif, atau reset password warga</div>
                </div>
                <button type="button" class="siwil-modal-close" onclick="closeEditCitizenAccountModal()" aria-label="Tutup">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
            <form id="editCitizenAccountForm" method="POST" action="">
                @csrf
                @method('PUT')
                <input type="hidden" name="role" value="WARGA">

                <div class="siwil-modal-body">
                    <div class="form-group">
                        <label class="form-label">NIK / Username</label>
                        <input type="text" id="edit_username" class="form-input" readonly disabled style="background: var(--bg-body); opacity: 0.85; font-family: monospace; font-weight: 600;">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="full_name" id="edit_full_name" class="form-input" readonly style="background: var(--bg-body); opacity: 0.85;">
                    </div>
                    <div class="form-group">
                        <label class="form-label">No. HP / WhatsApp</label>
                        <input type="text" name="phone_number" id="edit_phone_number" class="form-input" placeholder="08xxxxxxxxxx">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="edit_email" class="form-input" placeholder="warga@email.com">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status Akun Login</label>
                        <select name="status" id="edit_status" class="form-select">
                            <option value="active">Active (Aktif)</option>
                            <option value="pending">Pending (Menunggu Verifikasi)</option>
                            <option value="inactive">Inactive (Nonaktif)</option>
                            <option value="rejected">Rejected (Ditolak)</option>
                        </select>
                    </div>
                    <div style="background: rgba(99, 102, 241, 0.05); border: 1px dashed rgba(99, 102, 241, 0.2); padding: 0.85rem; border-radius: 8px;">
                        <div style="font-size: 0.82rem; font-weight: 600; color: var(--text-primary); margin-bottom: 0.5rem;">Reset Password (Kosongkan bila tidak diganti)</div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" style="font-size: 0.75rem;">Password Baru</label>
                                <input type="password" name="password" class="form-input" placeholder="Min. 6 karakter">
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" style="font-size: 0.75rem;">Konfirmasi Password</label>
                                <input type="password" name="password_confirmation" class="form-input" placeholder="Ulangi password">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="siwil-modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeEditCitizenAccountModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    function openCreateRwModal() {
        const modal = document.getElementById('createRwModal');
        if (modal) modal.classList.add('active');
    }
    function closeCreateRwModal() {
        const modal = document.getElementById('createRwModal');
        if (modal) modal.classList.remove('active');
    }

    function openCreateRtModal() {
        const modal = document.getElementById('createRtModal');
        if (modal) modal.classList.add('active');
    }
    function closeCreateRtModal() {
        const modal = document.getElementById('createRtModal');
        if (modal) modal.classList.remove('active');
    }

    // Modal Akun Warga
    function openCreateCitizenAccountModal(nik, fullName, rtId, rwId, villageId) {
        document.getElementById('create_username').value = nik;
        document.getElementById('create_full_name').value = fullName;
        document.getElementById('create_rt_id').value = rtId || '';
        document.getElementById('create_rw_id').value = rwId || '';
        document.getElementById('create_village_id').value = villageId || '';
        
        const modal = document.getElementById('createCitizenAccountModal');
        if (modal) modal.classList.add('active');
    }

    function closeCreateCitizenAccountModal() {
        const modal = document.getElementById('createCitizenAccountModal');
        if (modal) modal.classList.remove('active');
    }

    function openEditCitizenAccountModal(userId, username, fullName, phone, email, status, updateUrl) {
        document.getElementById('edit_username').value = username;
        document.getElementById('edit_full_name').value = fullName;
        document.getElementById('edit_phone_number').value = phone || '';
        document.getElementById('edit_email').value = email || '';
        document.getElementById('edit_status').value = status || 'active';
        
        const form = document.getElementById('editCitizenAccountForm');
        if (form) form.action = updateUrl;

        const modal = document.getElementById('editCitizenAccountModal');
        if (modal) modal.classList.add('active');
    }

    function closeEditCitizenAccountModal() {
        const modal = document.getElementById('editCitizenAccountModal');
        if (modal) modal.classList.remove('active');
    }

    // Filter dinamis RW -> RT pada filter kependudukan
    const rwFilterSelect = document.getElementById('kependudukanRwFilter');
    const rtFilterSelect = document.getElementById('kependudukanRtFilter');
    if (rwFilterSelect && rtFilterSelect) {
        rwFilterSelect.addEventListener('change', function() {
            const selectedRwId = this.value;
            const rtOptions = rtFilterSelect.querySelectorAll('option');
            let isCurrentRtValid = false;

            rtOptions.forEach(opt => {
                if (!opt.value) {
                    opt.hidden = false;
                    opt.disabled = false;
                    return;
                }
                const optRwId = opt.getAttribute('data-rw-id');
                if (!selectedRwId || optRwId === selectedRwId) {
                    opt.hidden = false;
                    opt.disabled = false;
                    if (opt.value === rtFilterSelect.value) isCurrentRtValid = true;
                } else {
                    opt.hidden = true;
                    opt.disabled = true;
                }
            });

            if (!isCurrentRtValid && rtFilterSelect.value !== '') {
                rtFilterSelect.value = '';
            }

            if (typeof window.rebuildCustomDropdown === 'function') {
                window.rebuildCustomDropdown(rtFilterSelect);
            }
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeCreateRwModal();
            closeCreateRtModal();
            closeCreateCitizenAccountModal();
            closeEditCitizenAccountModal();
        }
    });
</script>
@endsection
