@extends('layouts.app')

@section('title', 'Pembangunan, APBDes & Analitik Spasial - SIWIL AI')
@section('page-title', 'Pembangunan, APBDes & Analitik Spasial')

@section('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    .hub-twin-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
        align-items: stretch;
    }

    @media (max-width: 1100px) {
        .hub-twin-grid {
            grid-template-columns: 1fr;
        }
    }

    .mini-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
        gap: 0.75rem;
        margin-bottom: 1.25rem;
    }

    .mini-stat-box {
        background: var(--bg-body);
        border: 1px solid var(--border-glass);
        border-radius: 10px;
        padding: 0.85rem 1rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .mini-stat-label {
        font-size: 0.75rem;
        color: var(--text-muted);
        margin-bottom: 0.25rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .mini-stat-value {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-primary);
        line-height: 1.2;
    }

    .mini-stat-sub {
        font-size: 0.72rem;
        color: var(--text-muted);
        margin-top: 0.2rem;
    }

    #spatial-map { 
        height: 500px; 
        width: 100%; 
        border-radius: 12px;
        z-index: 1; 
    }

    .alert-banner-sos {
        background: rgba(239, 68, 68, 0.1);
        border: 1px solid rgba(239, 68, 68, 0.3);
        border-left: 4px solid var(--danger);
        padding: 0.85rem 1.25rem;
        border-radius: 8px;
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.85rem;
    }

    .progress-bar-container {
        width: 100%;
        height: 6px;
        background: rgba(100, 116, 139, 0.15);
        border-radius: 999px;
        overflow: hidden;
        margin-top: 3px;
    }

    .progress-bar-fill {
        height: 100%;
        border-radius: 999px;
        transition: width 0.3s ease;
    }

    /* Modal Styling */
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
        max-width: 640px;
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

    .form-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    @media (max-width: 640px) {
        .form-grid-2 {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
    {{-- Header --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 class="page-title" style="margin-bottom: 0.25rem;">Pembangunan, APBDes & Analitik Spasial</h1>
            <p style="color: var(--text-secondary); font-size: 0.92rem; margin: 0; font-weight: 500;">
                Monitoring terpadu dalam satu layar: Keuangan APBDes di sisi kiri, Proyek Pembangunan di sisi kanan, serta Peta Analitik Spasial di bagian bawah.
            </p>
        </div>
    </div>

    {{-- Session Alerts --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
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

    {{-- BARIS ATAS: 2 BOX BERDAMPINGAN (APBDes di Kiri, Pembangunan di Kanan) --}}
    <div class="hub-twin-grid">
        
        {{-- BOX 1: APBDes & KEUANGAN DESA (KIRI) --}}
        @php
            $surplus = $budgetStats['pendapatan_realized'] - $budgetStats['belanja_realized'];
        @endphp
        <div class="data-card" style="margin-bottom: 0; display: flex; flex-direction: column;">
            <div class="data-card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(16, 185, 129, 0.12); color: #10b981; display: flex; align-items: center; justify-content: center;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    </div>
                    <div>
                        <h3 class="data-card-title" style="margin: 0;">Keuangan & APBDes</h3>
                        <div style="font-size: 0.78rem; color: var(--text-muted);">Alokasi pagu & realisasi anggaran desa</div>
                    </div>
                </div>
                <button type="button" class="btn btn-primary btn-sm" onclick="openCreateBudgetModal()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    Tambah Anggaran
                </button>
            </div>
            
            <div class="data-card-body" style="flex: 1; display: flex; flex-direction: column;">
                {{-- Mini Stats APBDes --}}
                <div class="mini-stats-grid">
                    <div class="mini-stat-box" style="border-left: 3px solid #10b981;">
                        <div class="mini-stat-label">Pendapatan</div>
                        <div class="mini-stat-value" style="color: #10b981;">
                            Rp {{ number_format($budgetStats['pendapatan_budget'], 0, ',', '.') }}
                        </div>
                        <div class="mini-stat-sub">Real: Rp {{ number_format($budgetStats['pendapatan_realized'], 0, ',', '.') }}</div>
                    </div>
                    <div class="mini-stat-box" style="border-left: 3px solid #ef4444;">
                        <div class="mini-stat-label">Belanja</div>
                        <div class="mini-stat-value" style="color: #ef4444;">
                            Rp {{ number_format($budgetStats['belanja_budget'], 0, ',', '.') }}
                        </div>
                        <div class="mini-stat-sub">Real: Rp {{ number_format($budgetStats['belanja_realized'], 0, ',', '.') }}</div>
                    </div>
                    <div class="mini-stat-box" style="border-left: 3px solid {{ $surplus >= 0 ? '#3b82f6' : '#f59e0b' }};">
                        <div class="mini-stat-label">{{ $surplus >= 0 ? 'Surplus' : 'Defisit' }}</div>
                        <div class="mini-stat-value" style="color: {{ $surplus >= 0 ? '#10b981' : '#ef4444' }};">
                            Rp {{ number_format($surplus, 0, ',', '.') }}
                        </div>
                        <div class="mini-stat-sub">Sisa realisasi bersih</div>
                    </div>
                </div>

                {{-- Tabel Pos Anggaran --}}
                <div class="table-responsive" style="flex: 1;">
                    <table>
                        <thead>
                            <tr>
                                <th>Tahun/Tipe</th>
                                <th>Pos Anggaran</th>
                                <th>Target & Realisasi</th>
                                <th>Capaian</th>
                                <th style="text-align: right;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($budgets as $budget)
                                @php
                                    $percentage = $budget->amount_budget > 0 ? round(($budget->amount_realized / $budget->amount_budget) * 100, 1) : 0;
                                @endphp
                                <tr>
                                    <td>
                                        <div style="font-weight: 700; font-size: 0.85rem;">{{ $budget->year }}</div>
                                        <div style="margin-top: 2px;">
                                            @if($budget->type === 'PENDAPATAN')
                                                <span class="badge badge-green" style="font-size: 0.68rem; padding: 1px 6px;">PENDAPATAN</span>
                                            @elseif($budget->type === 'BELANJA')
                                                <span class="badge badge-red" style="font-size: 0.68rem; padding: 1px 6px;">BELANJA</span>
                                            @else
                                                <span class="badge badge-blue" style="font-size: 0.68rem; padding: 1px 6px;">PEMBIAYAAN</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; color: var(--text-primary); font-size: 0.88rem;">{{ $budget->category }}</div>
                                        @if($budget->description)
                                            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 1px;">{{ \Illuminate\Support\Str::limit($budget->description, 35) }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div style="font-size: 0.82rem; font-weight: 600;">Rp {{ number_format($budget->amount_budget, 0, ',', '.') }}</div>
                                        <div style="font-size: 0.74rem; color: var(--text-muted);">Real: Rp {{ number_format($budget->amount_realized, 0, ',', '.') }}</div>
                                    </td>
                                    <td style="min-width: 90px;">
                                        <div style="font-size: 0.78rem; font-weight: 600;">{{ $percentage }}%</div>
                                        <div class="progress-bar-container">
                                            <div class="progress-bar-fill" style="width: {{ min(100, $percentage) }}%; background: {{ $percentage >= 100 ? '#10b981' : ($percentage >= 50 ? '#3b82f6' : '#f59e0b') }};"></div>
                                        </div>
                                    </td>
                                    <td style="text-align: right;">
                                        <div style="display: inline-flex; gap: 0.35rem; align-items: center; justify-content: flex-end;">
                                            <button type="button" 
                                                    class="btn-icon btn-icon-edit" 
                                                    title="Edit Anggaran"
                                                    onclick="openEditBudgetModal({{ json_encode($budget) }})">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                </svg>
                                            </button>
                                            <form action="{{ route('budgets.destroy', $budget->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pos anggaran ini?');" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-icon btn-icon-danger" title="Hapus Anggaran">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <polyline points="3 6 5 6 21 6"></polyline>
                                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="empty-state" style="padding: 1.5rem 1rem;">
                                        <div>Belum ada data anggaran APBDes</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div style="margin-top: 0.75rem;">
                    {{ $budgets->links() }}
                </div>
            </div>
        </div>

        {{-- BOX 2: PROYEK PEMBANGUNAN (KANAN) --}}
        <div class="data-card" style="margin-bottom: 0; display: flex; flex-direction: column;">
            <div class="data-card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(59, 130, 246, 0.12); color: #3b82f6; display: flex; align-items: center; justify-content: center;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;"><path d="M2 22h20"/><path d="M17 2v20"/><path d="M7 10v12"/><path d="M12 6v16"/><path d="M17 6H7"/><path d="M17 10H7"/></svg>
                    </div>
                    <div>
                        <h3 class="data-card-title" style="margin: 0;">Proyek Pembangunan</h3>
                        <div style="font-size: 0.78rem; color: var(--text-muted);">Pelaksanaan & progres proyek fisik desa</div>
                    </div>
                </div>
                <button type="button" class="btn btn-primary btn-sm" onclick="openCreateDevModal()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    Tambah Proyek
                </button>
            </div>

            <div class="data-card-body" style="flex: 1; display: flex; flex-direction: column;">
                {{-- Mini Stats Pembangunan --}}
                <div class="mini-stats-grid">
                    <div class="mini-stat-box" style="border-left: 3px solid #3b82f6;">
                        <div class="mini-stat-label">Total Proyek</div>
                        <div class="mini-stat-value" style="color: #3b82f6;">{{ $devStats['total'] }}</div>
                        <div class="mini-stat-sub">{{ $devStats['planned'] }} Direncanakan</div>
                    </div>
                    <div class="mini-stat-box" style="border-left: 3px solid #f59e0b;">
                        <div class="mini-stat-label">Sedang Berjalan</div>
                        <div class="mini-stat-value" style="color: #f59e0b;">{{ $devStats['ongoing'] }}</div>
                        <div class="mini-stat-sub">Proses pengerjaan</div>
                    </div>
                    <div class="mini-stat-box" style="border-left: 3px solid #10b981;">
                        <div class="mini-stat-label">Selesai</div>
                        <div class="mini-stat-value" style="color: #10b981;">{{ $devStats['completed'] }}</div>
                        <div class="mini-stat-sub">100% Rampung</div>
                    </div>
                </div>

                {{-- Tabel Proyek Pembangunan --}}
                <div class="table-responsive" style="flex: 1;">
                    <table>
                        <thead>
                            <tr>
                                <th>Proyek & Sumber</th>
                                <th>Alokasi Anggaran</th>
                                <th>Status</th>
                                <th>Progres</th>
                                <th style="text-align: right;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($developments as $dev)
                                <tr>
                                    <td>
                                        <div style="font-weight: 600; color: var(--text-primary); font-size: 0.88rem;">{{ $dev->title }}</div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted); display: flex; align-items: center; gap: 0.35rem; margin-top: 1px;">
                                            <span>{{ $dev->source_of_fund ?? 'Dana Desa' }}</span>
                                            @if($dev->location_lat && $dev->location_lng)
                                                <span class="badge badge-blue" style="font-size: 0.65rem; padding: 0 4px;">Peta</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-size: 0.85rem; font-weight: 600; color: var(--text-primary);">
                                            Rp {{ number_format($dev->budget_allocated, 0, ',', '.') }}
                                        </div>
                                    </td>
                                    <td>
                                        @if($dev->status === 'PLANNED')
                                            <span class="badge badge-yellow" style="font-size: 0.68rem; padding: 1px 6px;">Rencana</span>
                                        @elseif($dev->status === 'ONGOING')
                                            <span class="badge badge-blue" style="font-size: 0.68rem; padding: 1px 6px;">Berjalan</span>
                                        @else
                                            <span class="badge badge-green" style="font-size: 0.68rem; padding: 1px 6px;">Selesai</span>
                                        @endif
                                    </td>
                                    <td style="min-width: 95px;">
                                        <div style="font-size: 0.78rem; font-weight: 600;">{{ $dev->progress_percentage }}%</div>
                                        <div class="progress-bar-container">
                                            <div class="progress-bar-fill" style="width: {{ $dev->progress_percentage }}%; background: {{ $dev->progress_percentage >= 100 ? '#10b981' : ($dev->progress_percentage > 40 ? '#3b82f6' : '#f59e0b') }};"></div>
                                        </div>
                                    </td>
                                    <td style="text-align: right;">
                                        <div style="display: inline-flex; gap: 0.35rem; align-items: center; justify-content: flex-end;">
                                            <button type="button" 
                                                    class="btn-icon btn-icon-edit" 
                                                    title="Edit Proyek"
                                                    onclick="openEditDevModal({{ json_encode($dev) }})">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                </svg>
                                            </button>
                                            <form action="{{ route('developments.destroy', $dev->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus proyek ini?');" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-icon btn-icon-danger" title="Hapus Proyek">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <polyline points="3 6 5 6 21 6"></polyline>
                                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="empty-state" style="padding: 1.5rem 1rem;">
                                        <div>Belum ada data proyek pembangunan</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div style="margin-top: 0.75rem;">
                    {{ $developments->links() }}
                </div>
            </div>
        </div>

    </div>

    {{-- BARIS BAWAH: ANALITIK SPASIAL & PETA WILAYAH (LEBAR PENUH) --}}
    <div class="data-card" style="margin-bottom: 1.5rem;">
        <div class="data-card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(168, 85, 247, 0.12); color: #a855f7; display: flex; align-items: center; justify-content: center;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;">
                        <polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"></polygon>
                        <line x1="8" y1="2" x2="8" y2="18"></line>
                        <line x1="16" y1="6" x2="16" y2="22"></line>
                    </svg>
                </div>
                <div>
                    <h3 class="data-card-title" style="margin: 0;">Peta & Analitik Spasial Wilayah</h3>
                    <div style="font-size: 0.78rem; color: var(--text-muted);">Sebaran lokasi pembangunan fisik, aduan warga, dan peringatan darurat real-time</div>
                </div>
            </div>

            {{-- Legend Chips --}}
            <div style="display: flex; gap: 1rem; align-items: center; font-size: 0.82rem; font-weight: 500; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 0.4rem;">
                    <span style="width: 12px; height: 12px; border-radius: 4px; background: #10b981; display: inline-block;"></span>
                    <span>Proyek Pembangunan ({{ $developmentLocations->count() }})</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.4rem;">
                    <span style="width: 12px; height: 12px; border-radius: 50%; background: #3b82f6; display: inline-block;"></span>
                    <span>Pengaduan Warga ({{ $recentComplaints }})</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.4rem;">
                    <span style="width: 12px; height: 12px; border-radius: 50%; background: #ef4444; display: inline-block;"></span>
                    <span>Darurat SOS ({{ $emergencies->count() }})</span>
                </div>
            </div>
        </div>

        <div class="data-card-body">
            @if($unresolvedEmergencies > 0)
                <div class="alert-banner-sos">
                    <div style="background: var(--danger); color: white; border-radius: 50%; width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    </div>
                    <div>
                        <div style="color: var(--danger); font-weight: 700; font-size: 0.9rem;">PERINGATAN DINI: Sinyal Darurat (SOS) Aktif!</div>
                        <div style="font-size: 0.82rem; color: var(--text-secondary);">Terdapat <strong>{{ $unresolvedEmergencies }}</strong> laporan darurat warga aktif yang belum terselesaikan di wilayah desa.</div>
                    </div>
                </div>
            @endif

            <div id="spatial-map"></div>
        </div>
    </div>

    {{-- MODAL 1: TAMBAH PROYEK PEMBANGUNAN --}}
    <div id="createDevModal" class="siwil-modal-overlay">
        <div class="siwil-modal-dialog">
            <div class="siwil-modal-header">
                <div>
                    <h3 class="siwil-modal-title">Tambah Proyek Pembangunan</h3>
                </div>
                <button type="button" class="siwil-modal-close" onclick="closeCreateDevModal()" aria-label="Tutup">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
            <form action="{{ route('developments.store') }}" method="POST">
                @csrf
                <div class="siwil-modal-body">
                    <div class="form-group">
                        <label class="form-label">Nama Proyek Pembangunan *</label>
                        <input type="text" name="title" class="form-input" placeholder="Contoh: Pengaspalan Jalan Dusun II" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Deskripsi Pekerjaan</label>
                        <textarea name="description" class="form-input" rows="2" placeholder="Uraian ringkas spesifikasi dan lingkup pekerjaan..."></textarea>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Alokasi Anggaran (Rp) *</label>
                            <input type="number" name="budget_allocated" class="form-input" placeholder="0" min="0" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Sumber Dana</label>
                            <input type="text" name="source_of_fund" class="form-input" placeholder="Contoh: Dana Desa (DD) 2026">
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Status Proyek *</label>
                            <select name="status" class="form-select" required>
                                <option value="PLANNED">Direncanakan (PLANNED)</option>
                                <option value="ONGOING" selected>Sedang Berjalan (ONGOING)</option>
                                <option value="COMPLETED">Selesai (COMPLETED)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Progres Fisik (%) *</label>
                            <input type="number" name="progress_percentage" class="form-input" value="0" min="0" max="100" required>
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Tanggal Mulai</label>
                            <input type="date" name="start_date" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tanggal Selesai</label>
                            <input type="date" name="end_date" class="form-input">
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Latitude Lokasi</label>
                            <input type="text" name="location_lat" class="form-input" placeholder="-6.2088">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Longitude Lokasi</label>
                            <input type="text" name="location_lng" class="form-input" placeholder="106.8456">
                        </div>
                    </div>
                </div>
                <div class="siwil-modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeCreateDevModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Proyek</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL 2: EDIT PROYEK PEMBANGUNAN --}}
    <div id="editDevModal" class="siwil-modal-overlay">
        <div class="siwil-modal-dialog">
            <div class="siwil-modal-header">
                <div>
                    <h3 class="siwil-modal-title">Edit Proyek Pembangunan</h3>
                </div>
                <button type="button" class="siwil-modal-close" onclick="closeEditDevModal()" aria-label="Tutup">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
            <form id="editDevForm" method="POST">
                @csrf
                @method('PUT')
                <div class="siwil-modal-body">
                    <div class="form-group">
                        <label class="form-label">Nama Proyek Pembangunan *</label>
                        <input type="text" name="title" id="edit_dev_title" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Deskripsi Pekerjaan</label>
                        <textarea name="description" id="edit_dev_description" class="form-input" rows="2"></textarea>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Alokasi Anggaran (Rp) *</label>
                            <input type="number" name="budget_allocated" id="edit_dev_budget_allocated" class="form-input" min="0" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Sumber Dana</label>
                            <input type="text" name="source_of_fund" id="edit_dev_source_of_fund" class="form-input">
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Status Proyek *</label>
                            <select name="status" id="edit_dev_status" class="form-select" required>
                                <option value="PLANNED">Direncanakan (PLANNED)</option>
                                <option value="ONGOING">Sedang Berjalan (ONGOING)</option>
                                <option value="COMPLETED">Selesai (COMPLETED)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Progres Fisik (%) *</label>
                            <input type="number" name="progress_percentage" id="edit_dev_progress_percentage" class="form-input" min="0" max="100" required>
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Tanggal Mulai</label>
                            <input type="date" name="start_date" id="edit_dev_start_date" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tanggal Selesai</label>
                            <input type="date" name="end_date" id="edit_dev_end_date" class="form-input">
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Latitude Lokasi</label>
                            <input type="text" name="location_lat" id="edit_dev_location_lat" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Longitude Lokasi</label>
                            <input type="text" name="location_lng" id="edit_dev_location_lng" class="form-input">
                        </div>
                    </div>
                </div>
                <div class="siwil-modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeEditDevModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Perbarui Proyek</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL 3: TAMBAH POS ANGGARAN APBDES --}}
    <div id="createBudgetModal" class="siwil-modal-overlay">
        <div class="siwil-modal-dialog">
            <div class="siwil-modal-header">
                <div>
                    <h3 class="siwil-modal-title">Tambah Pos Anggaran APBDes</h3>
                </div>
                <button type="button" class="siwil-modal-close" onclick="closeCreateBudgetModal()" aria-label="Tutup">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
            <form action="{{ route('budgets.store') }}" method="POST">
                @csrf
                <div class="siwil-modal-body">
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Tahun Anggaran *</label>
                            <input type="number" name="year" class="form-input" value="{{ date('Y') }}" min="2020" max="2099" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tipe Anggaran *</label>
                            <select name="type" class="form-select" required>
                                <option value="PENDAPATAN">PENDAPATAN</option>
                                <option value="BELANJA">BELANJA</option>
                                <option value="PEMBIAYAAN">PEMBIAYAAN</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Kategori / Akun Rekening *</label>
                        <input type="text" name="category" class="form-input" placeholder="Contoh: Pendapatan Transfer - Dana Desa" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Deskripsi Rincian</label>
                        <textarea name="description" class="form-input" rows="2" placeholder="Uraian alokasi peruntukan anggaran..."></textarea>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Pagu Target Anggaran (Rp) *</label>
                            <input type="number" name="amount_budget" class="form-input" placeholder="0" min="0" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Jumlah Realisasi (Rp) *</label>
                            <input type="number" name="amount_realized" class="form-input" placeholder="0" min="0" value="0" required>
                        </div>
                    </div>
                </div>
                <div class="siwil-modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeCreateBudgetModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Anggaran</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL 4: EDIT POS ANGGARAN APBDES --}}
    <div id="editBudgetModal" class="siwil-modal-overlay">
        <div class="siwil-modal-dialog">
            <div class="siwil-modal-header">
                <div>
                    <h3 class="siwil-modal-title">Edit Pos Anggaran APBDes</h3>
                </div>
                <button type="button" class="siwil-modal-close" onclick="closeEditBudgetModal()" aria-label="Tutup">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
            <form id="editBudgetForm" method="POST">
                @csrf
                @method('PUT')
                <div class="siwil-modal-body">
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Tahun Anggaran *</label>
                            <input type="number" name="year" id="edit_budget_year" class="form-input" min="2020" max="2099" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tipe Anggaran *</label>
                            <select name="type" id="edit_budget_type" class="form-select" required>
                                <option value="PENDAPATAN">PENDAPATAN</option>
                                <option value="BELANJA">BELANJA</option>
                                <option value="PEMBIAYAAN">PEMBIAYAAN</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Kategori / Akun Rekening *</label>
                        <input type="text" name="category" id="edit_budget_category" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Deskripsi Rincian</label>
                        <textarea name="description" id="edit_budget_description" class="form-input" rows="2"></textarea>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">Pagu Target Anggaran (Rp) *</label>
                            <input type="number" name="amount_budget" id="edit_budget_amount_budget" class="form-input" min="0" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Jumlah Realisasi (Rp) *</label>
                            <input type="number" name="amount_realized" id="edit_budget_amount_realized" class="form-input" min="0" required>
                        </div>
                    </div>
                </div>
                <div class="siwil-modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeEditBudgetModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Perbarui Anggaran</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const map = L.map('spatial-map').setView([-6.200000, 106.816666], 13);
        
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);

        const complaints = {!! json_encode($complaints) !!};
        const emergencies = {!! json_encode($emergencies) !!};
        const devLocations = {!! json_encode($developmentLocations) !!};
        const markers = [];

        // Plot Development Projects (Green)
        devLocations.forEach(d => {
            if (d.location_lat && d.location_lng) {
                const lat = parseFloat(d.location_lat);
                const lng = parseFloat(d.location_lng);
                if (!isNaN(lat) && !isNaN(lng)) {
                    const icon = L.divIcon({
                        className: 'custom-dev-icon',
                        html: `<div style="background-color: #10b981; width: 22px; height: 22px; border-radius: 6px; border: 2px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.4); display:flex; align-items:center; justify-content:center; color:white; font-size:11px; font-weight:bold;">🏗️</div>`,
                        iconSize: [22, 22],
                        iconAnchor: [11, 11]
                    });
                    const marker = L.marker([lat, lng], { icon: icon }).addTo(map);
                    marker.bindPopup(`
                        <div style="font-family: inherit; font-size: 0.85rem; line-height: 1.4;">
                            <strong style="color: #10b981;">🏗️ Proyek Pembangunan</strong><br>
                            <strong>${d.title}</strong><br>
                            Status: <span style="font-weight:600;">${d.status}</span> (${d.progress_percentage}%)<br>
                            Anggaran: Rp ${new Intl.NumberFormat('id-ID').format(d.budget_allocated)}
                        </div>
                    `);
                    markers.push(marker);
                }
            }
        });

        // Plot Complaints (Blue)
        complaints.forEach(c => {
            if (c.location_lat && c.location_lng) {
                const lat = parseFloat(c.location_lat);
                const lng = parseFloat(c.location_lng);
                if (!isNaN(lat) && !isNaN(lng)) {
                    const icon = L.divIcon({
                        className: 'custom-complaint-icon',
                        html: `<div style="background-color: #3b82f6; width: 16px; height: 16px; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 5px rgba(0,0,0,0.5);"></div>`,
                        iconSize: [16, 16],
                        iconAnchor: [8, 8]
                    });
                    const marker = L.marker([lat, lng], { icon: icon }).addTo(map);
                    marker.bindPopup(`
                        <div style="font-family: inherit; font-size: 0.85rem; line-height: 1.4;">
                            <strong style="color: #3b82f6;">Pengaduan Warga</strong><br>
                            ${c.description || '-'}<br>
                            <small style="color: #64748b;">Status: ${c.status}</small>
                        </div>
                    `);
                    markers.push(marker);
                }
            }
        });

        // Plot Emergencies (Red with pulse animation)
        emergencies.forEach(e => {
            if (e.location_lat && e.location_lng) {
                const lat = parseFloat(e.location_lat);
                const lng = parseFloat(e.location_lng);
                if (!isNaN(lat) && !isNaN(lng)) {
                    const icon = L.divIcon({
                        className: 'custom-emergency-icon',
                        html: `<div style="background-color: #ef4444; width: 22px; height: 22px; border-radius: 50%; border: 3px solid white; box-shadow: 0 0 8px rgba(239,68,68,0.8); ${e.status === 'ACTIVE' ? 'animation: pulse 1s infinite alternate;' : ''}"></div>`,
                        iconSize: [22, 22],
                        iconAnchor: [11, 11]
                    });
                    const marker = L.marker([lat, lng], { icon: icon }).addTo(map);
                    marker.bindPopup(`
                        <div style="font-family: inherit; font-size: 0.85rem; line-height: 1.4;">
                            <strong style="color: #ef4444;">🚨 Panggilan Darurat (SOS)</strong><br>
                            Jenis: ${e.type}<br>
                            <small style="color: #64748b;">Status: <strong>${e.status}</strong></small>
                        </div>
                    `);
                    markers.push(marker);
                }
            }
        });

        if (markers.length > 0) {
            const group = new L.featureGroup(markers);
            map.fitBounds(group.getBounds().pad(0.15));
        }

        const style = document.createElement('style');
        style.innerHTML = `
            @keyframes pulse {
                0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
                70% { transform: scale(1.15); box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); }
                100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
            }
        `;
        document.head.appendChild(style);
    });

    // Development Modals
    function openCreateDevModal() {
        document.getElementById('createDevModal').classList.add('active');
    }
    function closeCreateDevModal() {
        document.getElementById('createDevModal').classList.remove('active');
    }

    function openEditDevModal(dev) {
        const form = document.getElementById('editDevForm');
        form.action = `/dashboard/developments/${dev.id}`;
        document.getElementById('edit_dev_title').value = dev.title || '';
        document.getElementById('edit_dev_description').value = dev.description || '';
        document.getElementById('edit_dev_budget_allocated').value = dev.budget_allocated || 0;
        document.getElementById('edit_dev_source_of_fund').value = dev.source_of_fund || '';
        document.getElementById('edit_dev_status').value = dev.status || 'PLANNED';
        document.getElementById('edit_dev_progress_percentage').value = dev.progress_percentage || 0;
        document.getElementById('edit_dev_start_date').value = dev.start_date ? dev.start_date.substring(0, 10) : '';
        document.getElementById('edit_dev_end_date').value = dev.end_date ? dev.end_date.substring(0, 10) : '';
        document.getElementById('edit_dev_location_lat').value = dev.location_lat || '';
        document.getElementById('edit_dev_location_lng').value = dev.location_lng || '';

        document.getElementById('editDevModal').classList.add('active');
    }
    function closeEditDevModal() {
        document.getElementById('editDevModal').classList.remove('active');
    }

    // Budget Modals
    function openCreateBudgetModal() {
        document.getElementById('createBudgetModal').classList.add('active');
    }
    function closeCreateBudgetModal() {
        document.getElementById('createBudgetModal').classList.remove('active');
    }

    function openEditBudgetModal(budget) {
        const form = document.getElementById('editBudgetForm');
        form.action = `/dashboard/budgets/${budget.id}`;
        document.getElementById('edit_budget_year').value = budget.year || new Date().getFullYear();
        document.getElementById('edit_budget_type').value = budget.type || 'PENDAPATAN';
        document.getElementById('edit_budget_category').value = budget.category || '';
        document.getElementById('edit_budget_description').value = budget.description || '';
        document.getElementById('edit_budget_amount_budget').value = budget.amount_budget || 0;
        document.getElementById('edit_budget_amount_realized').value = budget.amount_realized || 0;

        document.getElementById('editBudgetModal').classList.add('active');
    }
    function closeEditBudgetModal() {
        document.getElementById('editBudgetModal').classList.remove('active');
    }

    // Close modals on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeCreateDevModal();
            closeEditDevModal();
            closeCreateBudgetModal();
            closeEditBudgetModal();
        }
    });
</script>
@endsection
