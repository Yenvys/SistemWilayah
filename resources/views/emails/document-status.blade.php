@extends('emails.layout')

@section('content')
  @php
    $badgeClass = match($status) {
      'SELESAI', 'APPROVED', 'DISETUJUI_DESA' => 'badge-green',
      'REJECTED', 'DITOLAK_RT', 'DITOLAK_RW', 'DITOLAK_DESA' => 'badge-red',
      'DIAJUKAN_RT' => 'badge-blue',
      default => 'badge-yellow'
    };
  @endphp

  <div style="text-align: center;">
    <span class="badge {{ $badgeClass }}">{{ $statusTitle }}</span>
  </div>

  <div class="greeting">Halo, {{ $userName }}!</div>

  <p style="margin-top: 0; color: #334155;">{{ $statusDescription }}</p>

  <div class="info-box">
    <div class="info-row">
      <span class="info-label">Nomor Pengajuan:</span>
      <span class="info-value">{{ $docRequest->request_number ?? ('#' . substr($docRequest->id, 0, 8)) }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">Jenis Surat:</span>
      <span class="info-value">{{ $docTypeName }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">Keperluan:</span>
      <span class="info-value">{{ $docRequest->purpose ?? '-' }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">Status Saat Ini:</span>
      <span class="info-value">{{ $statusTitle }}</span>
    </div>
    @if(!empty($actorName))
    <div class="info-row">
      <span class="info-label">Diverifikasi Oleh:</span>
      <span class="info-value">{{ $actorTitle }} ({{ $actorName }})</span>
    </div>
    @endif
    @if(!empty($notes))
    <div class="info-row">
      <span class="info-label">Catatan Petugas:</span>
      <span class="info-value" style="color: #b91c1c;">{{ $notes }}</span>
    </div>
    @endif
    <div class="info-row">
      <span class="info-label">Waktu Pembaruan:</span>
      <span class="info-value">{{ now()->translatedFormat('d F Y, H:i') }} WIB</span>
    </div>
  </div>

  @if(in_array($status, ['SELESAI', 'APPROVED', 'DISETUJUI_DESA']))
    <div style="background-color: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; padding: 14px; margin: 18px 0; text-align: center; color: #065f46; font-size: 13px;">
      🎉 <strong>Surat resmi Anda telah selesai diproses dan ditandatangani secara digital!</strong><br>
      Anda dapat langsung mengunduh dan mencetak dokumen surat melalui tautan di bawah ini:
    </div>
  @endif

  <div class="btn-container">
    @if(in_array($status, ['SELESAI', 'APPROVED', 'DISETUJUI_DESA']))
      <a href="{{ $actionUrl }}" class="btn" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%);">📥 Unduh Surat di Website</a>
    @else
      <a href="{{ $actionUrl }}" class="btn">🔎 Lihat Progres Pengajuan</a>
    @endif
  </div>

  <p style="margin-top: 24px; font-size: 12px; color: #94a3b8; text-align: center;">
    Jika tombol di atas tidak dapat diklik, salin dan buka tautan berikut di browser Anda:<br>
    <a href="{{ $actionUrl }}" style="color: #3b82f6; word-break: break-all;">{{ $actionUrl }}</a>
  </p>
@endsection
