@extends('layouts.app')

@section('title', 'Dashboard Warga - SIWIL AI')
@section('page-title', 'Dashboard Warga')

@section('styles')
<style>
    .hub-twin-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
        align-items: stretch;
    }

    @media (max-width: 1024px) {
        .hub-twin-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Warga Greeting Banner */
    .warga-banner {
        background: var(--bg-card);
        border: 1px solid var(--border-glass);
        border-radius: 16px;
        padding: 1.5rem 1.75rem;
        margin-bottom: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1.25rem;
        box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.05);
    }

    .warga-banner-info {
        display: flex;
        align-items: center;
        gap: 1.25rem;
    }

    .warga-avatar-box {
        width: 58px;
        height: 58px;
        border-radius: 14px;
        background: var(--accent-gradient);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        box-shadow: 0 8px 18px rgba(37, 99, 235, 0.28);
    }

    .warga-avatar-box svg {
        width: 30px;
        height: 30px;
    }

    .warga-greeting {
        font-size: 1.35rem;
        font-weight: 800;
        color: var(--text-primary);
        margin-bottom: 0.2rem;
    }

    .warga-sub-loc {
        font-size: 0.88rem;
        color: var(--text-secondary);
        display: flex;
        align-items: center;
        gap: 0.4rem;
        flex-wrap: wrap;
    }

    .warga-banner-actions {
        display: flex;
        gap: 0.6rem;
        flex-wrap: wrap;
        align-items: center;
    }

    /* Poll Voting Cards */
    .poll-card {
        background: var(--bg-body);
        border: 1px solid var(--border-glass);
        border-radius: 12px;
        padding: 1.15rem;
        margin-bottom: 1rem;
        transition: all 0.25s ease;
    }

    .poll-card:last-child {
        margin-bottom: 0;
    }

    .poll-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.35rem;
    }

    .poll-meta {
        font-size: 0.78rem;
        color: var(--text-secondary);
        margin-bottom: 0.85rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .poll-option-radio {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.55rem 0.75rem;
        border-radius: 8px;
        background: var(--bg-card);
        border: 1px solid var(--border-glass);
        margin-bottom: 0.45rem;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 0.88rem;
        color: var(--text-primary);
    }

    .poll-option-radio:hover {
        border-color: var(--accent-1);
        background: rgba(37, 99, 235, 0.04);
    }

    .poll-result-bar {
        margin-bottom: 0.65rem;
    }

    .poll-result-header {
        display: flex;
        justify-content: space-between;
        font-size: 0.82rem;
        color: var(--text-secondary);
        margin-bottom: 0.25rem;
    }

    .poll-track {
        height: 10px;
        background: rgba(148, 163, 184, 0.18);
        border-radius: 999px;
        overflow: hidden;
    }

    .poll-fill {
        height: 100%;
        background: var(--accent-gradient);
        border-radius: 999px;
        transition: width 0.6s ease;
    }

    /* Announcement mini feed */
    .ann-item {
        background: var(--bg-body);
        border: 1px solid var(--border-glass);
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 0.85rem;
        transition: transform 0.2s;
    }

    .ann-item:last-child {
        margin-bottom: 0;
    }

    .ann-item:hover {
        transform: translateY(-2px);
    }

    .ann-date {
        font-size: 0.74rem;
        font-weight: 700;
        color: var(--accent-1);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.25rem;
    }

    .ann-title {
        font-size: 0.92rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.35rem;
    }

    .ann-excerpt {
        font-size: 0.82rem;
        color: var(--text-secondary);
        line-height: 1.5;
    }

    /* Modal Popup Styling */
    .siwil-modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(15, 23, 42, 0.65);
        backdrop-filter: blur(4px);
        z-index: 99999;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        opacity: 0;
        transition: opacity 0.25s ease;
    }

    .siwil-modal-backdrop.active {
        display: flex;
        opacity: 1;
    }

    .siwil-modal-dialog {
        background: var(--bg-card);
        border: 1px solid var(--border-glass);
        border-radius: 18px;
        width: 100%;
        max-width: 580px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
        transform: translateY(20px) scale(0.96);
        transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .siwil-modal-backdrop.active .siwil-modal-dialog {
        transform: translateY(0) scale(1);
    }

    .siwil-modal-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid var(--border-glass);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .siwil-modal-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin: 0;
    }

    .siwil-modal-close {
        background: transparent;
        border: none;
        color: var(--text-muted);
        cursor: pointer;
        padding: 4px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }

    .siwil-modal-close:hover {
        background: rgba(148, 163, 184, 0.15);
        color: var(--text-primary);
    }

    .siwil-modal-body {
        padding: 1.5rem;
    }

    .siwil-modal-footer {
        padding: 1rem 1.5rem;
        border-top: 1px solid var(--border-glass);
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
    }
</style>
@endsection

@section('content')
    {{-- Notifikasi Sukses / Error --}}
    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 1.25rem;">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-error" style="margin-bottom: 1.25rem;">
            {{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-error" style="margin-bottom: 1.25rem;">
            <ul style="margin: 0; padding-left: 1rem;">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Banner Selamat Datang Warga --}}
    <div class="warga-banner">
        <div class="warga-banner-info">
            <div class="warga-avatar-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </div>
            <div>
                <h1 class="warga-greeting">
                    Halo, {{ $resident->full_name ?? ($user->name ?? $user->username) }}! 👋
                </h1>
                <div class="warga-sub-loc">
                    <span>Wilayah:</span>
                    <strong>RT {{ $resident->rt->number ?? ($user->rt->number ?? '-') }} / RW {{ $resident->rt->rw->number ?? ($user->rt->rw->number ?? '-') }}</strong>
                    <span>•</span>
                    <span>Desa {{ $resident->rt->rw->village->name ?? ($user->rt->rw->village->name ?? 'Sukamaju') }}</span>
                </div>
            </div>
        </div>

        <div class="warga-banner-actions">
            <button type="button" onclick="openFloatingChat()" class="btn btn-primary btn-sm" style="display: inline-flex; align-items: center; gap: 0.4rem;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                Tanya SIWIL AI
            </button>
            <button type="button" onclick="openModal('modalCreateDocument')" class="btn btn-outline btn-sm" style="display: inline-flex; align-items: center; gap: 0.4rem;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Buat Surat
            </button>
            <button type="button" onclick="openModal('modalCreateComplaint')" class="btn btn-outline btn-sm" style="display: inline-flex; align-items: center; gap: 0.4rem;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Lapor Pengaduan
            </button>
        </div>
    </div>

    {{-- Ringkasan Metrik (4 Kartu) --}}
    <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); margin-bottom: 1.5rem;">
        {{-- Stat 1: Pengajuan Surat Saya --}}
        <div class="stat-card" style="cursor: pointer;" onclick="openModal('modalCreateDocument')">
            <div class="stat-header">
                <span class="stat-title">Pengajuan Surat</span>
                <span class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/></svg>
                </span>
            </div>
            <div class="stat-value">{{ $totalDocuments }} <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">Surat</span></div>
            <div style="font-size: 0.78rem; margin-top: 0.35rem; display: flex; gap: 0.4rem; align-items: center;">
                <span class="badge badge-yellow" style="font-size: 0.7rem; padding: 1px 6px;">{{ $pendingDocuments }} Diproses</span>
                <span class="badge badge-green" style="font-size: 0.7rem; padding: 1px 6px;">{{ $completedDocuments }} Selesai</span>
            </div>
        </div>

        {{-- Stat 2: Pengaduan Saya --}}
        <div class="stat-card" style="cursor: pointer;" onclick="openModal('modalCreateComplaint')">
            <div class="stat-header">
                <span class="stat-title">Pengaduan Warga</span>
                <span class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                </span>
            </div>
            <div class="stat-value">{{ $totalComplaints }} <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">Laporan</span></div>
            <div style="font-size: 0.78rem; margin-top: 0.35rem; display: flex; gap: 0.4rem; align-items: center;">
                <span class="badge badge-yellow" style="font-size: 0.7rem; padding: 1px 6px;">{{ $processingComplaints }} Menunggu</span>
                <span class="badge badge-green" style="font-size: 0.7rem; padding: 1px 6px;">{{ $resolvedComplaints }} Selesai</span>
            </div>
        </div>

        {{-- Stat 3: Musyawarah / Polling --}}
        <div class="stat-card" style="cursor: pointer;" onclick="window.location='{{ route('polls.index') }}'">
            <div class="stat-header">
                <span class="stat-title">Musyawarah & Polling</span>
                <span class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13h-.68l-2 2h1.91L19 17H5l1.78-2h2.05l-2-2H6l-3 3v2c0 1.1.89 2 1.99 2H19c1.1 0 2-.9 2-2v-2l-3-3zm-1-5.05l-4.95 4.95-3.54-3.54 4.95-4.95L16.99 7.95z"/></svg>
                </span>
            </div>
            <div class="stat-value">{{ $activePollsCount }} <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">Aktif</span></div>
            <div style="font-size: 0.78rem; margin-top: 0.35rem; color: var(--accent-1); font-weight: 600;">
                Partisipasi Suara &rarr;
            </div>
        </div>

        {{-- Stat 4: Informasi Publik --}}
        <div class="stat-card" style="cursor: pointer;" onclick="window.location='{{ route('announcements.index') }}'">
            <div class="stat-header">
                <span class="stat-title">Informasi Publik</span>
                <span class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                </span>
            </div>
            <div class="stat-value">{{ $announcements->count() }} <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">Pengumuman</span></div>
            <div style="font-size: 0.78rem; margin-top: 0.35rem; color: var(--accent-2); font-weight: 600;">
                Warta Resmi Wilayah &rarr;
            </div>
        </div>
    </div>

    {{-- BARIS 1: TWIN GRID (Pengajuan Surat di Kiri, Laporan & Pengaduan di Kanan) --}}
    <div class="hub-twin-grid">
        
        {{-- BOX 1: PENGAJUAN SURAT MANDIRI (KIRI) --}}
        <div class="data-card" style="margin-bottom: 0; display: flex; flex-direction: column;">
            <div class="data-card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(37, 99, 235, 0.12); color: #2563eb; display: flex; align-items: center; justify-content: center;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/></svg>
                    </div>
                    <div>
                        <h3 class="data-card-title" style="margin: 0;">Pengajuan Surat Mandiri</h3>
                        <div style="font-size: 0.78rem; color: var(--text-secondary);">Riwayat dan permohonan surat pengantar Anda</div>
                    </div>
                </div>

                <div style="display: flex; gap: 0.4rem; align-items: center;">
                    <button type="button" onclick="openModal('modalCreateDocument')" class="btn btn-primary btn-sm" style="font-size: 0.8rem; padding: 0.35rem 0.65rem;">
                        + Buat Surat
                    </button>
                    <a href="{{ route('documents.index') }}" class="btn btn-outline btn-sm" style="font-size: 0.8rem; padding: 0.35rem 0.65rem;">
                        Semua
                    </a>
                </div>
            </div>

            <div class="data-card-body" style="flex: 1; display: flex; flex-direction: column; padding: 0;">
                @if($documentRequests->count() > 0)
                    <div class="table-responsive" style="flex: 1;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Nomor / Jenis Surat</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                    <th style="text-align: right;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($documentRequests as $req)
                                <tr>
                                    <td>
                                        <div style="font-weight: 700; color: var(--text-primary); font-size: 0.88rem;">
                                            {{ str_replace('_', ' ', $req->type) }}
                                        </div>
                                        <div style="font-size: 0.75rem; color: var(--text-secondary);">
                                            No: {{ $req->request_number }}
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $isDone = in_array($req->status, ['SELESAI', 'DISETUJUI', 'COMPLETED']);
                                            $isRejected = ($req->status === 'DITOLAK' || $req->status === 'REJECTED');
                                        @endphp
                                        <span class="badge {{ $isDone ? 'badge-green' : ($isRejected ? 'badge-red' : 'badge-yellow') }}" style="font-size: 0.72rem;">
                                            {{ $req->status }}
                                        </span>
                                    </td>
                                    <td style="font-size: 0.78rem; color: var(--text-secondary); white-space: nowrap;">
                                        {{ $req->created_at->format('d M Y') }}
                                    </td>
                                    <td style="text-align: right; white-space: nowrap;">
                                        <div style="display: inline-flex; gap: 0.35rem; align-items: center; justify-content: flex-end;">
                                            <a href="{{ route('documents.show', $req->id) }}" class="btn-icon btn-icon-view" title="Detail Pengajuan" aria-label="Detail Pengajuan">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"></path>
                                                    <circle cx="12" cy="12" r="3"></circle>
                                                </svg>
                                            </a>
                                            @if($isDone)
                                                <a href="{{ route('documents.print', $req->id) }}" target="_blank" class="btn-icon btn-icon-success" title="Cetak Surat" aria-label="Cetak Surat">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <polyline points="6 9 6 2 18 2 18 9"/>
                                                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                                                        <rect x="6" y="14" width="12" height="8"/>
                                                    </svg>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="empty-state" style="padding: 2.5rem 1rem; text-align: center;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width: 42px; height: 42px; margin: 0 auto 0.75rem; color: var(--text-muted);"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/></svg>
                        <div style="font-weight: 700; color: var(--text-primary); margin-bottom: 0.25rem;">Belum Ada Pengajuan Surat</div>
                        <div style="font-size: 0.82rem; color: var(--text-secondary); margin-bottom: 1rem;">Anda belum pernah membuat permohonan surat pengantar mandiri.</div>
                        <button type="button" onclick="openModal('modalCreateDocument')" class="btn btn-primary btn-sm">
                            + Buat Pengajuan Sekarang
                        </button>
                    </div>
                @endif
            </div>
        </div>

        {{-- BOX 2: LAPORAN & PENGADUAN WARGA (KANAN) --}}
        <div class="data-card" style="margin-bottom: 0; display: flex; flex-direction: column;">
            <div class="data-card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(239, 68, 68, 0.12); color: #ef4444; display: flex; align-items: center; justify-content: center;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    </div>
                    <div>
                        <h3 class="data-card-title" style="margin: 0;">Laporan & Pengaduan Warga</h3>
                        <div style="font-size: 0.78rem; color: var(--text-secondary);">Pantau aspirasi dan laporan keluhan lingkungan Anda</div>
                    </div>
                </div>

                <div style="display: flex; gap: 0.4rem; align-items: center;">
                    <button type="button" onclick="openModal('modalCreateComplaint')" class="btn btn-danger btn-sm" style="font-size: 0.8rem; padding: 0.35rem 0.65rem;">
                        + Lapor Masalah
                    </button>
                    <a href="{{ route('complaints.index') }}" class="btn btn-outline btn-sm" style="font-size: 0.8rem; padding: 0.35rem 0.65rem;">
                        Semua
                    </a>
                </div>
            </div>

            <div class="data-card-body" style="flex: 1; display: flex; flex-direction: column; padding: 0;">
                @if($myComplaints->count() > 0)
                    <div class="table-responsive" style="flex: 1;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Judul Pengaduan</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                    <th style="text-align: right;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($myComplaints as $c)
                                <tr>
                                    <td>
                                        <div style="font-weight: 700; color: var(--text-primary); font-size: 0.88rem;">
                                            {{ $c->title }}
                                        </div>
                                        <div style="font-size: 0.75rem; color: var(--text-secondary); max-width: 260px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            {{ $c->description }}
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $compBadge = match($c->status) {
                                                'RESOLVED', 'SELESAI' => 'badge-green',
                                                'PROCESSING', 'DIPROSES' => 'badge-blue',
                                                'REJECTED', 'DITOLAK' => 'badge-red',
                                                default => 'badge-yellow',
                                            };
                                        @endphp
                                        <span class="badge {{ $compBadge }}" style="font-size: 0.72rem;">
                                            {{ $c->status }}
                                        </span>
                                    </td>
                                    <td style="font-size: 0.78rem; color: var(--text-secondary); white-space: nowrap;">
                                        {{ $c->created_at->format('d M Y') }}
                                    </td>
                                    <td style="text-align: right; white-space: nowrap;">
                                        <div style="display: inline-flex; gap: 0.35rem; align-items: center; justify-content: flex-end;">
                                            <a href="{{ route('complaints.show', $c->id) }}" class="btn-icon btn-icon-view" title="Detail Pengaduan" aria-label="Detail Pengaduan">
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
                @else
                    <div class="empty-state" style="padding: 2.5rem 1rem; text-align: center;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width: 42px; height: 42px; margin: 0 auto 0.75rem; color: var(--text-muted);"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        <div style="font-weight: 700; color: var(--text-primary); margin-bottom: 0.25rem;">Tidak Ada Laporan Pengaduan</div>
                        <div style="font-size: 0.82rem; color: var(--text-secondary); margin-bottom: 1rem;">Lingkungan aman & kondusif. Laporkan bila ada kendala jalan, kebersihan, atau keamanan.</div>
                        <button type="button" onclick="openModal('modalCreateComplaint')" class="btn btn-danger btn-sm">
                            + Laporkan Masalah Lingkungan
                        </button>
                    </div>
                @endif
            </div>
        </div>

    </div>

    {{-- BARIS 2: TWIN GRID (Musyawarah & Polling di Kiri, Informasi Publik & Pengumuman di Kanan) --}}
    <div class="hub-twin-grid">

        {{-- BOX 3: MUSYAWARAH WARGA & POLLING AKTIF (KIRI) --}}
        <div class="data-card" style="margin-bottom: 0; display: flex; flex-direction: column;">
            <div class="data-card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(168, 85, 247, 0.12); color: #a855f7; display: flex; align-items: center; justify-content: center;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;"><path d="M18 13h-.68l-2 2h1.91L19 17H5l1.78-2h2.05l-2-2H6l-3 3v2c0 1.1.89 2 1.99 2H19c1.1 0 2-.9 2-2v-2l-3-3zm-1-5.05l-4.95 4.95-3.54-3.54 4.95-4.95L16.99 7.95z"/></svg>
                    </div>
                    <div>
                        <h3 class="data-card-title" style="margin: 0;">Musyawarah & Polling Warga</h3>
                        <div style="font-size: 0.78rem; color: var(--text-secondary);">Partisipasi aktif dalam pengambilan keputusan bersama</div>
                    </div>
                </div>

                <a href="{{ route('polls.index') }}" class="btn btn-outline btn-sm" style="font-size: 0.8rem; padding: 0.35rem 0.65rem;">
                    Semua Musyawarah &rarr;
                </a>
            </div>

            <div class="data-card-body" style="flex: 1; display: flex; flex-direction: column; gap: 1rem;">
                @forelse($polls as $poll)
                    @php
                        $hasVoted = $poll->votes->where('user_id', $user->id)->isNotEmpty();
                        $userVoteOptionId = $hasVoted ? $poll->votes->where('user_id', $user->id)->first()->poll_option_id : null;
                        $totalVotes = $poll->votes->count();
                        $isExpired = $poll->expires_at && $poll->expires_at->isPast();
                        $isActive = ($poll->status === 'ACTIVE' && !$isExpired);
                    @endphp

                    <div class="poll-card">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem; margin-bottom: 0.35rem;">
                            <div class="poll-title" style="margin-bottom: 0;">{{ $poll->title }}</div>
                            <span class="badge {{ $isActive ? 'badge-green' : 'badge-gray' }}" style="font-size: 0.7rem; padding: 1px 6px;">
                                {{ $isActive ? 'AKTIF' : 'SELESAI' }}
                            </span>
                        </div>

                        <div class="poll-meta">
                            <span>Dibuat oleh: {{ $poll->creator->name ?? 'Pengurus Wilayah' }}</span>
                            <span>Batas: {{ $poll->expires_at ? $poll->expires_at->format('d M Y') : 'Tanpa Batas' }}</span>
                        </div>

                        {{-- JIKA SUDAH VOTE / POLLING SUDAH TUTUP: Tampilkan Progress Hasil --}}
                        @if($hasVoted || !$isActive)
                            <div style="margin-bottom: 0.5rem;">
                                @if($hasVoted)
                                    <div style="font-size: 0.78rem; font-weight: 700; color: #10b981; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.35rem;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                        Suara Anda telah tercatat pada musyawarah ini.
                                    </div>
                                @endif

                                @foreach($poll->options as $opt)
                                    @php
                                        $optVotes = $opt->votes->count();
                                        $pct = $totalVotes > 0 ? round(($optVotes / $totalVotes) * 100) : 0;
                                        $isMyChoice = ($opt->id === $userVoteOptionId);
                                    @endphp
                                    <div class="poll-result-bar">
                                        <div class="poll-result-header">
                                            <span style="font-weight: {{ $isMyChoice ? '700' : '500' }}; color: {{ $isMyChoice ? 'var(--accent-1)' : 'var(--text-primary)' }};">
                                                {{ $opt->text }} @if($isMyChoice) <small>(Pilihan Anda)</small> @endif
                                            </span>
                                            <span style="font-weight: 700; color: var(--text-primary);">{{ $optVotes }} suara ({{ $pct }}%)</span>
                                        </div>
                                        <div class="poll-track">
                                            <div class="poll-fill" style="width: {{ $pct }}%;"></div>
                                        </div>
                                    </div>
                                @endforeach
                                <div style="font-size: 0.75rem; color: var(--text-muted); text-align: right; margin-top: 0.25rem;">
                                    Total: {{ $totalVotes }} suara masuk
                                </div>
                            </div>
                        @else
                            {{-- JIKA BELUM VOTE: Form pemilihan suara langsung --}}
                            <form action="{{ route('polls.vote', $poll->id) }}" method="POST">
                                @csrf
                                <div style="margin-bottom: 0.75rem;">
                                    @foreach($poll->options as $opt)
                                        <label class="poll-option-radio">
                                            <input type="radio" name="option_id" value="{{ $opt->id }}" required style="accent-color: var(--accent-1);">
                                            <span>{{ $opt->text }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="font-size: 0.76rem; color: var(--text-secondary);">Pilih salah satu opsi lalu klik Kirim Suara.</span>
                                    <button type="submit" class="btn btn-primary btn-sm" style="font-size: 0.8rem; padding: 0.35rem 0.75rem;">
                                        Kirim Suara
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                @empty
                    <div class="empty-state" style="padding: 2.5rem 1rem; text-align: center;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width: 42px; height: 42px; margin: 0 auto 0.75rem; color: var(--text-muted);"><path d="M18 13h-.68l-2 2h1.91L19 17H5l1.78-2h2.05l-2-2H6l-3 3v2c0 1.1.89 2 1.99 2H19c1.1 0 2-.9 2-2v-2l-3-3zm-1-5.05l-4.95 4.95-3.54-3.54 4.95-4.95L16.99 7.95z"/></svg>
                        <div style="font-weight: 700; color: var(--text-primary); margin-bottom: 0.25rem;">Belum Ada Musyawarah Aktif</div>
                        <div style="font-size: 0.82rem; color: var(--text-secondary);">Saat ini belum ada pemungutan suara atau musyawarah warga yang sedang berlangsung.</div>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- BOX 4: INFORMASI PUBLIK & PENGUMUMAN RESMI (KANAN) --}}
        <div class="data-card" style="margin-bottom: 0; display: flex; flex-direction: column;">
            <div class="data-card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(16, 185, 129, 0.12); color: #10b981; display: flex; align-items: center; justify-content: center;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                    </div>
                    <div>
                        <h3 class="data-card-title" style="margin: 0;">Informasi Publik & Pengumuman</h3>
                        <div style="font-size: 0.78rem; color: var(--text-secondary);">Agenda kegiatan dan kabar resmi wilayah Anda</div>
                    </div>
                </div>

                <a href="{{ route('announcements.index') }}" class="btn btn-outline btn-sm" style="font-size: 0.8rem; padding: 0.35rem 0.65rem;">
                    Lihat Semua &rarr;
                </a>
            </div>

            <div class="data-card-body" style="flex: 1; display: flex; flex-direction: column; gap: 0.85rem;">
                {{-- 1. Pengumuman & Informasi Resmi Desa (DI ATAS) --}}
                @forelse($announcements as $ann)
                    <div class="ann-item">
                        <div class="ann-date">
                            {{ $ann->published_at ? $ann->published_at->format('d M Y') : $ann->created_at->format('d M Y') }}
                        </div>
                        <div class="ann-title">
                            {{ $ann->title }}
                        </div>
                        <div class="ann-excerpt">
                            {{ Str::limit($ann->content, 130) }}
                        </div>
                        <div style="margin-top: 0.5rem;">
                            <a href="{{ route('announcements.show', $ann->id) }}" style="font-size: 0.78rem; font-weight: 700; color: var(--accent-1); text-decoration: none;">
                                Baca Selengkapnya &rarr;
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="empty-state" style="padding: 1.75rem 1rem; text-align: center;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width: 38px; height: 38px; margin: 0 auto 0.5rem; color: var(--text-muted);"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                        <div style="font-weight: 700; color: var(--text-primary); margin-bottom: 0.25rem;">Belum Ada Pengumuman</div>
                        <div style="font-size: 0.82rem; color: var(--text-secondary);">Warta resmi dan agenda kegiatan warga akan ditampilkan di sini.</div>
                    </div>
                @endforelse

                {{-- 2. Hasil Musyawarah Selesai / Keputusan Warga (DI BAWAH) --}}
                @if(isset($completedPolls) && $completedPolls->count() > 0)
                    <div style="border-top: 1px dashed var(--border-glass); padding-top: 0.85rem; margin-top: 0.25rem;">
                        <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--accent-1); margin-bottom: 0.6rem; display: flex; align-items: center; gap: 0.35rem;">
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                            Keputusan & Hasil Musyawarah Desa
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 0.65rem;">
                            @foreach($completedPolls as $cPoll)
                                @php
                                    $cWinning = $cPoll->winning_option;
                                    $cTotal = $cPoll->votes->count();
                                    $cWinVotes = $cWinning ? $cWinning->votes->count() : 0;
                                    $cPct = $cTotal > 0 ? round(($cWinVotes / $cTotal) * 100) : 0;
                                @endphp
                                <div style="background: rgba(16, 185, 129, 0.06); border: 1px solid rgba(16, 185, 129, 0.2); border-radius: 10px; padding: 0.75rem 0.85rem;">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem; margin-bottom: 0.25rem;">
                                        <h5 style="font-size: 0.88rem; font-weight: 700; color: var(--text-primary); margin: 0; line-height: 1.35;">
                                            <a href="{{ route('polls.show', $cPoll->id) }}" style="color: inherit; text-decoration: none;">
                                                {{ $cPoll->title }}
                                            </a>
                                        </h5>
                                        <span class="badge badge-green" style="font-size: 0.65rem; padding: 2px 6px; flex-shrink: 0;">Selesai</span>
                                    </div>
                                    @if($cWinning)
                                        <div style="font-size: 0.78rem; color: var(--text-secondary); margin-top: 0.3rem;">
                                            🏆 <strong>Keputusan Terpilih:</strong> <span style="color: #10b981; font-weight: 600;">{{ $cWinning->text }}</span>
                                            <span style="color: var(--text-muted);">({{ $cWinVotes }} suara / {{ $cPct }}%)</span>
                                        </div>
                                    @endif
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.4rem; font-size: 0.73rem; color: var(--text-muted);">
                                        <span>Partisipasi: {{ $cTotal }} warga</span>
                                        <a href="{{ route('polls.show', $cPoll->id) }}" style="color: var(--accent-1); font-weight: 600; text-decoration: none;">Lihat Rincian &rarr;</a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

    </div>

    {{-- MODAL POPUP 1: BUAT PENGAJUAN SURAT BARU --}}
    <div id="modalCreateDocument" class="siwil-modal-backdrop">
        <div class="siwil-modal-dialog">
            <div class="siwil-modal-header">
                <h3 class="siwil-modal-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: #2563eb;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/></svg>
                    Buat Pengajuan Surat Mandiri
                </h3>
                <button type="button" class="siwil-modal-close" onclick="closeModal('modalCreateDocument')">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="siwil-modal-body">
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label class="form-label" style="font-weight: 700; font-size: 0.88rem; margin-bottom: 0.35rem; display: block;">Jenis Surat <span style="color: var(--danger);">*</span></label>
                        <select name="type" class="form-select" required style="width: 100%;">
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

                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label class="form-label" style="font-weight: 700; font-size: 0.88rem; margin-bottom: 0.35rem; display: block;">Keperluan / Keterangan Tambahan</label>
                        <textarea name="notes" class="form-textarea" placeholder="Tuliskan alasan/keperluan pengajuan (contoh: Untuk melamar pekerjaan)" rows="3" style="width: 100%;"></textarea>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 700; font-size: 0.88rem; margin-bottom: 0.35rem; display: block;">Dokumen Persyaratan (Opsional)</label>
                        <input type="file" name="attachment" class="form-input" accept=".pdf,image/*" style="width: 100%; padding: 0.45rem;">
                        <small style="color: var(--text-muted); display: block; margin-top: 0.3rem;">Format: PDF, JPG, PNG (Maksimal 5MB).</small>
                    </div>
                </div>
                <div class="siwil-modal-footer">
                    <button type="button" class="btn btn-outline btn-sm" onclick="closeModal('modalCreateDocument')">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">Kirim Pengajuan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL POPUP 2: BUAT PENGADUAN WARGA --}}
    <div id="modalCreateComplaint" class="siwil-modal-backdrop">
        <div class="siwil-modal-dialog">
            <div class="siwil-modal-header">
                <h3 class="siwil-modal-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: #ef4444;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    Buat Laporan Pengaduan Warga
                </h3>
                <button type="button" class="siwil-modal-close" onclick="closeModal('modalCreateComplaint')">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <form action="{{ route('complaints.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="siwil-modal-body">
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label class="form-label" style="font-weight: 700; font-size: 0.88rem; margin-bottom: 0.35rem; display: block;">Judul Pengaduan <span style="color: var(--danger);">*</span></label>
                        <input type="text" name="title" class="form-input" required placeholder="Misal: Jalan Berlubang di RT 02" style="width: 100%;">
                    </div>

                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label class="form-label" style="font-weight: 700; font-size: 0.88rem; margin-bottom: 0.35rem; display: block;">Deskripsi Lengkap <span style="color: var(--danger);">*</span></label>
                        <textarea name="description" class="form-textarea" required placeholder="Jelaskan detail permasalahan, kondisi terkini, dan harapan penanganan..." rows="3" style="width: 100%;"></textarea>
                    </div>

                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label class="form-label" style="font-weight: 700; font-size: 0.88rem; margin-bottom: 0.35rem; display: block;">Lokasi Kejadian (Geotag / Alamat)</label>
                        <div style="display: flex; gap: 0.5rem;">
                            <input type="text" id="modal-location" name="location" class="form-input" placeholder="Tulis alamat atau klik tombol GPS" style="flex: 1;">
                            <button type="button" onclick="getLocationInModal()" class="btn btn-outline btn-sm" style="white-space: nowrap;">📍 Ambil GPS</button>
                        </div>
                        <small id="modal-location-status" style="display: block; margin-top: 0.3rem; font-size: 0.78rem;"></small>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-weight: 700; font-size: 0.88rem; margin-bottom: 0.35rem; display: block;">Unggah Foto Bukti (Opsional)</label>
                        <input type="file" name="photo" class="form-input" accept="image/*" style="width: 100%; padding: 0.45rem;">
                        <small style="color: var(--text-muted); display: block; margin-top: 0.3rem;">Format: JPG, PNG (Maksimal 2MB).</small>
                    </div>
                </div>
                <div class="siwil-modal-footer">
                    <button type="button" class="btn btn-outline btn-sm" onclick="closeModal('modalCreateComplaint')">Batal</button>
                    <button type="submit" class="btn btn-danger btn-sm">Kirim Laporan</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    // Close modal on click backdrop
    document.querySelectorAll('.siwil-modal-backdrop').forEach(backdrop => {
        backdrop.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal(this.id);
            }
        });
    });

    // Close on ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.siwil-modal-backdrop.active').forEach(modal => {
                closeModal(modal.id);
            });
        }
    });

    // GPS Geolocation for complaints modal
    function getLocationInModal() {
        const status = document.getElementById('modal-location-status');
        const locInput = document.getElementById('modal-location');

        if (!navigator.geolocation) {
            status.textContent = 'Geolocation tidak didukung oleh browser Anda.';
            status.style.color = 'var(--danger)';
            return;
        }

        status.textContent = 'Mendeteksi koordinat GPS...';
        status.style.color = 'var(--text-secondary)';

        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                locInput.value = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                status.textContent = '✅ Koordinat GPS berhasil disematkan!';
                status.style.color = 'var(--success)';
            },
            function(error) {
                status.textContent = 'Gagal mengambil lokasi: ' + error.message;
                status.style.color = 'var(--danger)';
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    }
</script>
@endsection
