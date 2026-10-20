@extends('layouts.app')

@section('title', 'Detail Pengaduan - SIWIL AI')
@section('page-title', 'Detail Pengaduan')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h1 class="page-title" style="margin-bottom: 0;">Detail Pengaduan</h1>
    <a href="{{ route('complaints.index') }}" class="btn btn-outline">Kembali</a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="dashboard-grid">
    <div class="data-card">
        <div class="data-card-header">
            <h2 class="data-card-title">Informasi Laporan</h2>
            <span class="badge 
                @if($complaint->status == 'PENDING') badge-yellow 
                @elseif($complaint->status == 'PROCESSING') badge-blue 
                @elseif($complaint->status == 'RESOLVED') badge-green 
                @else badge-red @endif">
                {{ $complaint->status }}
            </span>
        </div>
        <div class="data-card-body">
            <table style="width: 100%; border: none;">
                <tr style="border-bottom: 1px solid var(--border-glass);">
                    <td style="width: 30%; color: var(--text-secondary); padding: 12px 0;">Pelapor</td>
                    <td style="font-weight: 600; padding: 12px 0;">{{ $complaint->user->name ?? 'Anonim' }}</td>
                </tr>
                <tr style="border-bottom: 1px solid var(--border-glass);">
                    <td style="color: var(--text-secondary); padding: 12px 0;">Tanggal Lapor</td>
                    <td style="font-weight: 600; padding: 12px 0;">{{ $complaint->created_at->format('d M Y, H:i') }}</td>
                </tr>
                <tr style="border-bottom: 1px solid var(--border-glass);">
                    <td style="color: var(--text-secondary); padding: 12px 0;">Judul</td>
                    <td style="font-weight: 600; padding: 12px 0;">{{ $complaint->title }}</td>
                </tr>
                <tr style="border-bottom: 1px solid var(--border-glass);">
                    <td style="color: var(--text-secondary); padding: 12px 0;">Deskripsi</td>
                    <td style="padding: 12px 0;">{{ $complaint->description }}</td>
                </tr>
                <tr>
                    <td style="color: var(--text-secondary); padding: 12px 0;">Lokasi Kejadian</td>
                    <td style="font-weight: 600; padding: 12px 0;">
                        {{ $complaint->location ?? '-' }}
                        @if($complaint->location && strpos($complaint->location, ',') !== false)
                            <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode(str_replace(' ', '', $complaint->location)) }}" target="_blank" style="margin-left: 0.5rem; color: var(--accent-1); text-decoration: underline; font-size: 0.85rem;">Buka di Maps</a>
                        @endif
                    </td>
                </tr>
            </table>

            @if(Auth::user()->role !== 'WARGA')
            <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px dashed var(--border-glass);">
                <h3 style="font-size: 1rem; margin-bottom: 0.75rem;">Tindak Lanjut & Update Status Pengaduan:</h3>
                <form id="complaintDirectActionForm" action="{{ route('complaints.update-status', $complaint->id) }}" method="POST" style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center;">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" id="complaintDirectStatus" value="">

                    @if($complaint->status !== 'PROCESSING')
                    <button type="button" onclick="submitDirectStatus('PROCESSING')" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.4rem; font-weight: 600;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 15px; height: 15px;"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        Tandai Diproses
                    </button>
                    @endif

                    @if($complaint->status !== 'RESOLVED')
                    <button type="button" onclick="submitDirectStatus('RESOLVED')" class="btn btn-success" style="display: inline-flex; align-items: center; gap: 0.4rem; font-weight: 600;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 15px; height: 15px;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        Tandai Selesai
                    </button>
                    @endif

                    @if($complaint->status !== 'REJECTED')
                    <button type="button" onclick="submitDirectStatus('REJECTED')" class="btn btn-danger" style="display: inline-flex; align-items: center; gap: 0.4rem; font-weight: 600;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 15px; height: 15px;"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                        Tolak Laporan
                    </button>
                    @endif
                </form>
                <script>
                async function submitDirectStatus(status) {
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

                    let confirmed = false;
                    try {
                        if (typeof window.siwilConfirm === 'function') {
                            confirmed = await window.siwilConfirm(config);
                        } else {
                            confirmed = window.confirm(config.message);
                        }
                    } catch (e) {
                        console.warn('Confirmation error, fallback to native confirm:', e);
                        confirmed = window.confirm(config.message);
                    }

                    if (confirmed) {
                        document.getElementById('complaintDirectStatus').value = status;
                        document.getElementById('complaintDirectActionForm').submit();
                    }
                }
                </script>
            </div>
            @endif
        </div>
    </div>

    <div class="data-card">
        <div class="data-card-header">
            <h2 class="data-card-title">Foto / Bukti Lampiran</h2>
        </div>
        <div class="data-card-body" style="text-align: center;">
            @if($complaint->photo_path)
                <img src="{{ asset('storage/' . $complaint->photo_path) }}" alt="Foto Bukti" style="max-width: 100%; max-height: 400px; border-radius: 8px; border: 1px solid var(--border-glass);">
            @else
                <div style="padding: 3rem; color: var(--text-muted); background: var(--bg-body); border-radius: 8px; border: 1px dashed var(--border-glass);">
                    Tidak ada foto lampiran.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
