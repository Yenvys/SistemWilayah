@extends('layouts.app')

@section('title', 'Dashboard - SIWIL AI')
@section('page-title', 'Dashboard')

@section('content')
    <style>
        .stat-card-link {
            text-decoration: none;
            color: inherit;
        }
        .stat-card-link .stat-link-action svg {
            transition: transform 0.25s var(--ease-spring);
        }
        .stat-card-link:hover .stat-link-action svg {
            transform: translateX(4px);
        }

        /* Combined Banner Responsive */
        .module-shortcut-banner {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.08) 0%, rgba(99, 102, 241, 0.06) 50%, rgba(56, 189, 248, 0.06) 100%);
            border: 1px solid rgba(99, 102, 241, 0.22);
            border-radius: 16px;
            padding: 1.5rem 1.75rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px -2px rgba(99, 102, 241, 0.06);
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 1.25rem;
        }

        .module-shortcut-actions {
            display: flex;
            gap: 0.65rem;
            flex-wrap: wrap;
            align-items: center;
        }

        @media (max-width: 992px) {
            .village-matrix-layout {
                grid-template-columns: 1fr !important;
            }
        }

        @media (max-width: 768px) {
            .module-shortcut-banner {
                padding: 1.25rem 1.25rem;
            }
            .module-shortcut-actions {
                width: 100%;
            }
            .module-shortcut-actions .btn {
                flex: 1 1 auto;
                text-align: center;
                justify-content: center;
                min-height: 42px;
            }
            .dashboard-announcements-polls-grid {
                grid-template-columns: 1fr !important;
                gap: 1.25rem !important;
            }
            .card-header-actions {
                width: 100% !important;
                display: flex !important;
                gap: 0.5rem !important;
                margin-top: 0.5rem;
            }
            .card-header-actions .btn {
                flex: 1 1 auto !important;
                text-align: center !important;
                justify-content: center !important;
                min-height: 40px;
            }
        }

        @media (max-width: 480px) {
            .village-header-summary-badge {
                width: 100% !important;
                justify-content: space-between !important;
            }
            .village-kpi-grid {
                grid-template-columns: 1fr !important;
            }
        }
    </style>

    @if(isset($activeEmergencies) && $activeEmergencies->count() > 0)
        <div style="background: rgba(239, 68, 68, 0.1); border: 2px solid var(--danger); padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem;">
            <h3 style="color: var(--danger); margin-bottom: 1rem; font-size: 1.25rem; display: flex; align-items: center; gap: 0.5rem;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:24px;height:24px;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                PANGGILAN DARURAT (SOS) AKTIF DI SELURUH DESA!
            </h3>
            <div style="display: grid; gap: 1rem;">
                @foreach($activeEmergencies as $em)
                <div style="background: transparent; border: 1px solid rgba(239, 68, 68, 0.2); padding: 1rem; border-radius: 8px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <strong>{{ $em->user->name }}</strong> melaporkan keadaan darurat ({{ $em->type }}).<br>
                        Waktu: {{ $em->created_at->format('d M Y, H:i') }} | 
                        @if($em->location_lat && $em->location_lng)
                            <a href="https://www.google.com/maps?q={{ $em->location_lat }},{{ $em->location_lng }}" target="_blank" style="color: var(--accent-1); text-decoration: underline;">Lihat Lokasi</a>
                        @else
                            Lokasi tidak tersedia
                        @endif
                    </div>
                    <form action="{{ route('emergencies.resolve', $em->id) }}" method="POST">
                        @csrf @method('PATCH')
                        <button class="btn btn-sm btn-primary">Tandai Selesai</button>
                    </form>
                </div>
                @endforeach
            </div>
        </div>
    @endif

    <h1 class="page-title">
        @switch(Auth::user()->role)
            @case('SUPER_ADMIN')
                Dashboard Super Admin
                @break
            @case('ADMIN_DESA')
                Dashboard Admin Desa
                @break
            @default
                Dashboard
        @endswitch
    </h1>

    <div class="stats-grid">
        <a href="{{ route('areas.index', ['tab' => 'desa']) }}" class="stat-card stat-card-link" title="Buka kelola data desa">
            <div class="stat-header">
                <span class="stat-title">Total Desa</span>
                <span class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/></svg>
                </span>
            </div>
            <div class="stat-value">{{ $stats['villages'] ?? 0 }}</div>
            <div class="stat-link-action" style="font-size: 0.8rem; color: var(--accent-1); margin-top: 0.5rem; display: flex; align-items: center; gap: 0.35rem; font-weight: 600;">
                <span>Kelola Data Desa</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="width: 14px; height: 14px;"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </div>
        </a>

        <a href="{{ route('areas.index', ['tab' => 'rw_rt']) }}" class="stat-card stat-card-link" title="Buka kelola data RW & RT">
            <div class="stat-header">
                <span class="stat-title">Total RW & RT</span>
                <span class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 2v6h18V2H3zm2 4H4V4h1v2zm3 0H7V4h1v2zm3 0h-1V4h1v2zm9 10a2 2 0 0 1-2 2H6l-4 4V10a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v6z"/></svg>
                </span>
            </div>
            <div class="stat-value" style="display: flex; align-items: baseline; gap: 0.5rem; flex-wrap: wrap;">
                <span>{{ $stats['rws'] ?? 0 }} <span style="font-size: 0.95rem; color: var(--text-muted); font-weight: 500;">RW</span></span>
                <span style="font-size: 0.95rem; color: var(--border-glass);">|</span>
                <span>{{ $stats['rts'] ?? 0 }} <span style="font-size: 0.95rem; color: var(--text-muted); font-weight: 500;">RT</span></span>
            </div>
            <div class="stat-link-action" style="font-size: 0.8rem; color: var(--accent-1); margin-top: 0.5rem; display: flex; align-items: center; gap: 0.35rem; font-weight: 600;">
                <span>Kelola Data RW & RT</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="width: 14px; height: 14px;"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </div>
        </a>

        <a href="{{ route('areas.index', ['tab' => 'penduduk']) }}" class="stat-card stat-card-link" title="Buka kelola data kependudukan">
            <div class="stat-header">
                <span class="stat-title">Total Penduduk & KK</span>
                <span class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </span>
            </div>
            <div class="stat-value">{{ $stats['residents'] ?? 0 }} <span style="font-size: 0.95rem; color: var(--text-muted); font-weight: 500;">Jiwa</span></div>
            <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 0.35rem; display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
                <span><strong>{{ $stats['male'] ?? 0 }}</strong> L</span>
                <span>•</span>
                <span><strong>{{ $stats['female'] ?? 0 }}</strong> P</span>
                <span>•</span>
                <span style="color: var(--accent-1); font-weight: 700;">{{ $stats['families'] ?? 0 }} KK</span>
            </div>
        </a>

        <a href="{{ route('complaints.index') }}" class="stat-card stat-card-link" title="Buka daftar pengaduan warga">
            <div class="stat-header">
                <span class="stat-title">Total Pengaduan</span>
                <span class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                </span>
            </div>
            <div class="stat-value">{{ $stats['complaints'] ?? 0 }}</div>
            <div class="stat-link-action" style="font-size: 0.8rem; color: var(--accent-1); margin-top: 0.5rem; display: flex; align-items: center; gap: 0.35rem; font-weight: 600;">
                <span>Buka Pengaduan Warga</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="width: 14px; height: 14px;"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </div>
        </a>
    </div>

    {{-- Combined Module Shortcut Banner --}}
    <div class="module-shortcut-banner">
        <div>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem;">
                <span style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 2px 10px; border-radius: 20px; font-size: 0.72rem; font-weight: 700; background: rgba(99, 102, 241, 0.15); color: var(--accent-1); border: 1px solid rgba(99, 102, 241, 0.3);">
                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #10b981; display: inline-block;"></span>
                    SIWIL Terpadu Real-Time
                </span>
                <span style="font-size: 0.78rem; color: var(--text-muted);">Integrasi Wilayah & Demografi</span>
            </div>
            <h3 style="margin: 0 0 0.35rem 0; font-size: 1.25rem; font-weight: 800; color: var(--text-primary); display: flex; align-items: center; gap: 0.6rem;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 24px; height: 24px; color: var(--accent-1);"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"></polygon><line x1="8" y1="2" x2="8" y2="18"></line><line x1="16" y1="6" x2="16" y2="22"></line></svg>
                Modul Terpadu Wilayah & Kependudukan
            </h3>
            <p style="margin: 0 0 0.75rem 0; font-size: 0.875rem; color: var(--text-secondary); max-width: 650px; line-height: 1.45;">
                Data desa, struktur rukun warga (RW/RT), dan data kependudukan kini digabungkan dalam satu ekosistem dashboard terintegrasi.
            </p>
            <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; font-size: 0.8rem; color: var(--text-muted);">
                <span style="display: inline-flex; align-items: center; gap: 0.35rem;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px; color: var(--accent-1);"><path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/></svg>
                    <strong>{{ $stats['villages'] ?? 0 }}</strong> Desa
                </span>
                <span style="color: var(--border-glass);">•</span>
                <span style="display: inline-flex; align-items: center; gap: 0.35rem;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px; color: var(--warning);"><path d="M3 2v6h18V2H3zm2 4H4V4h1v2zm3 0H7V4h1v2zm3 0h-1V4h1v2zm9 10a2 2 0 0 1-2 2H6l-4 4V10a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v6z"/></svg>
                    <strong>{{ $stats['rws'] ?? 0 }}</strong> RW / <strong>{{ $stats['rts'] ?? 0 }}</strong> RT
                </span>
                <span style="color: var(--border-glass);">•</span>
                <span style="display: inline-flex; align-items: center; gap: 0.35rem;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px; color: var(--success);"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                    <strong>{{ $stats['residents'] ?? 0 }}</strong> Jiwa Terdata ({{ $stats['families'] ?? 0 }} KK)
                </span>
            </div>
        </div>
        <div class="module-shortcut-actions">
            <a href="{{ route('areas.index', ['tab' => 'desa']) }}" class="btn btn-sm btn-outline" style="border-color: rgba(99, 102, 241, 0.35); display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 0.85rem;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                Tab Desa
            </a>
            <a href="{{ route('areas.index', ['tab' => 'rw_rt']) }}" class="btn btn-sm btn-outline" style="border-color: rgba(99, 102, 241, 0.35); display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 0.85rem;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                Tab RW & RT
            </a>
            <a href="{{ route('areas.index', ['tab' => 'penduduk']) }}" class="btn btn-sm btn-primary" style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 1rem; box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25);">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                Tab Kependudukan
            </a>
        </div>
    </div>

    {{-- Hierarchical Territory & Population Matrix --}}
    @if(isset($territoryTree) && $territoryTree->count() > 0)
        <div class="data-card" style="margin-bottom: 2rem;">
            <div class="data-card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                <div>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span class="data-card-title">Matriks Hierarki Wilayah & Kependudukan</span>
                        <span class="badge badge-blue" style="font-size: 0.7rem; font-weight: 700;">{{ $territoryTree->count() }} Desa Terdaftar</span>
                    </div>
                    <p style="margin: 0.25rem 0 0 0; font-size: 0.8rem; color: var(--text-muted);">
                        Distribusi unit rukun warga (RW), rukun tetangga (RT), kepadatan demografi, dan status sensus
                    </p>
                </div>
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    <a href="{{ route('areas.index', ['tab' => 'rw_rt']) }}" class="btn btn-sm btn-outline" style="font-size: 0.78rem; padding: 0.35rem 0.75rem;">
                        + Tambah RW/RT
                    </a>
                    <a href="{{ route('areas.index') }}" class="btn btn-sm btn-outline" style="font-size: 0.78rem; padding: 0.35rem 0.75rem;">
                        Lihat Modul Lengkap &rarr;
                    </a>
                </div>
            </div>

            <div class="data-card-body" style="padding: 1.25rem;">
                <div style="display: grid; gap: 1.5rem;">
                    @foreach($territoryTree as $village)
                        @php
                            $vRws = $village->rws->count();
                            $vRts = $village->rws->sum(fn($rw) => $rw->rts->count());
                            $vResidents = $village->rws->sum(fn($rw) => $rw->rts->sum('residents_count'));
                            $vFamilies = $village->rws->sum(fn($rw) => $rw->rts->sum('families_count'));
                            $avgPerKk = $vFamilies > 0 ? round($vResidents / $vFamilies, 1) : 0;
                            $avgPerRt = $vRts > 0 ? round($vResidents / $vRts, 1) : 0;
                            $maxRtResidents = max(1, $village->rws->flatMap->rts->max('residents_count') ?? 1);
                        @endphp

                        <div style="background: var(--bg-card); border: 1px solid var(--border-glass); border-radius: 12px; padding: 1.35rem; box-shadow: var(--shadow-soft);">
                            
                            {{-- Village Header Banner --}}
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-glass); padding-bottom: 1rem; flex-wrap: wrap; gap: 1rem;">
                                <div style="display: flex; align-items: center; gap: 0.85rem;">
                                    <div style="width: 44px; height: 44px; border-radius: 10px; background: linear-gradient(135deg, rgba(37, 99, 235, 0.15), rgba(99, 102, 241, 0.15)); color: var(--accent-1); display: flex; align-items: center; justify-content: center; border: 1px solid rgba(37, 99, 235, 0.25);">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 22px; height: 22px;"><path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/></svg>
                                    </div>
                                    <div>
                                        <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                            <h4 style="margin: 0; font-size: 1.15rem; font-weight: 800; color: var(--text-primary);">
                                                {{ $village->name }}
                                            </h4>
                                            <span class="badge badge-blue" style="font-size: 0.75rem; font-weight: 600;">Kode: {{ $village->code }}</span>
                                            <span style="display: inline-flex; align-items: center; gap: 0.3rem; padding: 2px 8px; border-radius: 20px; font-size: 0.68rem; font-weight: 600; background: rgba(16, 185, 129, 0.12); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.25);">
                                                <span style="width: 5px; height: 5px; border-radius: 50%; background: #10b981;"></span>
                                                Wilayah Aktif Terdata
                                            </span>
                                        </div>
                                        <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.25rem;">
                                            Kecamatan: <strong>{{ $village->district ?? '-' }}</strong> • Kabupaten: <strong>{{ $village->regency ?? '-' }}</strong>
                                        </div>
                                    </div>
                                </div>

                                {{-- Quick Village Summary Badges & Action --}}
                                <div style="display: flex; align-items: center; gap: 0.65rem; flex-wrap: wrap;">
                                    <div class="village-header-summary-badge" style="background: var(--bg-body); border: 1px solid var(--border-glass); border-radius: 8px; padding: 0.4rem 0.75rem; display: flex; align-items: center; gap: 0.5rem; font-size: 0.8rem;">
                                        <span style="color: var(--accent-1); font-weight: 700;">{{ $vResidents }}</span>
                                        <span style="color: var(--text-muted); font-size: 0.75rem;">Jiwa</span>
                                        <span style="color: var(--border-glass);">|</span>
                                        <span style="color: var(--text-primary); font-weight: 700;">{{ $vFamilies }}</span>
                                        <span style="color: var(--text-muted); font-size: 0.75rem;">KK</span>
                                        <span style="color: var(--border-glass);">|</span>
                                        <span style="color: var(--text-muted); font-size: 0.75rem;"><strong>{{ $vRws }}</strong> RW <strong>{{ $vRts }}</strong> RT</span>
                                    </div>
                                    <a href="{{ route('areas.index', ['tab' => 'desa', 'search_village' => $village->name]) }}" class="btn btn-sm btn-outline" title="Kelola Detail Desa" style="font-size: 0.78rem; padding: 0.4rem 0.65rem; display: inline-flex; align-items: center; gap: 0.35rem;">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                        Detail
                                    </a>
                                </div>
                            </div>

                            {{-- Dual-Column Main Layout: Left Structure / Right Insights --}}
                            <div class="village-matrix-layout" style="display: grid; grid-template-columns: minmax(0, 1.45fr) minmax(320px, 1fr); gap: 1.25rem; align-items: start;">
                                
                                {{-- LEFT: RW & RT Hierarchy Grid --}}
                                <div>
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.85rem;">
                                        <div style="font-size: 0.875rem; font-weight: 700; color: var(--text-primary); display: flex; align-items: center; gap: 0.4rem;">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; color: var(--accent-1);"><path d="M3 2v6h18V2H3zm2 4H4V4h1v2zm3 0H7V4h1v2zm3 0h-1V4h1v2zm9 10a2 2 0 0 1-2 2H6l-4 4V10a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v6z"/></svg>
                                            Struktur Satuan Wilayah (RW & RT)
                                        </div>
                                        <span style="font-size: 0.75rem; color: var(--text-muted);">
                                            Total: <strong>{{ $vRws }} RW</strong> • <strong>{{ $vRts }} RT</strong>
                                        </span>
                                    </div>

                                    @if($village->rws->count() > 0)
                                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 1rem;">
                                            @foreach($village->rws as $rw)
                                                @php
                                                    $rwResidents = $rw->rts->sum('residents_count');
                                                    $rwFamilies = $rw->rts->sum('families_count');
                                                @endphp
                                                <div style="background: var(--bg-body); border: 1px solid var(--border-glass); border-radius: 10px; padding: 1rem; transition: transform 0.2s, box-shadow 0.2s;">
                                                    
                                                    {{-- RW Header --}}
                                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.85rem; padding-bottom: 0.5rem; border-bottom: 1px dashed var(--border-glass);">
                                                        <div style="display: flex; align-items: center; gap: 0.4rem;">
                                                            <div style="width: 24px; height: 24px; border-radius: 6px; background: rgba(99, 102, 241, 0.15); color: var(--accent-1); display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 800;">
                                                                {{ $rw->number }}
                                                            </div>
                                                            <strong style="color: var(--text-primary); font-size: 0.95rem;">RW {{ $rw->number }}</strong>
                                                        </div>
                                                        <div style="display: flex; align-items: center; gap: 0.35rem;">
                                                            <span class="badge badge-yellow" style="font-size: 0.68rem; padding: 2px 6px;">{{ $rw->rts->count() }} RT</span>
                                                            <span class="badge badge-blue" style="font-size: 0.68rem; padding: 2px 6px;">{{ $rwResidents }} Jiwa</span>
                                                        </div>
                                                    </div>

                                                    {{-- RT List inside this RW --}}
                                                    <div style="display: grid; gap: 0.6rem;">
                                                        @forelse($rw->rts as $rt)
                                                            @php
                                                                $rtPct = $vResidents > 0 ? round(($rt->residents_count / $vResidents) * 100) : 0;
                                                            @endphp
                                                            <div style="background: var(--bg-card); padding: 0.65rem 0.75rem; border-radius: 8px; font-size: 0.825rem; border: 1px solid var(--border-glass); box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                                                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                                                                    <div style="display: flex; align-items: center; gap: 0.4rem;">
                                                                        <span style="font-weight: 700; color: var(--text-primary);">RT {{ $rt->number }}</span>
                                                                        <span style="font-size: 0.68rem; color: var(--text-muted); background: rgba(99,102,241,0.08); padding: 1px 5px; border-radius: 4px;">{{ $rtPct }}% warga</span>
                                                                    </div>
                                                                    <div style="display: flex; align-items: center; gap: 0.45rem;">
                                                                        <span style="color: var(--text-primary); font-size: 0.78rem; font-weight: 600;">
                                                                            <strong>{{ $rt->residents_count }}</strong> <span style="font-size: 0.7rem; color: var(--text-muted); font-weight: 400;">jiwa</span>
                                                                        </span>
                                                                        <span style="color: var(--border-glass);">|</span>
                                                                        <span style="color: var(--accent-1); font-size: 0.78rem; font-weight: 700;">
                                                                            {{ $rt->families_count }} <span style="font-size: 0.7rem; color: var(--text-muted); font-weight: 400;">KK</span>
                                                                        </span>
                                                                    </div>
                                                                </div>

                                                                {{-- Proportional visual density bar --}}
                                                                <div style="width: 100%; height: 5px; background: rgba(148, 163, 184, 0.18); border-radius: 10px; overflow: hidden; margin-top: 0.35rem;">
                                                                    <div style="height: 100%; width: {{ max(6, ($rt->residents_count / $maxRtResidents) * 100) }}%; background: linear-gradient(90deg, #6366f1, #3b82f6); border-radius: 10px;"></div>
                                                                </div>
                                                            </div>
                                                        @empty
                                                            <div style="font-size: 0.8rem; color: var(--text-muted); text-align: center; padding: 0.75rem; background: var(--bg-card); border-radius: 6px; border: 1px dashed var(--border-glass);">
                                                                Belum ada RT terdaftar di RW ini.
                                                            </div>
                                                        @endforelse
                                                    </div>
                                                </div>
                                            @endforeach

                                            {{-- Quick Add RW Slot --}}
                                            <a href="{{ route('areas.index', ['tab' => 'rw_rt']) }}" style="text-decoration: none; border: 2px dashed rgba(99, 102, 241, 0.3); border-radius: 10px; padding: 1rem; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; color: var(--accent-1); background: rgba(99, 102, 241, 0.02); transition: all 0.2s ease; min-height: 120px;" onmouseover="this.style.background='rgba(99, 102, 241, 0.06)'; this.style.borderColor='var(--accent-1)';" onmouseout="this.style.background='rgba(99, 102, 241, 0.02)'; this.style.borderColor='rgba(99, 102, 241, 0.3)';">
                                                <div style="width: 32px; height: 32px; border-radius: 50%; background: rgba(99, 102, 241, 0.12); display: flex; align-items: center; justify-content: center; margin-bottom: 0.4rem;">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width: 16px; height: 16px;"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                                </div>
                                                <span style="font-weight: 700; font-size: 0.85rem;">Tambah RW / RT</span>
                                                <span style="font-size: 0.72rem; color: var(--text-muted); margin-top: 0.2rem;">Perluas cakupan wilayah desa</span>
                                            </a>
                                        </div>
                                    @else
                                        <div style="text-align: center; padding: 2rem; background: var(--bg-body); border-radius: 10px; border: 1px dashed var(--border-glass); color: var(--text-muted); font-size: 0.875rem;">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width: 36px; height: 36px; margin: 0 auto 0.5rem; opacity: 0.4;"><path d="M3 2v6h18V2H3zm2 4H4V4h1v2zm3 0H7V4h1v2zm3 0h-1V4h1v2zm9 10a2 2 0 0 1-2 2H6l-4 4V10a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v6z"/></svg>
                                            <div style="font-weight: 600; color: var(--text-primary); margin-bottom: 0.25rem;">Belum ada data RW & RT untuk desa ini</div>
                                            <p style="margin: 0 0 0.75rem 0; font-size: 0.8rem;">Daftarkan nomor rukun warga (RW) dan rukun tetangga (RT) untuk memulai integrasi kependudukan.</p>
                                            <a href="{{ route('areas.index', ['tab' => 'rw_rt']) }}" class="btn btn-sm btn-primary">+ Tambah RW/RT Baru</a>
                                        </div>
                                    @endif
                                </div>

                                {{-- RIGHT: Demographics Insights & Quick Actions Panel (Fills Empty Space) --}}
                                <div style="display: flex; flex-direction: column; gap: 1rem;">
                                    
                                    {{-- Demographics KPI Breakdown --}}
                                    <div style="background: var(--bg-body); border: 1px solid var(--border-glass); border-radius: 10px; padding: 1.15rem;">
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.85rem;">
                                            <div style="font-size: 0.875rem; font-weight: 700; color: var(--text-primary); display: flex; align-items: center; gap: 0.4rem;">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; color: #10b981;"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                                                Indikator & Demografi Desa
                                            </div>
                                            <span style="font-size: 0.72rem; color: var(--text-muted); background: var(--bg-card); padding: 2px 7px; border-radius: 4px; border: 1px solid var(--border-glass);">
                                                Kepadatan Terhitung
                                            </span>
                                        </div>

                                        {{-- 2x2 Mini KPI Cards --}}
                                        <div class="village-kpi-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.65rem; margin-bottom: 0.95rem;">
                                            <div style="background: var(--bg-card); border: 1px solid var(--border-glass); border-radius: 8px; padding: 0.65rem 0.75rem;">
                                                <div style="font-size: 0.72rem; color: var(--text-muted); display: flex; align-items: center; gap: 0.3rem;">
                                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #6366f1;"></span>
                                                    Rerata Jiwa / KK
                                                </div>
                                                <div style="font-size: 1.15rem; font-weight: 800; color: var(--text-primary); margin-top: 0.15rem;">
                                                    {{ $avgPerKk }} <span style="font-size: 0.75rem; font-weight: 500; color: var(--text-muted);">jiwa</span>
                                                </div>
                                                <div style="font-size: 0.68rem; color: var(--text-muted); margin-top: 0.15rem;">Beban keluarga normal</div>
                                            </div>

                                            <div style="background: var(--bg-card); border: 1px solid var(--border-glass); border-radius: 8px; padding: 0.65rem 0.75rem;">
                                                <div style="font-size: 0.72rem; color: var(--text-muted); display: flex; align-items: center; gap: 0.3rem;">
                                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #3b82f6;"></span>
                                                    Rerata Jiwa / RT
                                                </div>
                                                <div style="font-size: 1.15rem; font-weight: 800; color: var(--text-primary); margin-top: 0.15rem;">
                                                    ~{{ $avgPerRt }} <span style="font-size: 0.75rem; font-weight: 500; color: var(--text-muted);">jiwa</span>
                                                </div>
                                                <div style="font-size: 0.68rem; color: var(--text-muted); margin-top: 0.15rem;">Sebaran unit seimbang</div>
                                            </div>

                                            <div style="background: var(--bg-card); border: 1px solid var(--border-glass); border-radius: 8px; padding: 0.65rem 0.75rem;">
                                                <div style="font-size: 0.72rem; color: var(--text-muted); display: flex; align-items: center; gap: 0.3rem;">
                                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #10b981;"></span>
                                                    Cakupan RT
                                                </div>
                                                <div style="font-size: 1.15rem; font-weight: 800; color: var(--text-primary); margin-top: 0.15rem;">
                                                    {{ $vRts }} <span style="font-size: 0.75rem; font-weight: 500; color: var(--text-muted);">RT Terdaftar</span>
                                                </div>
                                                <div style="font-size: 0.68rem; color: var(--text-muted); margin-top: 0.15rem;">Dalam {{ $vRws }} RW</div>
                                            </div>

                                            <div style="background: var(--bg-card); border: 1px solid var(--border-glass); border-radius: 8px; padding: 0.65rem 0.75rem;">
                                                <div style="font-size: 0.72rem; color: var(--text-muted); display: flex; align-items: center; gap: 0.3rem;">
                                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #f59e0b;"></span>
                                                    Status Sensus
                                                </div>
                                                <div style="font-size: 1.15rem; font-weight: 800; color: #10b981; margin-top: 0.15rem;">
                                                    100%
                                                </div>
                                                <div style="font-size: 0.68rem; color: var(--text-muted); margin-top: 0.15rem;">Tersinkron SIWIL AI</div>
                                            </div>
                                        </div>

                                        {{-- Visual Distribution Progress Bar --}}
                                        @if($vResidents > 0)
                                            <div>
                                                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.72rem; color: var(--text-muted); margin-bottom: 0.35rem;">
                                                    <span>Distribusi Populasi RT</span>
                                                    <span style="font-weight: 600; color: var(--text-primary);">Total: {{ $vResidents }} Jiwa</span>
                                                </div>
                                                <div style="width: 100%; height: 8px; background: rgba(148, 163, 184, 0.2); border-radius: 12px; overflow: hidden; display: flex;">
                                                    @php
                                                        $colors = ['#6366f1', '#3b82f6', '#10b981', '#f59e0b', '#ec4899', '#8b5cf6'];
                                                        $cIdx = 0;
                                                    @endphp
                                                    @foreach($village->rws as $rw)
                                                        @foreach($rw->rts as $rt)
                                                            @php
                                                                $rtWidth = ($rt->residents_count / max($vResidents, 1)) * 100;
                                                                $curColor = $colors[$cIdx % count($colors)];
                                                                $cIdx++;
                                                            @endphp
                                                            <div style="height: 100%; width: {{ $rtWidth }}%; background: {{ $curColor }};" title="RT {{ $rt->number }}: {{ $rt->residents_count }} jiwa ({{ round($rtWidth) }}%)"></div>
                                                        @endforeach
                                                    @endforeach
                                                </div>
                                                
                                                <div style="display: flex; flex-wrap: wrap; gap: 0.65rem; margin-top: 0.5rem; font-size: 0.72rem; color: var(--text-muted);">
                                                    @php $cIdx = 0; @endphp
                                                    @foreach($village->rws as $rw)
                                                        @foreach($rw->rts as $rt)
                                                            @php
                                                                $curColor = $colors[$cIdx % count($colors)];
                                                                $rtPct = $vResidents > 0 ? round(($rt->residents_count / $vResidents) * 100) : 0;
                                                                $cIdx++;
                                                            @endphp
                                                            <span style="display: inline-flex; align-items: center; gap: 0.3rem;">
                                                                <span style="width: 8px; height: 8px; border-radius: 2px; background: {{ $curColor }};"></span>
                                                                <strong>RT {{ $rt->number }}:</strong> {{ $rt->residents_count }} jiwa ({{ $rtPct }}%)
                                                            </span>
                                                        @endforeach
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Quick Navigation & Actions --}}
                                    <div style="background: var(--bg-body); border: 1px solid var(--border-glass); border-radius: 10px; padding: 1.15rem;">
                                        <div style="font-size: 0.875rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.4rem;">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; color: var(--accent-1);"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                                            Pusat Aksi Cepat Wilayah
                                        </div>

                                        <div style="display: grid; gap: 0.5rem;">
                                            <a href="{{ route('residents.create') }}" style="display: flex; align-items: center; justify-content: space-between; background: var(--bg-card); border: 1px solid var(--border-glass); border-radius: 8px; padding: 0.65rem 0.85rem; text-decoration: none; color: inherit; transition: all 0.2s;" onmouseover="this.style.borderColor='var(--accent-1)'; this.style.transform='translateX(3px)';" onmouseout="this.style.borderColor='var(--border-glass)'; this.style.transform='translateX(0)';">
                                                <div style="display: flex; align-items: center; gap: 0.65rem;">
                                                    <div style="width: 28px; height: 28px; border-radius: 6px; background: rgba(37, 99, 235, 0.12); color: var(--accent-1); display: flex; align-items: center; justify-content: center;">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                                                    </div>
                                                    <div>
                                                        <div style="font-size: 0.825rem; font-weight: 700; color: var(--text-primary);">+ Input Warga Baru</div>
                                                        <div style="font-size: 0.72rem; color: var(--text-muted);">Daftarkan warga & NIK ke wilayah</div>
                                                    </div>
                                                </div>
                                                <span style="font-size: 0.85rem; color: var(--accent-1);">&rarr;</span>
                                            </a>

                                            <a href="{{ route('areas.index', ['tab' => 'penduduk']) }}" style="display: flex; align-items: center; justify-content: space-between; background: var(--bg-card); border: 1px solid var(--border-glass); border-radius: 8px; padding: 0.65rem 0.85rem; text-decoration: none; color: inherit; transition: all 0.2s;" onmouseover="this.style.borderColor='var(--accent-1)'; this.style.transform='translateX(3px)';" onmouseout="this.style.borderColor='var(--border-glass)'; this.style.transform='translateX(0)';">
                                                <div style="display: flex; align-items: center; gap: 0.65rem;">
                                                    <div style="width: 28px; height: 28px; border-radius: 6px; background: rgba(16, 185, 129, 0.12); color: #10b981; display: flex; align-items: center; justify-content: center;">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                                                    </div>
                                                    <div>
                                                        <div style="font-size: 0.825rem; font-weight: 700; color: var(--text-primary);">Kelola Sensus Warga</div>
                                                        <div style="font-size: 0.72rem; color: var(--text-muted);">Saring, edit & cek kelengkapan data</div>
                                                    </div>
                                                </div>
                                                <span style="font-size: 0.85rem; color: var(--accent-1);">&rarr;</span>
                                            </a>

                                            <a href="{{ route('areas.index', ['tab' => 'rw_rt']) }}" style="display: flex; align-items: center; justify-content: space-between; background: var(--bg-card); border: 1px solid var(--border-glass); border-radius: 8px; padding: 0.65rem 0.85rem; text-decoration: none; color: inherit; transition: all 0.2s;" onmouseover="this.style.borderColor='var(--accent-1)'; this.style.transform='translateX(3px)';" onmouseout="this.style.borderColor='var(--border-glass)'; this.style.transform='translateX(0)';">
                                                <div style="display: flex; align-items: center; gap: 0.65rem;">
                                                    <div style="width: 28px; height: 28px; border-radius: 6px; background: rgba(245, 158, 11, 0.12); color: var(--warning); display: flex; align-items: center; justify-content: center;">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><path d="M3 2v6h18V2H3zm2 4H4V4h1v2zm3 0H7V4h1v2zm3 0h-1V4h1v2zm9 10a2 2 0 0 1-2 2H6l-4 4V10a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v6z"/></svg>
                                                    </div>
                                                    <div>
                                                        <div style="font-size: 0.825rem; font-weight: 700; color: var(--text-primary);">Kelola Struktur RW & RT</div>
                                                        <div style="font-size: 0.72rem; color: var(--text-muted);">Pengurus wilayah & penomoran unit</div>
                                                    </div>
                                                </div>
                                                <span style="font-size: 0.85rem; color: var(--accent-1);">&rarr;</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- Charts and Data --}}
    <div class="dashboard-grid">
        <div class="data-card">
            <div class="data-card-header">
                <span class="data-card-title">Statistik Kependudukan & Pengaduan</span>
            </div>
            <div class="data-card-body" style="height: 350px;">
                <canvas id="mainChart"></canvas>
            </div>
        </div>

        <div class="data-card">
            <div class="data-card-header">
                <span class="data-card-title">Penduduk Terbaru Ditambahkan</span>
                <a href="{{ route('areas.index', ['tab' => 'penduduk']) }}" class="btn btn-sm btn-outline">Lihat Semua</a>
            </div>
            <div class="data-card-body" style="padding: 0;">
                @if(isset($recentResidents) && $recentResidents->count() > 0)
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Nama & NIK</th>
                                    <th>Wilayah</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentResidents as $resident)
                                <tr>
                                    <td style="font-weight:600;">
                                        {{ $resident->full_name }}
                                        <br><span style="font-size: 0.8rem; color: var(--text-muted); font-weight: 400;">{{ substr($resident->nik, 0, 4) . '********' . substr($resident->nik, -4) }}</span>
                                    </td>
                                    <td style="font-size: 0.85rem; color: var(--text-muted);">
                                        RT {{ $resident->rt->number ?? ($resident->family->rt->number ?? '-') }} / 
                                        RW {{ $resident->rt->rw->number ?? ($resident->family->rt->rw->number ?? '-') }}
                                    </td>
                                    <td><span class="badge {{ $resident->status === 'AKTIF' ? 'badge-green' : 'badge-red' }}">{{ $resident->status }}</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div style="padding: 3rem; text-align: center; color: var(--text-muted);">Belum ada data penduduk terbaru.</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Section: Informasi Desa & Hasil Musyawarah --}}
    <div class="dashboard-announcements-polls-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem; margin-top: 1.5rem;">
        
        {{-- Box Kiri: Informasi & Pengumuman Desa (DI ATAS / PERTAMA) --}}
        <div class="data-card" style="display: flex; flex-direction: column;">
            <div class="data-card-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-glass); padding-bottom: 0.85rem; flex-wrap: wrap; gap: 0.5rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(37, 99, 235, 0.12); color: #2563eb; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;"><path d="m3 11 18-5v12L3 13v-2z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/></svg>
                    </div>
                    <div>
                        <span class="data-card-title" style="margin: 0;">Pengumuman & Informasi Desa</span>
                        <div style="font-size: 0.78rem; color: var(--text-muted);">Warta resmi yang dipublikasikan untuk warga</div>
                    </div>
                </div>
                <div class="card-header-actions" style="display: flex; gap: 0.5rem; align-items: center;">
                    <a href="{{ route('announcements.create') }}" class="btn btn-primary btn-sm" style="font-size: 0.75rem; padding: 0.35rem 0.65rem;">+ Buat Baru</a>
                    <a href="{{ route('announcements.index') }}" class="btn btn-outline btn-sm" style="font-size: 0.75rem; padding: 0.35rem 0.65rem;">Semua</a>
                </div>
            </div>

            <div class="data-card-body" style="padding: 1rem; flex: 1; display: flex; flex-direction: column; gap: 0.85rem;">
                @if(isset($recentAnnouncements) && $recentAnnouncements->count() > 0)
                    @foreach($recentAnnouncements as $ann)
                        <div style="background: var(--bg-body); border: 1px solid var(--border-glass); border-radius: 10px; padding: 0.85rem 1rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem; font-size: 0.75rem; color: var(--accent-1);">
                                <span>{{ $ann->published_at ? $ann->published_at->format('d M Y') : $ann->created_at->format('d M Y') }}</span>
                                @if($ann->is_active)
                                    <span class="badge badge-green" style="font-size: 0.65rem; padding: 2px 6px;">Terbit</span>
                                @else
                                    <span class="badge badge-yellow" style="font-size: 0.65rem; padding: 2px 6px;">Draf</span>
                                @endif
                            </div>
                            <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--text-primary); margin: 0 0 0.35rem 0;">
                                <a href="{{ route('announcements.show', $ann->id) }}" style="color: inherit; text-decoration: none;">
                                    {{ $ann->title }}
                                </a>
                            </h4>
                            <p style="font-size: 0.82rem; color: var(--text-secondary); margin: 0 0 0.5rem 0; line-height: 1.45;">
                                {{ Str::limit(strip_tags($ann->content), 110) }}
                            </p>
                            <div style="text-align: right;">
                                <a href="{{ route('announcements.show', $ann->id) }}" style="font-size: 0.75rem; color: var(--accent-1); font-weight: 600; text-decoration: none;">Baca Selengkapnya &rarr;</a>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div style="padding: 2.5rem 1rem; text-align: center; color: var(--text-muted);">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width: 36px; height: 36px; margin: 0 auto 0.5rem; opacity: 0.4;"><path d="m3 11 18-5v12L3 13v-2z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/></svg>
                        <div style="font-weight: 600;">Belum Ada Pengumuman Desa</div>
                        <div style="font-size: 0.8rem;">Publikasikan pengumuman penting untuk warga di sini.</div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Box Kanan: Hasil Musyawarah Warga (KEDUA) --}}
        <div class="data-card" style="display: flex; flex-direction: column;">
            <div class="data-card-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-glass); padding-bottom: 0.85rem; flex-wrap: wrap; gap: 0.5rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(16, 185, 129, 0.12); color: #10b981; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;"><path d="M18 13h-.68l-2 2h1.91L19 17H5l1.78-2h2.05l-2-2H6l-3 3v2c0 1.1.89 2 1.99 2H19c1.1 0 2-.9 2-2v-2l-3-3zm-1-5.05l-4.95 4.95-3.54-3.54 4.95-4.95L16.99 7.95z"/></svg>
                    </div>
                    <div>
                        <span class="data-card-title" style="margin: 0;">Hasil Musyawarah Desa</span>
                        <div style="font-size: 0.78rem; color: var(--text-muted);">Keputusan resmi dan kesepakatan warga</div>
                    </div>
                </div>
                <div class="card-header-actions" style="display: flex; align-items: center;">
                    <a href="{{ route('polls.index') }}" class="btn btn-outline btn-sm" style="font-size: 0.78rem; padding: 0.35rem 0.65rem;">
                        Semua Musyawarah &rarr;
                    </a>
                </div>
            </div>

            <div class="data-card-body" style="padding: 1rem; flex: 1; display: flex; flex-direction: column; gap: 0.85rem;">
                @if(isset($completedPolls) && $completedPolls->count() > 0)
                    @foreach($completedPolls as $cPoll)
                        @php
                            $winningOpt = $cPoll->winning_option;
                            $tVotes = $cPoll->votes->count();
                            $wVotes = $winningOpt ? $winningOpt->votes->count() : 0;
                            $pct = $tVotes > 0 ? round(($wVotes / $tVotes) * 100) : 0;
                        @endphp
                        <div style="background: var(--bg-body); border: 1px solid var(--border-glass); border-radius: 10px; padding: 0.85rem 1rem;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem; margin-bottom: 0.35rem;">
                                <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--text-primary); margin: 0; line-height: 1.35;">
                                    <a href="{{ route('polls.show', $cPoll->id) }}" style="color: inherit; text-decoration: none;">
                                        {{ $cPoll->title }}
                                    </a>
                                </h4>
                                <span class="badge badge-green" style="font-size: 0.68rem; padding: 2px 7px; flex-shrink: 0;">Selesai</span>
                            </div>
                            @if($winningOpt)
                                <div style="font-size: 0.82rem; color: var(--text-secondary); background: var(--bg-card); padding: 0.45rem 0.65rem; border-radius: 6px; border: 1px solid var(--border-glass); margin-bottom: 0.45rem;">
                                    🏆 <strong>Hasil Terpilih:</strong> <span style="color: #10b981; font-weight: 600;">{{ $winningOpt->text }}</span>
                                    <span style="color: var(--text-muted); font-size: 0.75rem;">({{ $wVotes }} suara / {{ $pct }}%)</span>
                                </div>
                            @endif
                            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.75rem; color: var(--text-muted); flex-wrap: wrap; gap: 0.5rem;">
                                <span>Partisipasi: {{ $tVotes }} suara • Selesai: {{ $cPoll->updated_at->format('d M Y') }}</span>
                                <a href="{{ route('polls.show', $cPoll->id) }}" style="color: var(--accent-1); font-weight: 600; text-decoration: none;">Lihat Detail &rarr;</a>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div style="padding: 2.5rem 1rem; text-align: center; color: var(--text-muted);">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width: 36px; height: 36px; margin: 0 auto 0.5rem; opacity: 0.4;"><path d="M18 13h-.68l-2 2h1.91L19 17H5l1.78-2h2.05l-2-2H6l-3 3v2c0 1.1.89 2 1.99 2H19c1.1 0 2-.9 2-2v-2l-3-3zm-1-5.05l-4.95 4.95-3.54-3.54 4.95-4.95L16.99 7.95z"/></svg>
                        <div style="font-weight: 600;">Belum Ada Musyawarah yang Selesai</div>
                        <div style="font-size: 0.8rem;">Hasil musyawarah yang telah ditutup akan muncul di sini.</div>
                    </div>
                @endif
            </div>
        </div>

    </div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('mainChart').getContext('2d');
        
        // Gradient for chart line
        let gradientBlue = ctx.createLinearGradient(0, 0, 0, 400);
        gradientBlue.addColorStop(0, 'rgba(99, 102, 241, 0.3)');
        gradientBlue.addColorStop(1, 'rgba(99, 102, 241, 0.0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
                datasets: [
                    {
                        label: 'Pengaduan Masuk',
                        data: [12, 19, 15, 25, 22, 30],
                        borderColor: '#6366f1',
                        backgroundColor: gradientBlue,
                        borderWidth: 3,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#6366f1',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        fill: true,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: { family: "'Outfit', sans-serif" },
                            color: '#475569'
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0, 0, 0, 0.05)', drawBorder: false },
                        ticks: { font: { family: "'Outfit', sans-serif" }, color: '#94a3b8' }
                    },
                    x: {
                        grid: { display: false, drawBorder: false },
                        ticks: { font: { family: "'Outfit', sans-serif" }, color: '#94a3b8' }
                    }
                }
            }
        });
    });
</script>
@endsection
