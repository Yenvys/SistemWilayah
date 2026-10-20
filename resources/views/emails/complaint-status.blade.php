@extends('emails.layout')

@section('content')
  @php
    $badgeClass = match($status) {
      'RESOLVED' => 'badge-green',
      'REJECTED' => 'badge-red',
      'PROCESSING' => 'badge-yellow',
      default => 'badge-blue'
    };
  @endphp

  <div style="text-align: center;">
    <span class="badge {{ $badgeClass }}">{{ $statusTitle }}</span>
  </div>

  <div class="greeting">Halo, {{ $userName }}!</div>

  <p style="margin-top: 0; color: #334155;">{{ $statusDescription }}</p>

  <div class="info-box">
    <div class="info-row">
      <span class="info-label">Judul Pengaduan:</span>
      <span class="info-value">{{ $complaint->title }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">Lokasi Kejadian:</span>
      <span class="info-value">{{ $complaint->location ?? '-' }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">Status Terkini:</span>
      <span class="info-value">{{ $statusTitle }}</span>
    </div>
    @if(!empty($actorName))
    <div class="info-row">
      <span class="info-label">Ditindaklanjuti Oleh:</span>
      <span class="info-value">{{ $actorTitle }} ({{ $actorName }})</span>
    </div>
    @endif
    <div class="info-row">
      <span class="info-label">Waktu Pembaruan:</span>
      <span class="info-value">{{ now()->translatedFormat('d F Y, H:i') }} WIB</span>
    </div>
  </div>

  <div style="background-color: #f1f5f9; border-left: 4px solid #3b82f6; padding: 12px 16px; border-radius: 4px; margin: 16px 0; font-size: 13px;">
    <strong>Uraian Pengaduan Anda:</strong><br>
    <span style="color: #475569;">"{{ Str::limit($complaint->description, 200) }}"</span>
  </div>

  <div class="btn-container">
    <a href="{{ $actionUrl }}" class="btn">🔎 Cek Riwayat Pengaduan</a>
  </div>

  <p style="margin-top: 24px; font-size: 12px; color: #94a3b8; text-align: center;">
    Jika tombol di atas tidak dapat diklik, salin dan buka tautan berikut di browser Anda:<br>
    <a href="{{ $actionUrl }}" style="color: #3b82f6; word-break: break-all;">{{ $actionUrl }}</a>
  </p>
@endsection
