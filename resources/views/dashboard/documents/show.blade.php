@extends('layouts.app')

@section('title', 'Detail Pengajuan Surat - SIWIL AI')
@section('page-title', 'Detail Pengajuan Surat')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h1 class="page-title" style="margin-bottom: 0;">Detail Surat: {{ $docRequest->request_number }}</h1>
    <a href="{{ route('documents.index') }}" class="btn btn-outline">
        Kembali
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-error">{{ $errors->first() }}</div>
@endif

<div class="dashboard-grid">
    <!-- Left Column: Letter Details -->
    <div class="data-card">
        <div class="data-card-header">
            <h2 class="data-card-title">Informasi Surat</h2>
            @if($docRequest->status === 'SELESAI' || $docRequest->status === 'DISETUJUI')
                <span class="badge badge-green">{{ $docRequest->status }}</span>
            @elseif($docRequest->status === 'DITOLAK')
                <span class="badge badge-red">{{ $docRequest->status }}</span>
            @else
                <span class="badge badge-yellow">{{ $docRequest->status }}</span>
            @endif
        </div>
        <div class="data-card-body">
            <table style="width: 100%; border: none;">
                <tr style="border-bottom: 1px solid var(--border-glass);">
                    <td style="width: 30%; color: var(--text-secondary); padding: 12px 0;">Jenis Surat</td>
                    <td style="font-weight: 600; padding: 12px 0;">{{ str_replace('_', ' ', $docRequest->type) }}</td>
                </tr>
                <tr style="border-bottom: 1px solid var(--border-glass);">
                    <td style="color: var(--text-secondary); padding: 12px 0;">Nama Pemohon</td>
                    <td style="font-weight: 600; padding: 12px 0;">{{ $docRequest->resident->full_name }}</td>
                </tr>
                <tr style="border-bottom: 1px solid var(--border-glass);">
                    <td style="color: var(--text-secondary); padding: 12px 0;">NIK</td>
                    <td style="font-weight: 600; padding: 12px 0;">{{ $docRequest->resident->nik }}</td>
                </tr>
                <tr style="border-bottom: 1px solid var(--border-glass);">
                    <td style="color: var(--text-secondary); padding: 12px 0;">Wilayah</td>
                    <td style="font-weight: 600; padding: 12px 0;">RT {{ $docRequest->rt_number }} / RW {{ $docRequest->rw_number }}, Desa {{ $docRequest->village_name }}</td>
                </tr>
                <tr style="border-bottom: 1px solid var(--border-glass);">
                    <td style="color: var(--text-secondary); padding: 12px 0;">Keperluan / Catatan</td>
                    <td style="font-weight: 600; padding: 12px 0;">{{ $docRequest->notes ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="color: var(--text-secondary); padding: 12px 0;">Tanggal Pengajuan</td>
                    <td style="font-weight: 600; padding: 12px 0;">{{ $docRequest->created_at->format('d M Y, H:i') }}</td>
                </tr>
                @if($docRequest->attachment_path)
                <tr>
                    <td style="color: var(--text-secondary); padding: 12px 0; border-top: 1px solid var(--border-glass);">Berkas Persyaratan</td>
                    <td style="font-weight: 600; padding: 12px 0; border-top: 1px solid var(--border-glass);">
                        <a href="{{ asset('storage/' . $docRequest->attachment_path) }}" target="_blank" class="btn btn-outline" style="padding: 0.3rem 0.8rem; font-size: 0.85rem;">Lihat Berkas</a>
                    </td>
                </tr>
                @endif
            </table>

            <!-- Actions Area -->
            <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px dashed var(--border-glass); display: flex; gap: 1rem; align-items: center;">
                @if(in_array($docRequest->status, ['DISETUJUI', 'SELESAI']))
                    <a href="{{ route('documents.print', $docRequest->id) }}" target="_blank" class="btn btn-success"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg> Cetak Dokumen</a>
                @endif

                @php
                    $role = Auth::user()->role;
                    $finalLevel = $docRequest->final_approval_level;
                    $nextStatus = $docRequest->next_action_status;
                @endphp

                @if($role !== 'WARGA' && !in_array($docRequest->status, ['SELESAI', 'DITOLAK']))
                    
                    @if($docRequest->status === 'DIAJUKAN_RT' && in_array($role, ['RT', 'SUPER_ADMIN']))
                        <form action="{{ route('documents.update', $docRequest->id) }}" method="POST" style="display:inline;">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="{{ $nextStatus }}">
                            <button type="submit" class="btn btn-primary">
                                {{ $nextStatus === 'DISETUJUI' ? 'Setujui & Terbitkan' : 'Setujui (Teruskan ke RW)' }}
                            </button>
                        </form>
                        <button type="button" class="btn btn-danger" onclick="document.getElementById('reject-form').style.display='block'">Tolak</button>

                    @elseif($docRequest->status === 'DIAJUKAN_RW' && in_array($role, ['RW', 'SUPER_ADMIN']))
                        <form action="{{ route('documents.update', $docRequest->id) }}" method="POST" style="display:inline;">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="{{ $nextStatus }}">
                            <button type="submit" class="btn btn-primary">
                                {{ $nextStatus === 'DISETUJUI' ? 'Setujui & Terbitkan' : 'Setujui (Teruskan ke Desa)' }}
                            </button>
                        </form>
                        <button type="button" class="btn btn-danger" onclick="document.getElementById('reject-form').style.display='block'">Tolak</button>

                    @elseif($docRequest->status === 'DIAJUKAN_DESA' && in_array($role, ['ADMIN_DESA', 'SUPER_ADMIN']))
                        <form action="{{ route('documents.update', $docRequest->id) }}" method="POST" style="display:inline;">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="DISETUJUI">
                            <button type="submit" class="btn btn-primary">Setujui & Terbitkan</button>
                        </form>
                        <button type="button" class="btn btn-danger" onclick="document.getElementById('reject-form').style.display='block'">Tolak</button>
                    @endif

                    <!-- Reject Form (Hidden by default) -->
                    <form id="reject-form" action="{{ route('documents.update', $docRequest->id) }}" method="POST" style="display:none; width: 100%; margin-top: 1rem; background: #fee2e2; padding: 1rem; border-radius: 12px; border: 1px solid #fecaca;">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="DITOLAK">
                        <label class="form-label" style="color: #991b1b;">Alasan Penolakan (Wajib)</label>
                        <textarea name="notes" class="form-textarea" required placeholder="Tuliskan alasan penolakan..."></textarea>
                        <div style="margin-top: 1rem; display: flex; gap: 0.5rem;">
                            <button type="submit" class="btn btn-danger">Konfirmasi Tolak</button>
                            <button type="button" class="btn btn-outline" style="background: white;" onclick="document.getElementById('reject-form').style.display='none'">Batal</button>
                        </div>
                    </form>

                @endif
            </div>
        </div>
    </div>

    <!-- Right Column: History Tracking -->
    <div class="data-card">
        <div class="data-card-header">
            <h2 class="data-card-title">Riwayat Pengajuan</h2>
        </div>
        <div class="data-card-body">
            @if($docRequest->histories && $docRequest->histories->count() > 0)
                <ul style="list-style: none; padding: 0; position: relative;">
                    @foreach($docRequest->histories as $idx => $history)
                        <li style="position: relative; padding-left: 24px; margin-bottom: 1.5rem;">
                            <!-- Line connector -->
                            @if(!$loop->last)
                                <div style="position: absolute; left: 5px; top: 20px; bottom: -20px; width: 2px; background: var(--border-glass);"></div>
                            @endif
                            <!-- Dot -->
                            <div style="position: absolute; left: 0; top: 4px; width: 12px; height: 12px; border-radius: 50%; background: var(--accent-1); border: 2px solid white; box-shadow: 0 0 0 2px var(--accent-1);"></div>
                            
                            <div style="font-weight: 600; font-size: 0.95rem; color: var(--text-primary);">
                                {{ $history->status }}
                            </div>
                            <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 4px;">
                                {{ $history->created_at->format('d M Y, H:i') }}
                            </div>
                            <div style="font-size: 0.85rem; color: var(--text-secondary); background: var(--bg-body); padding: 8px 12px; border-radius: 8px; border: 1px solid var(--border-glass);">
                                {{ $history->notes }}
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-muted">Belum ada riwayat.</p>
            @endif
        </div>
    </div>
</div>
@endsection
