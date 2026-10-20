@extends('layouts.app')

@section('title', 'Dashboard ' . Auth::user()->role . ' - SIWIL AI')
@section('page-title', 'Dashboard ' . Auth::user()->role)

@section('content')
    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px; color: var(--success);"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: inherit;">&times;</button>
        </div>
    @endif

    @if(isset($activeEmergencies) && $activeEmergencies->count() > 0)
        <div style="background: rgba(239, 68, 68, 0.1); border: 2px solid var(--danger); padding: 1.25rem 1.5rem; border-radius: 12px; margin-bottom: 1.75rem;">
            <h3 style="color: var(--danger); margin: 0 0 0.75rem 0; font-size: 1.15rem; display: flex; align-items: center; gap: 0.5rem;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:22px;height:22px;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                PANGGILAN DARURAT (SOS) AKTIF DI WILAYAH ANDA!
            </h3>
            <div style="display: grid; gap: 0.75rem;">
                @foreach($activeEmergencies as $em)
                <div style="background: var(--bg-card); border: 1px solid rgba(239, 68, 68, 0.25); padding: 0.85rem 1rem; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                    <div>
                        <strong style="color: var(--text-primary);">{{ $em->user->name }}</strong> melaporkan keadaan darurat (<span style="color: var(--danger); font-weight: 600;">{{ $em->type }}</span>).<br>
                        <span style="font-size: 0.8rem; color: var(--text-muted);">
                            Waktu: {{ $em->created_at->format('d M Y, H:i') }} | 
                            @if($em->location_lat && $em->location_lng)
                                <a href="https://www.google.com/maps?q={{ $em->location_lat }},{{ $em->location_lng }}" target="_blank" style="color: var(--accent-1); text-decoration: underline;">Buka Peta Lokasi</a>
                            @else
                                Lokasi tidak terlampir
                            @endif
                        </span>
                    </div>
                    <form action="{{ route('emergencies.resolve', $em->id) }}" method="POST" style="margin: 0;">
                        @csrf @method('PATCH')
                        <button class="btn btn-sm btn-primary" style="background: var(--danger); border-color: var(--danger);">Tandai Selesai</button>
                    </form>
                </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Header & Quick Actions Toolbar --}}
    <div style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.08) 0%, rgba(168, 85, 247, 0.08) 100%); border: 1px solid var(--border-glass); border-radius: 14px; padding: 1.5rem; margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.25rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.65rem; margin-bottom: 0.35rem;">
                <span class="badge badge-blue" style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                    Panel Ketua {{ Auth::user()->role }}
                </span>
                <span style="font-size: 0.85rem; color: var(--text-muted);">
                    Wilayah: <strong>{{ Auth::user()->role === 'RT' ? 'RT ' . (Auth::user()->rt->number ?? Auth::user()->resident->rt->number ?? '-') : 'RW ' . (Auth::user()->rw->number ?? Auth::user()->resident->rt->rw->number ?? '-') }}</strong>
                </span>
            </div>
            <h1 style="font-size: 1.6rem; margin: 0 0 0.25rem 0; color: var(--text-primary); font-weight: 800;">
                Selamat Datang, {{ Auth::user()->name }}
            </h1>
            <p style="margin: 0; font-size: 0.875rem; color: var(--text-secondary);">
                Pusat kendali pelayanan warga: verifikasi pengajuan surat, tindak lanjut pengaduan, dan musyawarah digital warga.
            </p>
        </div>

        <div class="actions-bar" style="display: flex; gap: 0.65rem; flex-wrap: wrap; align-items: center;">
            <button type="button" onclick="openModal('modalCreatePoll')" class="btn btn-primary btn-sm">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                <span>+ Buat Polling / Musyawarah</span>
            </button>
            <a href="{{ route('documents.index') }}" class="btn btn-outline btn-sm">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><polyline points="14 2 14 8 20 8"/></svg>
                <span>Layanan Surat</span>
                @if(($stats['pending_documents'] ?? 0) > 0)
                    <span class="badge badge-yellow">{{ $stats['pending_documents'] }}</span>
                @endif
            </a>
            <a href="{{ route('complaints.index') }}" class="btn btn-outline btn-sm">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                <span>Pengaduan</span>
                @if(($stats['pending_complaints'] ?? 0) > 0)
                    <span class="badge badge-red">{{ $stats['pending_complaints'] }}</span>
                @endif
            </a>
        </div>
    </div>

    {{-- Stats Grid (4 Primary Cards) --}}
    <div class="stats-grid" style="margin-bottom: 2rem;">
        {{-- Card 1: Kependudukan --}}
        <a href="{{ route('areas.index', ['tab' => 'penduduk']) }}" class="stat-card" style="text-decoration: none; color: inherit;">
            <div class="stat-header">
                <span class="stat-title">Warga Terdaftar</span>
                <span class="stat-icon" style="color: var(--accent-1);">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </span>
            </div>
            <div class="stat-value">{{ $stats['residents'] ?? 0 }} <span style="font-size: 0.95rem; color: var(--text-muted); font-weight: 500;">Jiwa</span></div>
            <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.45rem; display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                <span><strong>{{ $stats['male'] ?? 0 }}</strong> L</span>
                <span>•</span>
                <span><strong>{{ $stats['female'] ?? 0 }}</strong> P</span>
                <span>•</span>
                <span style="color: var(--accent-1); font-weight: 600;">{{ $stats['families'] ?? 0 }} KK</span>
            </div>
        </a>

        {{-- Card 2: Pengajuan Surat --}}
        <a href="{{ route('documents.index') }}" class="stat-card" style="text-decoration: none; color: inherit;">
            <div class="stat-header">
                <span class="stat-title">Layanan Surat</span>
                <span class="stat-icon" style="color: #3b82f6;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                </span>
            </div>
            <div class="stat-value" style="display: flex; align-items: baseline; gap: 0.5rem;">
                {{ $stats['documents'] ?? 0 }} <span style="font-size: 0.95rem; color: var(--text-muted); font-weight: 500;">Surat</span>
            </div>
            <div style="font-size: 0.8rem; margin-top: 0.45rem;">
                @if(($stats['pending_documents'] ?? 0) > 0)
                    <span class="badge badge-yellow" style="font-size: 0.75rem; font-weight: 600;">{{ $stats['pending_documents'] }} Menunggu Persetujuan</span>
                @else
                    <span class="badge badge-green" style="font-size: 0.75rem;">Semua Terproses</span>
                @endif
            </div>
        </a>

        {{-- Card 3: Pengaduan Warga --}}
        <a href="{{ route('complaints.index') }}" class="stat-card" style="text-decoration: none; color: inherit;">
            <div class="stat-header">
                <span class="stat-title">Pengaduan Warga</span>
                <span class="stat-icon" style="color: #f59e0b;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                </span>
            </div>
            <div class="stat-value" style="display: flex; align-items: baseline; gap: 0.5rem;">
                {{ $stats['complaints'] ?? 0 }} <span style="font-size: 0.95rem; color: var(--text-muted); font-weight: 500;">Aduan</span>
            </div>
            <div style="font-size: 0.8rem; margin-top: 0.45rem;">
                @if(($stats['pending_complaints'] ?? 0) > 0)
                    <span class="badge badge-red" style="font-size: 0.75rem; font-weight: 600;">{{ $stats['pending_complaints'] }} Butuh Tindak Lanjut</span>
                @else
                    <span class="badge badge-green" style="font-size: 0.75rem;">Kondisi Terkendali</span>
                @endif
            </div>
        </a>

        {{-- Card 4: Musyawarah / Polling --}}
        <a href="{{ route('polls.index') }}" class="stat-card" style="text-decoration: none; color: inherit;">
            <div class="stat-header">
                <span class="stat-title">Musyawarah / Polling</span>
                <span class="stat-icon" style="color: #10b981;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13h-.68l-2 2h1.91L19 17H5l1.78-2h2.05l-2-2H6l-3 3v2c0 1.1.89 2 1.99 2H19c1.1 0 2-.9 2-2v-2l-3-3zm-1-5.05l-4.95 4.95-3.54-3.54 4.95-4.95L16.99 7.95z"/></svg>
                </span>
            </div>
            <div class="stat-value">{{ $stats['active_polls'] ?? 0 }} <span style="font-size: 0.95rem; color: var(--text-muted); font-weight: 500;">Aktif</span></div>
            <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.45rem; display: flex; align-items: center; gap: 0.25rem;">
                <span style="color: var(--accent-1); font-weight: 600;">+ Buat Kesepakatan Warga</span>
            </div>
        </a>
    </div>

    {{-- Combined Two-Column Service Processing Hub --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(460px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        
        {{-- Box Kiri: Pengajuan Surat Warga --}}
        <div class="data-card" style="display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div class="data-card-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-glass); padding-bottom: 0.85rem;">
                    <div>
                        <span class="data-card-title" style="display: flex; align-items: center; gap: 0.5rem;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px; color: var(--accent-1);"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/></svg>
                            Layanan Surat Masuk
                        </span>
                        <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.2rem;">
                            Verifikasi dan setujui permohonan surat dari warga
                        </div>
                    </div>
                    <a href="{{ route('documents.index') }}" class="btn btn-outline btn-sm" style="font-size: 0.78rem; padding: 0.35rem 0.65rem;">
                        Semua Surat
                    </a>
                </div>

                @if(isset($recentDocuments) && $recentDocuments->count() > 0)
                    <div class="table-responsive">
                        <table style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>Pemohon & No. Surat</th>
                                    <th>Jenis Surat</th>
                                    <th>Status</th>
                                    <th style="text-align: right;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentDocuments as $doc)
                                @php
                                    $finalLevel = $doc->final_approval_level;
                                    $nextStatus = $doc->next_action_status;
                                    $canApprove = $doc->canBeApprovedBy(Auth::user());
                                    $nextLabel = $doc->next_action_label;
                                    $docData = [
                                        'id' => $doc->id,
                                        'request_number' => $doc->request_number,
                                        'type' => ucwords(str_replace('_', ' ', strtolower($doc->type))),
                                        'applicant' => $doc->resident->full_name ?? 'Warga',
                                        'nik' => $doc->resident->nik ?? '-',
                                        'area' => 'RT ' . $doc->rt_number . ' / RW ' . $doc->rw_number . ', Desa ' . $doc->village_name,
                                        'notes' => $doc->notes ?? '-',
                                        'attachment_url' => $doc->attachment_path ? asset('storage/' . $doc->attachment_path) : null,
                                        'status' => $doc->status,
                                        'status_badge' => in_array($doc->status, ['DISETUJUI', 'SELESAI']) ? 'badge-green' : ($doc->status === 'DITOLAK' ? 'badge-red' : 'badge-yellow'),
                                        'created_at' => $doc->created_at->format('d M Y, H:i'),
                                        'final_level' => $finalLevel,
                                        'next_status' => $nextStatus,
                                        'next_label' => $nextLabel,
                                        'can_approve' => $canApprove,
                                        'is_approved' => in_array($doc->status, ['DISETUJUI', 'SELESAI']),
                                        'print_url' => route('documents.print', $doc->id),
                                        'show_url' => route('documents.show', $doc->id),
                                        'update_url' => route('documents.update', $doc->id),
                                    ];
                                @endphp
                                <tr>
                                    <td>
                                        <div style="font-weight: 600; color: var(--text-primary);">
                                            {{ $doc->resident->full_name ?? 'Warga' }}
                                        </div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted); font-family: monospace;">
                                            {{ $doc->request_number }}
                                        </div>
                                    </td>
                                    <td>
                                        <span style="font-size: 0.85rem; color: var(--text-secondary);">
                                            {{ ucwords(str_replace('_', ' ', strtolower($doc->type))) }}
                                        </span>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);">
                                            {{ $doc->created_at->format('d M Y') }}
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $badgeColor = match($doc->status) {
                                                'DISETUJUI', 'SELESAI' => 'badge-green',
                                                'DITOLAK' => 'badge-red',
                                                'DIAJUKAN_RT' => 'badge-yellow',
                                                'DIAJUKAN_RW' => 'badge-blue',
                                                default => 'badge-gray'
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeColor }}" style="font-size: 0.72rem;">
                                            {{ str_replace('_', ' ', $doc->status) }}
                                        </span>
                                    </td>
                                    <td style="text-align: right; white-space: nowrap;">
                                        <div style="display: inline-flex; gap: 0.35rem; align-items: center; justify-content: flex-end;">
                                            <button type="button" class="btn-icon btn-icon-view" onclick='openDocModal(@json($docData))' title="Proses / Lihat Detail Surat" aria-label="Detail Surat">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"></path>
                                                    <circle cx="12" cy="12" r="3"></circle>
                                                </svg>
                                            </button>
                                            @if(in_array($doc->status, ['DISETUJUI', 'SELESAI']))
                                                <a href="{{ route('documents.print', $doc->id) }}" target="_blank" class="btn-icon btn-icon-success" title="Cetak Surat Resmi" aria-label="Cetak Surat">
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
                    <div style="padding: 3rem 1.5rem; text-align: center; color: var(--text-muted);">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width: 42px; height: 42px; margin: 0 auto 0.75rem; opacity: 0.4;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><polyline points="14 2 14 8 20 8"/></svg>
                        <div style="font-weight: 600; color: var(--text-primary); margin-bottom: 0.25rem;">Belum Ada Pengajuan Surat</div>
                        <p style="font-size: 0.85rem; margin: 0;">Pengajuan surat baru dari warga akan muncul di sini untuk verifikasi Anda.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Box Kanan: Pengaduan Warga --}}
        <div class="data-card" style="display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div class="data-card-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-glass); padding-bottom: 0.85rem;">
                    <div>
                        <span class="data-card-title" style="display: flex; align-items: center; gap: 0.5rem;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px; color: #f59e0b;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                            Pengaduan & Aspirasi Warga
                        </span>
                        <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.2rem;">
                            Keluhan dan laporan masalah dari warga sekitar
                        </div>
                    </div>
                    <a href="{{ route('complaints.index') }}" class="btn btn-outline btn-sm" style="font-size: 0.78rem; padding: 0.35rem 0.65rem;">
                        Semua Pengaduan
                    </a>
                </div>

                @if(isset($recentComplaints) && $recentComplaints->count() > 0)
                    <div class="table-responsive">
                        <table style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>Pelapor & Judul</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                    <th style="text-align: right;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentComplaints as $complaint)
                                @php
                                    $cData = [
                                        'id' => $complaint->id,
                                        'title' => $complaint->title,
                                        'reporter' => $complaint->user->name ?? 'Anonim',
                                        'status' => $complaint->status,
                                        'status_badge' => match($complaint->status) {
                                            'PROCESSING' => 'badge-blue',
                                            'RESOLVED' => 'badge-green',
                                            'REJECTED' => 'badge-red',
                                            default => 'badge-yellow',
                                        },
                                        'created_at' => $complaint->created_at->format('d M Y, H:i'),
                                        'description' => $complaint->description,
                                        'location' => $complaint->location ?? '-',
                                        'maps_url' => ($complaint->location && strpos($complaint->location, ',') !== false) 
                                            ? 'https://www.google.com/maps/search/?api=1&query=' . urlencode(str_replace(' ', '', $complaint->location)) 
                                            : null,
                                        'photo_url' => $complaint->photo_path ? asset('storage/' . $complaint->photo_path) : null,
                                        'can_manage' => Auth::user()->role !== 'WARGA',
                                        'show_url' => route('complaints.show', $complaint->id),
                                        'update_url' => route('complaints.update-status', $complaint->id),
                                    ];
                                @endphp
                                <tr>
                                    <td>
                                        <div style="font-weight: 600; color: var(--text-primary);">
                                            {{ $complaint->title }}
                                        </div>
                                        <div style="font-size: 0.78rem; color: var(--text-muted);">
                                            Oleh: {{ $complaint->user->name ?? 'Anonim' }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge 
                                            @if($complaint->status == 'PENDING') badge-yellow 
                                            @elseif($complaint->status == 'PROCESSING') badge-blue 
                                            @elseif($complaint->status == 'RESOLVED') badge-green 
                                            @else badge-red @endif" style="font-size: 0.72rem;">
                                            {{ $complaint->status }}
                                        </span>
                                    </td>
                                    <td style="font-size: 0.8rem; color: var(--text-secondary); white-space: nowrap;">
                                        {{ $complaint->created_at->diffForHumans() }}
                                    </td>
                                    <td style="text-align: right; white-space: nowrap;">
                                        <div style="display: inline-flex; gap: 0.35rem; align-items: center; justify-content: flex-end;">
                                            <button type="button" class="btn-icon btn-icon-view" onclick='openComplaintModal(@json($cData))' title="Lihat & Tindak Lanjuti Pengaduan" aria-label="Detail Pengaduan">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"></path>
                                                    <circle cx="12" cy="12" r="3"></circle>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div style="padding: 3rem 1.5rem; text-align: center; color: var(--text-muted);">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width: 42px; height: 42px; margin: 0 auto 0.75rem; opacity: 0.4;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <div style="font-weight: 600; color: var(--text-primary); margin-bottom: 0.25rem;">Tidak Ada Pengaduan Aktif</div>
                        <p style="font-size: 0.85rem; margin: 0;">Lingkungan aman & kondusif. Belum ada keluhan warga yang perlu ditindaklanjuti.</p>
                    </div>
                @endif
            </div>
        </div>

    </div>

    {{-- Musyawarah & Polling Section --}}
    <div class="data-card" style="margin-bottom: 2rem;">
        <div class="data-card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
            <div>
                <span class="data-card-title" style="display: flex; align-items: center; gap: 0.5rem;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px; color: #10b981;"><path d="M18 13h-.68l-2 2h1.91L19 17H5l1.78-2h2.05l-2-2H6l-3 3v2c0 1.1.89 2 1.99 2H19c1.1 0 2-.9 2-2v-2l-3-3zm-1-5.05l-4.95 4.95-3.54-3.54 4.95-4.95L16.99 7.95z"/></svg>
                    Musyawarah & Polling Warga
                </span>
                <p style="margin: 0.2rem 0 0 0; font-size: 0.8rem; color: var(--text-muted);">
                    Pengambilan keputusan partisipatif: buat musyawarah atau jajak pendapat untuk warga di wilayah Anda.
                </p>
            </div>
            <div style="display: flex; gap: 0.5rem; align-items: center;">
                <button type="button" onclick="openModal('modalCreatePoll')" class="btn btn-primary btn-sm" style="display: inline-flex; align-items: center; gap: 0.4rem;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    <span>+ Buat Polling Baru</span>
                </button>
                <a href="{{ route('polls.index') }}" class="btn btn-outline btn-sm">
                    Lihat Semua
                </a>
            </div>
        </div>

        <div style="padding: 1.25rem;">
            @if(isset($polls) && $polls->count() > 0)
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1rem;">
                    @foreach($polls as $poll)
                        @php
                            $isExpired = $poll->expires_at && $poll->expires_at->isPast();
                            $statusLabel = ($poll->status === 'ACTIVE' && !$isExpired) ? 'AKTIF' : 'SELESAI';
                        @endphp
                        <div style="background: var(--bg-primary); border: 1px solid var(--border-glass); border-radius: 10px; padding: 1.25rem; display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.65rem; gap: 0.5rem;">
                                    <span class="badge {{ $statusLabel === 'AKTIF' ? 'badge-green' : 'badge-gray' }}" style="font-size: 0.72rem;">
                                        {{ $statusLabel }}
                                    </span>
                                    <span style="font-size: 0.75rem; color: var(--text-muted);">
                                        {{ $poll->expires_at ? 'Hingga ' . $poll->expires_at->format('d M Y') : 'Tanpa Batas Waktu' }}
                                    </span>
                                </div>
                                <h4 style="margin: 0 0 0.5rem 0; font-size: 1.05rem; color: var(--text-primary); font-weight: 700; line-height: 1.3;">
                                    {{ $poll->title }}
                                </h4>
                                @if($poll->description)
                                    <p style="margin: 0 0 0.85rem 0; font-size: 0.85rem; color: var(--text-secondary); line-height: 1.4;">
                                        {{ Str::limit($poll->description, 95) }}
                                    </p>
                                @endif

                                @if(($poll->status === 'CLOSED' || $isExpired) && $poll->winning_option)
                                    @php
                                        $rtWin = $poll->winning_option;
                                        $rtTotal = $poll->votes ? $poll->votes->count() : $poll->options->sum(fn($o) => $o->votes ? $o->votes->count() : 0);
                                        $rtWinVotes = $rtWin->votes ? $rtWin->votes->count() : 0;
                                        $rtPct = $rtTotal > 0 ? round(($rtWinVotes / $rtTotal) * 100) : 0;
                                    @endphp
                                    <div style="background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.25); border-radius: 6px; padding: 0.5rem 0.65rem; font-size: 0.8rem; margin-bottom: 0.85rem;">
                                        🏆 <strong>Hasil:</strong> <span style="color: #10b981; font-weight: 600;">{{ $rtWin->text }}</span>
                                        <span style="color: var(--text-muted); font-size: 0.75rem;">({{ $rtWinVotes }} suara / {{ $rtPct }}%)</span>
                                    </div>
                                @endif

                                <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                                    <span>{{ $poll->options->count() }} Pilihan</span>
                                    <span>•</span>
                                    <span>Dibuat oleh {{ $poll->creator->name ?? 'Pengurus' }}</span>
                                </div>
                            </div>

                            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-glass); padding-top: 0.85rem;">
                                <span style="font-size: 0.8rem; color: var(--accent-1); font-weight: 600;">
                                    {{ $poll->options->sum(fn($o) => $o->votes ? $o->votes->count() : 0) }} Suara Masuk
                                </span>
                                <div style="display: inline-flex; gap: 0.35rem;">
                                    <a href="{{ route('polls.show', $poll->id) }}" class="btn-icon btn-icon-view" title="Buka Detail & Hasil Polling" aria-label="Detail Polling">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div style="padding: 3rem 1.5rem; text-align: center; color: var(--text-muted);">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width: 48px; height: 48px; margin: 0 auto 0.75rem; opacity: 0.4;"><path d="M18 13h-.68l-2 2h1.91L19 17H5l1.78-2h2.05l-2-2H6l-3 3v2c0 1.1.89 2 1.99 2H19c1.1 0 2-.9 2-2v-2l-3-3zm-1-5.05l-4.95 4.95-3.54-3.54 4.95-4.95L16.99 7.95z"/></svg>
                    <div style="font-weight: 600; color: var(--text-primary); margin-bottom: 0.35rem;">Belum Ada Musyawarah / Polling</div>
                    <p style="font-size: 0.85rem; margin-bottom: 1rem;">Buat jajak pendapat untuk melibatkan warga dalam keputusan lingkungan RT/RW.</p>
                    <button type="button" onclick="openModal('modalCreatePoll')" class="btn btn-primary btn-sm">
                        + Buat Polling Pertama
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- MODAL BUAT POLLING / MUSYAWARAH BARU --}}
    <div id="modalCreatePoll" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;" onclick="if(event.target === this) closeModal('modalCreatePoll')">
        <div class="modal-container" style="background: var(--bg-card); border: 1px solid var(--border-glass); border-radius: 14px; max-width: 540px; width: 100%; box-shadow: 0 20px 40px rgba(0,0,0,0.3); overflow: hidden; animation: popIn 0.25s ease-out;">
            <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-glass); display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 0.65rem;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(16, 185, 129, 0.15); display: flex; align-items: center; justify-content: center; color: #10b981;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;"><path d="M18 13h-.68l-2 2h1.91L19 17H5l1.78-2h2.05l-2-2H6l-3 3v2c0 1.1.89 2 1.99 2H19c1.1 0 2-.9 2-2v-2l-3-3zm-1-5.05l-4.95 4.95-3.54-3.54 4.95-4.95L16.99 7.95z"/></svg>
                    </div>
                    <h3 style="margin: 0; font-size: 1.15rem; color: var(--text-primary); font-weight: 700;">Buat Musyawarah / Polling Warga</h3>
                </div>
                <button type="button" onclick="closeModal('modalCreatePoll')" style="background: none; border: none; font-size: 1.5rem; color: var(--text-muted); cursor: pointer; line-height: 1;">&times;</button>
            </div>

            <form action="{{ route('polls.store') }}" method="POST" style="padding: 1.5rem;">
                @csrf
                <div style="margin-bottom: 1.15rem;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.85rem; color: var(--text-primary); margin-bottom: 0.4rem; display: block;">
                        Judul Musyawarah / Polling <span style="color: var(--danger);">*</span>
                    </label>
                    <input type="text" name="title" class="form-control" required placeholder="Contoh: Kesepakatan Jadwal Kerja Bakti RT 01" style="width: 100%;">
                </div>

                <div style="margin-bottom: 1.15rem;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.85rem; color: var(--text-primary); margin-bottom: 0.4rem; display: block;">
                        Deskripsi / Latar Belakang (Opsional)
                    </label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Jelaskan pokok pembahasan musyawarah yang akan diputuskan..." style="width: 100%; resize: vertical;"></textarea>
                </div>

                <div style="margin-bottom: 1.15rem;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.85rem; color: var(--text-primary); margin-bottom: 0.4rem; display: block;">
                        Batas Waktu Pengisian (Opsional)
                    </label>
                    <input type="date" name="expires_at" class="form-control" min="{{ date('Y-m-d', strtotime('+1 day')) }}" style="width: 100%;">
                    <small style="color: var(--text-muted); font-size: 0.75rem;">Kosongkan jika musyawarah berlaku tanpa batas waktu.</small>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <label class="form-label" style="font-weight: 600; font-size: 0.85rem; color: var(--text-primary); margin-bottom: 0;">
                            Pilihan Jawaban (Minimal 2) <span style="color: var(--danger);">*</span>
                        </label>
                        <button type="button" onclick="addPollOption()" class="btn btn-outline btn-sm" style="font-size: 0.75rem; padding: 0.2rem 0.5rem;">
                            + Tambah Opsi
                        </button>
                    </div>

                    <div id="pollOptionsContainer" style="display: grid; gap: 0.5rem;">
                        <div class="poll-option-row" style="display: flex; gap: 0.5rem; align-items: center;">
                            <input type="text" name="options[]" class="form-control" value="Setuju" required placeholder="Opsi 1" style="flex: 1;">
                        </div>
                        <div class="poll-option-row" style="display: flex; gap: 0.5rem; align-items: center;">
                            <input type="text" name="options[]" class="form-control" value="Tidak Setuju" required placeholder="Opsi 2" style="flex: 1;">
                        </div>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 0.75rem; border-top: 1px solid var(--border-glass); padding-top: 1.25rem;">
                    <button type="button" class="btn btn-outline btn-sm" onclick="closeModal('modalCreatePoll')">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm" style="display: inline-flex; align-items: center; gap: 0.4rem;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                        <span>Publikasikan Polling</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Popup Detail & Proses Pengajuan Surat --}}
    <div id="docModalOverlay" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 9999; justify-content: center; align-items: center; padding: 1rem; animation: fadeIn 0.2s ease;">
        <div style="background: var(--bg-card); border: 1px solid var(--border-glass); border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); width: 100%; max-width: 620px; max-height: 90vh; overflow-y: auto; display: flex; flex-direction: column;">
            <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-glass); display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; background: var(--bg-card); z-index: 2;">
                <div>
                    <h3 id="modalDocNumber" style="font-size: 1.15rem; font-weight: 700; color: var(--text-primary); margin: 0;">Detail Surat</h3>
                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.15rem;" id="modalDocType">Jenis Dokumen</div>
                </div>
                <button type="button" onclick="closeDocModal()" style="background: transparent; border: none; font-size: 1.5rem; cursor: pointer; color: var(--text-muted); line-height: 1; padding: 4px;">&times;</button>
            </div>

            <div style="padding: 1.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; padding: 0.75rem 1rem; border-radius: 10px; background: rgba(37, 99, 235, 0.05); border: 1px solid var(--border-glass);">
                    <div style="font-size: 0.85rem; color: var(--text-secondary);">
                        Status Saat Ini: <span id="modalDocStatusBadge" class="badge">PENDING</span>
                    </div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);" id="modalDocDate">-</div>
                </div>

                <div style="margin-bottom: 1.25rem; padding: 0.85rem 1rem; border-radius: 10px; background: var(--bg-body); border: 1px dashed var(--border-glass);">
                    <div style="font-size: 0.78rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">
                        Tingkat Kebutuhan Verifikasi
                    </div>
                    <div id="modalDocVerificationLevel" style="font-size: 0.88rem; font-weight: 600; color: var(--text-primary);">
                        -
                    </div>
                </div>

                <table style="width: 100%; border: none; font-size: 0.9rem; margin-bottom: 1.25rem;">
                    <tr style="border-bottom: 1px solid var(--border-glass);">
                        <td style="width: 32%; color: var(--text-secondary); padding: 8px 0;">Pemohon</td>
                        <td style="font-weight: 600; padding: 8px 0;" id="modalDocApplicant">-</td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--border-glass);">
                        <td style="color: var(--text-secondary); padding: 8px 0;">NIK</td>
                        <td style="font-weight: 600; padding: 8px 0; font-family: monospace;" id="modalDocNik">-</td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--border-glass);">
                        <td style="color: var(--text-secondary); padding: 8px 0;">Wilayah</td>
                        <td style="font-weight: 600; padding: 8px 0;" id="modalDocArea">-</td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--border-glass);">
                        <td style="color: var(--text-secondary); padding: 8px 0;">Keperluan</td>
                        <td style="padding: 8px 0;" id="modalDocNotes">-</td>
                    </tr>
                    <tr id="modalDocAttachmentRow" style="display: none;">
                        <td style="color: var(--text-secondary); padding: 8px 0;">Berkas Lampiran</td>
                        <td style="padding: 8px 0;">
                            <a id="modalDocAttachmentLink" href="#" target="_blank" class="btn btn-outline btn-sm" style="font-size: 0.8rem; padding: 0.35rem 0.75rem;">Buka Berkas &rarr;</a>
                        </td>
                    </tr>
                </table>

                <div id="modalDocActionBox" style="padding-top: 1rem; border-top: 1px solid var(--border-glass);">
                    <div id="modalDocActionButtons" style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center;">
                        <form id="modalDocApproveForm" action="" method="POST" style="margin: 0;">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" id="modalDocApproveStatus" value="">
                            <button type="button" onclick="confirmApproveDoc(event)" class="btn btn-primary" id="modalDocApproveBtn" style="display: inline-flex; align-items: center; gap: 0.5rem; font-weight: 600; cursor: pointer;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;"><polyline points="20 6 9 17 4 12"/></svg>
                                <span id="modalDocApproveBtnText">Setujui Surat</span>
                            </button>
                        </form>

                        <button type="button" class="btn btn-danger" onclick="toggleDocRejectForm(true)">
                            Tolak Pengajuan
                        </button>

                        <a id="modalDocPrintBtn" href="#" target="_blank" class="btn btn-success" style="display: none; align-items: center; gap: 0.5rem;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                            Cetak Surat Resmi
                        </a>
                    </div>

                    <form id="modalDocRejectForm" action="" method="POST" style="display: none; margin-top: 1rem; background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.3); padding: 1rem; border-radius: 12px;">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="DITOLAK">
                        <label class="form-label" style="color: #b91c1c; font-weight: 600; margin-bottom: 0.4rem; display: block;">
                            Alasan Penolakan (Wajib Diisi):
                        </label>
                        <textarea name="notes" class="form-textarea" required placeholder="Tuliskan alasan penolakan secara jelas agar diketahui pemohon..." style="width: 100%; min-height: 80px; font-size: 0.88rem;"></textarea>
                        <div style="display: flex; gap: 0.5rem; margin-top: 0.75rem;">
                            <button type="submit" class="btn btn-danger btn-sm">Konfirmasi Tolak</button>
                            <button type="button" class="btn btn-outline btn-sm" onclick="toggleDocRejectForm(false)">Batal</button>
                        </div>
                    </form>
                </div>
            </div>

            <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--border-glass); background: var(--bg-body); display: flex; justify-content: space-between; align-items: center;">
                <a id="modalDocFullPageLink" href="#" style="font-size: 0.85rem; color: var(--accent-1); text-decoration: none; font-weight: 600;">
                    Buka Halaman Lengkap / Riwayat &rarr;
                </a>
                <button type="button" onclick="closeDocModal()" class="btn btn-outline btn-sm">Tutup</button>
            </div>
        </div>
    </div>

    {{-- Modal Popup Detail & Tindak Lanjut Pengaduan --}}
    <div id="complaintModalOverlay" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 9999; justify-content: center; align-items: center; padding: 1rem; animation: fadeIn 0.2s ease;">
        <div style="background: var(--bg-card); border: 1px solid var(--border-glass); border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); width: 100%; max-width: 620px; max-height: 90vh; overflow-y: auto; display: flex; flex-direction: column;">
            <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-glass); display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; background: var(--bg-card); z-index: 2;">
                <div>
                    <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--text-primary); margin: 0;">Detail Pengaduan Warga</h3>
                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.15rem;" id="modalComplaintDate">-</div>
                </div>
                <button type="button" onclick="closeComplaintModal()" style="background: transparent; border: none; font-size: 1.5rem; cursor: pointer; color: var(--text-muted); line-height: 1; padding: 4px;">&times;</button>
            </div>

            <div style="padding: 1.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; padding: 0.75rem 1rem; border-radius: 10px; background: rgba(37, 99, 235, 0.05); border: 1px solid var(--border-glass);">
                    <div>
                        <span style="font-size: 0.78rem; color: var(--text-muted);">Pelapor:</span>
                        <div style="font-weight: 700; font-size: 0.95rem; color: var(--text-primary);" id="modalComplaintReporter">-</div>
                    </div>
                    <div>
                        <span id="modalComplaintStatusBadge" class="badge">PENDING</span>
                    </div>
                </div>

                <div style="margin-bottom: 1.25rem;">
                    <label style="font-size: 0.78rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 0.35rem;">
                        Judul Laporan
                    </label>
                    <div id="modalComplaintTitle" style="font-size: 1.05rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.85rem;">
                        -
                    </div>

                    <label style="font-size: 0.78rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 0.35rem;">
                        Deskripsi Masalah
                    </label>
                    <div id="modalComplaintDesc" style="font-size: 0.9rem; color: var(--text-secondary); line-height: 1.5; background: var(--bg-body); padding: 0.85rem 1rem; border-radius: 10px; border: 1px solid var(--border-glass); white-space: pre-wrap;">
                        -
                    </div>
                </div>

                <div style="margin-bottom: 1.25rem; display: flex; justify-content: space-between; align-items: center; background: var(--bg-body); padding: 0.75rem 1rem; border-radius: 10px; border: 1px solid var(--border-glass);">
                    <div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">Lokasi Kejadian</div>
                        <div id="modalComplaintLocation" style="font-weight: 600; font-size: 0.88rem; color: var(--text-primary);">-</div>
                    </div>
                    <a id="modalComplaintMapsBtn" href="#" target="_blank" class="btn btn-outline btn-sm" style="display: none; font-size: 0.78rem; padding: 0.35rem 0.65rem;">
                        Buka di Maps &rarr;
                    </a>
                </div>

                <div id="modalComplaintPhotoBox" style="margin-bottom: 1.5rem; display: none;">
                    <label style="font-size: 0.78rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 0.35rem;">
                        Foto Bukti Kejadian
                    </label>
                    <div style="text-align: center; background: var(--bg-body); padding: 0.5rem; border-radius: 10px; border: 1px solid var(--border-glass);">
                        <img id="modalComplaintPhoto" src="" alt="Bukti Foto" style="max-width: 100%; max-height: 280px; object-fit: contain; border-radius: 8px;">
                    </div>
                </div>

                <div id="modalComplaintActionBox" style="padding-top: 1rem; border-top: 1px solid var(--border-glass); display: none;">
                    <label style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 0.65rem;">
                        Tindak Lanjut & Update Status Pengaduan:
                    </label>
                    
                    <form id="modalComplaintActionForm" action="" method="POST" style="margin: 0; display: flex; gap: 0.65rem; flex-wrap: wrap;">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" id="modalComplaintNewStatus" value="">

                        <button type="button" onclick="submitComplaintStatus('PROCESSING')" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.4rem; font-weight: 600;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 15px; height: 15px;"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            Diproses
                        </button>

                        <button type="button" onclick="submitComplaintStatus('RESOLVED')" class="btn btn-success" style="display: inline-flex; align-items: center; gap: 0.4rem; font-weight: 600;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 15px; height: 15px;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            Selesai
                        </button>

                        <button type="button" onclick="submitComplaintStatus('REJECTED')" class="btn btn-danger" style="display: inline-flex; align-items: center; gap: 0.4rem; font-weight: 600;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 15px; height: 15px;"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                            Ditolak
                        </button>
                    </form>
                </div>
            </div>

            <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--border-glass); background: var(--bg-body); display: flex; justify-content: space-between; align-items: center;">
                <a id="modalComplaintFullPageLink" href="#" style="font-size: 0.85rem; color: var(--accent-1); text-decoration: none; font-weight: 600;">
                    Buka Halaman Penuh &rarr;
                </a>
                <button type="button" onclick="closeComplaintModal()" class="btn btn-outline btn-sm">Tutup</button>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    function openModal(id) {
        const el = document.getElementById(id);
        if (el) {
            el.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }

    function closeModal(id) {
        const el = document.getElementById(id);
        if (el) {
            el.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    }

    function addPollOption() {
        const container = document.getElementById('pollOptionsContainer');
        const count = container.querySelectorAll('.poll-option-row').length + 1;
        const row = document.createElement('div');
        row.className = 'poll-option-row';
        row.style.cssText = 'display: flex; gap: 0.5rem; align-items: center;';
        row.innerHTML = `
            <input type="text" name="options[]" class="form-control" required placeholder="Opsi ${count}" style="flex: 1;">
            <button type="button" onclick="this.parentElement.remove()" class="btn-icon btn-icon-danger" title="Hapus opsi ini" style="width: 28px; height: 28px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        `;
        container.appendChild(row);
    }

    // ===== DOCUMENT MODAL FUNCTIONS =====
    function openDocModal(doc) {
        window.currentActiveDoc = doc;
        document.getElementById('modalDocNumber').innerText = doc.request_number;
        document.getElementById('modalDocType').innerText = doc.type;
        document.getElementById('modalDocApplicant').innerText = doc.applicant;
        document.getElementById('modalDocNik').innerText = doc.nik;
        document.getElementById('modalDocArea').innerText = doc.area;
        document.getElementById('modalDocNotes').innerText = doc.notes;
        document.getElementById('modalDocDate').innerText = doc.created_at;

        const badge = document.getElementById('modalDocStatusBadge');
        badge.innerText = doc.status.replace(/_/g, ' ');
        badge.className = 'badge ' + doc.status_badge;

        let levelDesc = '';
        if (doc.final_level === 'RT') {
            levelDesc = '✅ Verifikasi Cukup Sampai RT (Internal RT - Langsung Terbit setelah disetujui RT)';
        } else if (doc.final_level === 'RW') {
            levelDesc = '🔄 Alur Verifikasi: Warga ➔ RT ➔ Disetujui RW (Terbit setelah disetujui RW)';
        } else {
            levelDesc = '🏛️ Alur Verifikasi: Warga ➔ RT ➔ RW ➔ Kantor Desa (Terbit setelah disetujui Desa)';
        }
        document.getElementById('modalDocVerificationLevel').innerText = levelDesc;

        const attachmentRow = document.getElementById('modalDocAttachmentRow');
        const attachmentLink = document.getElementById('modalDocAttachmentLink');
        if (doc.attachment_url) {
            attachmentRow.style.display = 'table-row';
            attachmentLink.href = doc.attachment_url;
        } else {
            attachmentRow.style.display = 'none';
        }

        document.getElementById('modalDocFullPageLink').href = doc.show_url;

        const actionBox = document.getElementById('modalDocActionBox');
        const approveForm = document.getElementById('modalDocApproveForm');
        const rejectForm = document.getElementById('modalDocRejectForm');
        const approveBtn = document.getElementById('modalDocApproveBtn');
        const approveBtnText = document.getElementById('modalDocApproveBtnText');
        const approveStatus = document.getElementById('modalDocApproveStatus');
        const printBtn = document.getElementById('modalDocPrintBtn');

        toggleDocRejectForm(false);
        approveForm.action = doc.update_url;
        rejectForm.action = doc.update_url;

        if (doc.can_approve) {
            actionBox.style.display = 'block';
            approveForm.style.display = 'inline-block';
            approveStatus.value = doc.next_status;
            approveBtnText.innerText = doc.next_label;
            document.querySelector('#modalDocActionButtons .btn-danger').style.display = 'inline-block';
        } else {
            approveForm.style.display = 'none';
            document.querySelector('#modalDocActionButtons .btn-danger').style.display = 'none';
        }

        if (doc.is_approved) {
            printBtn.style.display = 'inline-flex';
            printBtn.href = doc.print_url;
            actionBox.style.display = 'block';
        } else {
            printBtn.style.display = 'none';
            if (!doc.can_approve) {
                actionBox.style.display = 'none';
            }
        }

        document.getElementById('docModalOverlay').style.display = 'flex';
    }

    function closeDocModal() {
        document.getElementById('docModalOverlay').style.display = 'none';
    }

    function toggleDocRejectForm(show) {
        document.getElementById('modalDocRejectForm').style.display = show ? 'block' : 'none';
    }

    async function confirmApproveDoc(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }

        const form = document.getElementById('modalDocApproveForm');
        if (!form) return;

        let targetAction = form.getAttribute('action') || form.action;
        if ((!targetAction || targetAction === '' || targetAction === window.location.href) && window.currentActiveDoc) {
            targetAction = window.currentActiveDoc.update_url;
            form.action = targetAction;
            form.setAttribute('action', targetAction);
        }

        const statusInput = document.getElementById('modalDocApproveStatus');
        if (statusInput && (!statusInput.value || statusInput.value === '') && window.currentActiveDoc) {
            statusInput.value = window.currentActiveDoc.next_status;
            statusInput.setAttribute('value', window.currentActiveDoc.next_status);
        }

        const label = document.getElementById('modalDocApproveBtnText')?.innerText || 'Setujui Surat';
        
        let confirmed = true;
        if (typeof window.siwilConfirm === 'function') {
            confirmed = await window.siwilConfirm({
                title: label + '?',
                message: 'Apakah Anda yakin ingin memproses dan menyetujui pengajuan surat ini? Notifikasi status akan otomatis dikirimkan ke pemohon.',
                confirmText: 'Ya, ' + label,
                type: 'success'
            });
        }

        if (confirmed) {
            form.submit();
        }
    }

    // ===== COMPLAINT MODAL FUNCTIONS =====
    function openComplaintModal(c) {
        document.getElementById('modalComplaintReporter').innerText = c.reporter;
        document.getElementById('modalComplaintDate').innerText = c.created_at;
        document.getElementById('modalComplaintTitle').innerText = c.title;
        document.getElementById('modalComplaintDesc').innerText = c.description;
        document.getElementById('modalComplaintLocation').innerText = c.location;

        const badge = document.getElementById('modalComplaintStatusBadge');
        badge.innerText = c.status;
        badge.className = 'badge ' + c.status_badge;

        const mapsBtn = document.getElementById('modalComplaintMapsBtn');
        if (c.maps_url) {
            mapsBtn.style.display = 'inline-flex';
            mapsBtn.href = c.maps_url;
        } else {
            mapsBtn.style.display = 'none';
        }

        const photoBox = document.getElementById('modalComplaintPhotoBox');
        const photoImg = document.getElementById('modalComplaintPhoto');
        if (c.photo_url) {
            photoBox.style.display = 'block';
            photoImg.src = c.photo_url;
        } else {
            photoBox.style.display = 'none';
        }

        document.getElementById('modalComplaintFullPageLink').href = c.show_url;

        const actionBox = document.getElementById('modalComplaintActionBox');
        const actionForm = document.getElementById('modalComplaintActionForm');
        actionForm.action = c.update_url;

        if (c.can_manage) {
            actionBox.style.display = 'block';
        } else {
            actionBox.style.display = 'none';
        }

        document.getElementById('complaintModalOverlay').style.display = 'flex';
    }

    async function submitComplaintStatus(status) {
        const configs = {
            'PROCESSING': {
                title: 'Proses Pengaduan?',
                message: 'Apakah Anda yakin ingin memproses pengaduan warga ini? Warga pelapor akan menerima notifikasi otomatis bahwa laporannya sedang ditindaklanjuti.',
                confirmText: 'Ya, Proses Sekarang',
                type: 'info'
            },
            'RESOLVED': {
                title: 'Selesaikan Pengaduan?',
                message: 'Apakah Anda yakin ingin menyelesaikan pengaduan ini? Warga pelapor akan menerima notifikasi otomatis bahwa masalah telah selesai ditangani.',
                confirmText: 'Ya, Selesaikan',
                type: 'success'
            },
            'REJECTED': {
                title: 'Tolak Pengaduan?',
                message: 'Apakah Anda yakin ingin menolak pengaduan ini? Warga pelapor akan menerima pemberitahuan mengenai status ini.',
                confirmText: 'Ya, Tolak',
                type: 'danger'
            }
        };

        const config = configs[status] || {
            title: 'Konfirmasi Tindak Lanjut',
            message: 'Apakah Anda yakin ingin memperbarui status pengaduan ini?',
            confirmText: 'Lanjutkan',
            type: 'info'
        };

        const confirmed = await window.siwilConfirm(config);
        if (confirmed) {
            document.getElementById('modalComplaintNewStatus').value = status;
            document.getElementById('modalComplaintActionForm').submit();
        }
    }

    function closeComplaintModal() {
        document.getElementById('complaintModalOverlay').style.display = 'none';
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal('modalCreatePoll');
            closeDocModal();
            closeComplaintModal();
        }
    });
</script>
@endsection
