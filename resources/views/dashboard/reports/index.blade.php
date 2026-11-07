@extends('layouts.app')

@section('title', 'Laporan & Audit Log - SIWIL AI')
@section('page-title', 'Laporan & Audit Log')

@section('styles')
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

    .report-card-section {
        background: var(--bg-body);
        border: 1px solid var(--border-glass);
        border-radius: 10px;
        padding: 1rem 1.25rem;
        margin-bottom: 1rem;
    }

    .report-section-title {
        font-size: 0.82rem;
        font-weight: 700;
        color: var(--text-primary);
        text-transform: uppercase;
        letter-spacing: 0.4px;
        margin-bottom: 0.75rem;
    }

    .bar-chart { display: flex; flex-direction: column; gap: 0.5rem; }
    .bar-row { display: flex; align-items: center; gap: 0.75rem; }
    .bar-label { font-size: 0.78rem; color: var(--text-secondary); min-width: 90px; text-align: right; font-weight: 500; }
    .bar-track { flex: 1; height: 22px; background: rgba(100, 116, 139, 0.12); border-radius: 6px; overflow: hidden; position: relative; }
    .bar-fill { height: 100%; border-radius: 6px; transition: width 0.6s ease; display: flex; align-items: center; padding-left: 8px; }
    .bar-value { font-size: 0.72rem; font-weight: 700; color: white; white-space: nowrap; }

    .donut-list { display: flex; flex-direction: column; gap: 0.55rem; }
    .donut-item { display: flex; align-items: center; justify-content: space-between; }
    .donut-label-wrap { display: flex; align-items: center; gap: 0.5rem; }
    .donut-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
    .donut-label { font-size: 0.82rem; color: var(--text-secondary); font-weight: 500; }
    .donut-value { font-size: 0.85rem; font-weight: 700; color: var(--text-primary); }

    /* Filter Bar */
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

    /* Audit log styling */
    .log-action {
        display: inline-flex; align-items: center; gap: 0.35rem;
        padding: 0.25rem 0.6rem; border-radius: 6px;
        font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px;
    }
    .log-action-create { background: #dcfce7; color: #166534; border: 1px solid rgba(22, 101, 52, 0.2); }
    .log-action-update { background: #dbeafe; color: #1d4ed8; border: 1px solid rgba(29, 78, 216, 0.2); }
    .log-action-delete { background: #fee2e2; color: #991b1b; border: 1px solid rgba(153, 27, 27, 0.2); }
    .log-action-default { background: #f1f5f9; color: #475569; border: 1px solid rgba(71, 85, 105, 0.2); }

    [data-theme="dark"] .log-action-create { background: rgba(22, 101, 52, 0.25); color: #86efac; border-color: rgba(134, 239, 172, 0.25); }
    [data-theme="dark"] .log-action-update { background: rgba(29, 78, 216, 0.25); color: #93c5fd; border-color: rgba(147, 197, 253, 0.25); }
    [data-theme="dark"] .log-action-delete { background: rgba(153, 27, 27, 0.25); color: #fca5a5; border-color: rgba(252, 165, 165, 0.25); }
    [data-theme="dark"] .log-action-default { background: rgba(71, 85, 105, 0.25); color: #cbd5e1; border-color: rgba(203, 213, 225, 0.25); }

    .log-details {
        font-size: 0.78rem; color: var(--text-secondary);
        max-width: 320px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        display: block;
    }
</style>
@endsection

@section('content')
    {{-- Header --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 class="page-title" style="margin-bottom: 0.25rem; font-size: 1.75rem; font-weight: 800; color: var(--text-primary);">Laporan & Audit Log</h1>
            <p style="color: var(--text-secondary); font-size: 0.92rem; margin: 0; font-weight: 500;">
                Monitoring terpadu: Statistik kependudukan di sisi kiri, Pengajuan surat di sisi kanan, serta Audit Log aktivitas di bagian bawah.
            </p>
        </div>
    </div>

    {{-- Ringkasan Statistik Utama --}}
    <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 1.5rem;">
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Total Penduduk</span>
                <span class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                </span>
            </div>
            <div class="stat-value">{{ number_format($stats['total_residents']) }} <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">Jiwa</span></div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Total Wilayah</span>
                <span class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/></svg>
                </span>
            </div>
            <div class="stat-value" style="display: flex; align-items: baseline; gap: 0.4rem; font-size: 1.25rem;">
                {{ $stats['total_villages'] }} <span style="font-size: 0.82rem; color: var(--text-muted); font-weight: 500;">Desa</span>
                <span style="color: var(--border-glass);">|</span>
                {{ $stats['total_rws'] }} <span style="font-size: 0.82rem; color: var(--text-muted); font-weight: 500;">RW</span>
                <span style="color: var(--border-glass);">|</span>
                {{ $stats['total_rts'] }} <span style="font-size: 0.82rem; color: var(--text-muted); font-weight: 500;">RT</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Pengajuan Surat</span>
                <span class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/></svg>
                </span>
            </div>
            <div class="stat-value">{{ number_format($stats['total_documents']) }} <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">Dokumen</span></div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Total Akun Pengguna</span>
                <span class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </span>
            </div>
            <div class="stat-value">{{ number_format($stats['total_users']) }} <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">Akun</span></div>
        </div>
    </div>

    {{-- BARIS ATAS: 2 BOX BERDAMPINGAN (Statistik Kependudukan di Kiri, Layanan Surat di Kanan) --}}
    <div class="hub-twin-grid">
        
        {{-- BOX 1: STATISTIK KEPENDUDUKAN & PENGGUNA (KIRI) --}}
        <div class="data-card" style="margin-bottom: 0; display: flex; flex-direction: column;">
            <div class="data-card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(59, 130, 246, 0.12); color: #3b82f6; display: flex; align-items: center; justify-content: center;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;"><path d="M18 20V10M12 20V4M6 20v-6"/></svg>
                    </div>
                    <div>
                        <h3 class="data-card-title" style="margin: 0;">Statistik Kependudukan & Akun</h3>
                        <div style="font-size: 0.78rem; color: var(--text-secondary);">Komposisi demografi penduduk dan peran akun</div>
                    </div>
                </div>
            </div>

            <div class="data-card-body" style="flex: 1; display: flex; flex-direction: column; gap: 1rem;">
                {{-- Distribusi Gender & Status Berdampingan --}}
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    {{-- Gender --}}
                    <div class="report-card-section" style="margin-bottom: 0;">
                        <div class="report-section-title">Distribusi Gender</div>
                        @php
                            $totalGender = array_sum($genderStats) ?: 1;
                            $genderColors = ['LAKI_LAKI' => '#3b82f6', 'PEREMPUAN' => '#ec4899'];
                            $genderLabels = ['LAKI_LAKI' => 'Laki-laki', 'PEREMPUAN' => 'Perempuan'];
                        @endphp
                        <div class="donut-list">
                            @foreach($genderStats as $gender => $count)
                            <div class="donut-item">
                                <div class="donut-label-wrap">
                                    <div class="donut-dot" style="background: {{ $genderColors[$gender] ?? '#94a3b8' }};"></div>
                                    <span class="donut-label">{{ $genderLabels[$gender] ?? $gender }}</span>
                                </div>
                                <span class="donut-value">{{ $count }} <small style="color: var(--text-muted); font-weight: normal;">({{ round($count / $totalGender * 100) }}%)</small></span>
                            </div>
                            @endforeach
                            @if(empty($genderStats))
                                <div class="empty-state" style="padding: 0.75rem; font-size: 0.8rem;">Belum ada data.</div>
                            @endif
                        </div>
                    </div>

                    {{-- Status Penduduk --}}
                    <div class="report-card-section" style="margin-bottom: 0;">
                        <div class="report-section-title">Status Penduduk</div>
                        @php
                            $totalStatus = array_sum($statusStats) ?: 1;
                            $statusColors = ['AKTIF' => '#10b981', 'PINDAH' => '#f59e0b', 'MENINGGAL' => '#ef4444'];
                        @endphp
                        <div class="donut-list">
                            @foreach($statusStats as $status => $count)
                            <div class="donut-item">
                                <div class="donut-label-wrap">
                                    <div class="donut-dot" style="background: {{ $statusColors[$status] ?? '#94a3b8' }};"></div>
                                    <span class="donut-label">{{ $status }}</span>
                                </div>
                                <span class="donut-value">{{ $count }} <small style="color: var(--text-muted); font-weight: normal;">({{ round($count / $totalStatus * 100) }}%)</small></span>
                            </div>
                            @endforeach
                            @if(empty($statusStats))
                                <div class="empty-state" style="padding: 0.75rem; font-size: 0.8rem;">Belum ada data.</div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Pengguna per Role --}}
                <div class="report-card-section" style="margin-bottom: 0; flex: 1;">
                    <div class="report-section-title">Pengguna Terdaftar per Role Akun</div>
                    @php
                        $maxRole = max($usersPerRole ?: [1]);
                        $roleChartColors = ['SUPER_ADMIN' => '#ef4444', 'ADMIN_DESA' => '#3b82f6', 'RW' => '#10b981', 'RT' => '#f59e0b', 'WARGA' => '#8b5cf6'];
                    @endphp
                    <div class="bar-chart">
                        @foreach($usersPerRole as $role => $count)
                        <div class="bar-row">
                            <span class="bar-label">{{ $role }}</span>
                            <div class="bar-track">
                                <div class="bar-fill" style="width: {{ ($count / $maxRole) * 100 }}%; background: {{ $roleChartColors[$role] ?? '#94a3b8' }};">
                                    <span class="bar-value">{{ $count }}</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        @if(empty($usersPerRole))
                            <div class="empty-state" style="padding: 0.75rem; font-size: 0.8rem;">Belum ada data pengguna.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- BOX 2: LAYANAN ADMINISTRASI SURAT (KANAN) --}}
        <div class="data-card" style="margin-bottom: 0; display: flex; flex-direction: column;">
            <div class="data-card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(16, 185, 129, 0.12); color: #10b981; display: flex; align-items: center; justify-content: center;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/></svg>
                    </div>
                    <div>
                        <h3 class="data-card-title" style="margin: 0;">Layanan Administrasi Surat</h3>
                        <div style="font-size: 0.78rem; color: var(--text-secondary);">Rekap status dan pengajuan surat warga terkini</div>
                    </div>
                </div>
                <span class="badge badge-green">{{ $stats['total_documents'] }} Pengajuan</span>
            </div>

            <div class="data-card-body" style="flex: 1; display: flex; flex-direction: column; gap: 1rem;">
                {{-- Status Pengajuan Surat --}}
                <div class="report-card-section" style="margin-bottom: 0;">
                    <div class="report-section-title">Status Pengajuan Surat</div>
                    @php
                        $pendingCnt = $documentStats['PENDING'] ?? (($documentStats['DIAJUKAN_RT'] ?? 0) + ($documentStats['DRAFT'] ?? 0));
                        $approvedCnt = $documentStats['APPROVED'] ?? ($documentStats['DISETUJUI'] ?? 0);
                        $rejectedCnt = $documentStats['REJECTED'] ?? ($documentStats['DITOLAK'] ?? 0);
                        $processedCnt = $documentStats['PROCESSED'] ?? (($documentStats['DIAJUKAN_RW'] ?? 0) + ($documentStats['DIAJUKAN_DESA'] ?? 0) + ($documentStats['DIPROSES'] ?? 0));
                        $doneCnt = $documentStats['DONE'] ?? ($documentStats['SELESAI'] ?? 0);

                        $docSummaryItems = [
                            ['label' => 'Menunggu', 'count' => $pendingCnt, 'color' => '#f59e0b'],
                            ['label' => 'Disetujui', 'count' => $approvedCnt, 'color' => '#10b981'],
                            ['label' => 'Ditolak', 'count' => $rejectedCnt, 'color' => '#ef4444'],
                            ['label' => 'Diproses', 'count' => $processedCnt, 'color' => '#3b82f6'],
                            ['label' => 'Selesai', 'count' => $doneCnt, 'color' => '#8b5cf6'],
                        ];
                    @endphp
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(110px, 1fr)); gap: 0.5rem;">
                        @foreach($docSummaryItems as $item)
                        <div style="background: var(--bg-card); border: 1px solid var(--border-glass); border-radius: 8px; padding: 0.5rem 0.65rem; text-align: center;">
                            <div style="font-size: 0.72rem; color: var(--text-secondary); font-weight: 500;">{{ $item['label'] }}</div>
                            <div style="font-size: 1.15rem; font-weight: 700; color: {{ $item['color'] }}; margin-top: 2px;">{{ $item['count'] }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Tabel Pengajuan Surat Terbaru --}}
                <div style="flex: 1; display: flex; flex-direction: column;">
                    <div style="font-weight: 700; font-size: 0.82rem; color: var(--text-primary); margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.4px;">
                        Pengajuan Surat Terbaru
                    </div>
                    <div class="table-responsive" style="flex: 1;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Pemohon</th>
                                    <th>Jenis Surat</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentDocuments as $doc)
                                <tr>
                                    <td style="font-weight: 600; color: var(--text-primary); font-size: 0.88rem;">{{ $doc->resident->full_name ?? '-' }}</td>
                                    <td style="font-size: 0.82rem; font-weight: 500;">{{ ucwords(str_replace('_', ' ', strtolower($doc->type ?? '-'))) }}</td>
                                    <td>
                                        @php
                                            $badgeMap = [
                                                'DIAJUKAN_RT' => 'badge-yellow',
                                                'PENDING' => 'badge-yellow',
                                                'DRAFT' => 'badge-gray',
                                                'DIAJUKAN_RW' => 'badge-blue',
                                                'DIAJUKAN_DESA' => 'badge-blue',
                                                'DIPROSES' => 'badge-blue',
                                                'PROCESSED' => 'badge-blue',
                                                'DISETUJUI' => 'badge-green',
                                                'APPROVED' => 'badge-green',
                                                'SELESAI' => 'badge-green',
                                                'DONE' => 'badge-green',
                                                'DITOLAK' => 'badge-red',
                                                'REJECTED' => 'badge-red',
                                            ];
                                            $readableStatus = [
                                                'DIAJUKAN_RT' => 'Menunggu RT',
                                                'DIAJUKAN_RW' => 'Menunggu RW',
                                                'DIAJUKAN_DESA' => 'Menunggu Desa',
                                                'PENDING' => 'Menunggu',
                                                'DIPROSES' => 'Diproses',
                                                'DISETUJUI' => 'Disetujui',
                                                'SELESAI' => 'Selesai',
                                                'DITOLAK' => 'Ditolak',
                                                'APPROVED' => 'Disetujui',
                                                'REJECTED' => 'Ditolak',
                                            ];
                                        @endphp
                                        <span class="badge {{ $badgeMap[$doc->status] ?? 'badge-gray' }}" style="font-size: 0.72rem; padding: 2px 7px; font-weight: 600;">{{ $readableStatus[$doc->status] ?? $doc->status }}</span>
                                    </td>
                                    <td style="font-size: 0.78rem; color: var(--text-secondary);">{{ $doc->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="empty-state" style="padding: 1.5rem 1rem; font-size: 0.85rem;">Belum ada pengajuan surat terbaru.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- BARIS BAWAH: AUDIT LOG AKTIVITAS SISTEM (LEBAR PENUH) --}}
    <div class="data-card" style="margin-bottom: 1.5rem;">
        <div class="data-card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(168, 85, 247, 0.12); color: #a855f7; display: flex; align-items: center; justify-content: center;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                </div>
                <div>
                    <h3 class="data-card-title" style="margin: 0;">Riwayat Aktivitas & Audit Log Sistem</h3>
                    <div style="font-size: 0.78rem; color: var(--text-secondary);">Catatan transparansi penambahan, pengubahan, dan penghapusan data</div>
                </div>
            </div>
            <span id="reportsLogCountBadge" class="badge badge-blue">{{ $logs->total() }} Total Log</span>
        </div>

        <div class="data-card-body">
            {{-- Filter Bar Log --}}
            <div class="filter-bar">
                <form method="GET" action="{{ route('reports.index') }}" data-ajax-filter="true" data-ajax-target="#reportsLogTableContainer" data-count-badge="#reportsLogCountBadge" style="display: flex; gap: 0.75rem; align-items: center; width: 100%; flex-wrap: wrap;">
                    <input type="text" name="search_log" class="form-input" placeholder="Cari user, aksi, entitas..." value="{{ request('search_log') }}" style="max-width: 260px; font-size: 0.85rem;">

                    <select name="action_filter" class="form-select" style="max-width: 160px; font-size: 0.85rem;">
                        <option value="">Semua Aksi</option>
                        @foreach($actions as $action)
                            <option value="{{ $action }}" {{ request('action_filter') === $action ? 'selected' : '' }}>{{ $action }}</option>
                        @endforeach
                    </select>

                    <select name="entity_filter" class="form-select" style="max-width: 160px; font-size: 0.85rem;">
                        <option value="">Semua Entitas</option>
                        @foreach($entities as $entity)
                            <option value="{{ $entity }}" {{ request('entity_filter') === $entity ? 'selected' : '' }}>{{ $entity }}</option>
                        @endforeach
                    </select>

                    <button type="submit" class="btn btn-primary btn-sm">Filter Log</button>
                    @if(request('search_log') || request('action_filter') || request('entity_filter'))
                        <a href="{{ route('reports.index') }}" class="btn btn-outline btn-sm reset-filter-btn">Reset</a>
                    @endif
                </form>
            </div>

            <div id="reportsLogTableContainer">
            {{-- Tabel Audit Log --}}
            @if($logs->count() > 0)
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>User Pelaksana</th>
                                <th>Aksi</th>
                                <th>Entitas</th>
                                <th>Detail Perubahan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                            <tr>
                                <td style="white-space: nowrap; font-size: 0.825rem; color: var(--text-secondary);">
                                    {{ $log->created_at->format('d M Y H:i:s') }}
                                </td>
                                <td>
                                    <div style="font-weight: 600; color: var(--text-primary); font-size: 0.88rem;">
                                        {{ $log->user->name ?? ($log->user->username ?? 'System') }}
                                    </div>
                                    @if(isset($log->user->role))
                                        <span style="font-size: 0.725rem; color: var(--text-secondary);">Role: {{ $log->user->role }}</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $actionClass = match($log->action) {
                                            'CREATE' => 'log-action-create',
                                            'UPDATE' => 'log-action-update',
                                            'DELETE' => 'log-action-delete',
                                            default => 'log-action-default',
                                        };
                                    @endphp
                                    <span class="log-action {{ $actionClass }}">{{ $log->action }}</span>
                                </td>
                                <td><span class="badge badge-gray" style="font-size: 0.72rem;">{{ $log->entity_name }}</span></td>
                                <td>
                                    <span class="log-details" title="{{ json_encode($log->details) }}">
                                        @if($log->details && is_array($log->details))
                                            @foreach($log->details as $key => $value)
                                                <strong>{{ $key }}:</strong> {{ is_array($value) ? json_encode($value) : $value }}@if(!$loop->last), @endif
                                            @endforeach
                                        @elseif($log->details)
                                            {{ $log->details }}
                                        @else
                                            <span style="color: var(--text-secondary);">-</span>
                                        @endif
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div style="margin-top: 1rem;">
                    {{ $logs->links() }}
                </div>
            @else
                <div class="empty-state" style="padding: 2.5rem; text-align: center; color: var(--text-secondary);">
                    Tidak ditemukan data audit log aktivitas yang sesuai.
                </div>
            @endif
            </div>
        </div>
    </div>
@endsection
