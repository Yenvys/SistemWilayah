@extends('emails.layout')

@section('content')
  @php
    $isClosed = ($type === 'CLOSED');
    $badgeClass = $isClosed ? 'badge-green' : 'badge-blue';
    $badgeText = $isClosed ? 'HASIL MUSYAWARAH' : 'MUSYAWARAH & POLLING WARGA';
  @endphp

  <div style="text-align: center;">
    <span class="badge {{ $badgeClass }}">{{ $badgeText }}</span>
  </div>

  <div class="greeting">Halo, Warga SIWIL!</div>

  @if($isClosed)
    <p style="margin-top: 0; color: #334155;">
      Musyawarah / Polling warga <strong>"{{ $poll->title }}"</strong> telah resmi ditutup dan diselesaikan.
    </p>
  @else
    <p style="margin-top: 0; color: #334155;">
      Pengurus wilayah telah membuka musyawarah / pemungutan suara (polling) warga baru. Pendapat dan suara Anda sangat berharga bagi kemajuan lingkungan kita.
    </p>
  @endif

  <div class="info-box">
    <div class="info-row">
      <span class="info-label">Judul Musyawarah:</span>
      <span class="info-value">{{ $poll->title }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">Tingkat Wilayah:</span>
      <span class="info-value">Tingkat {{ $poll->level }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">Penyelenggara:</span>
      <span class="info-value">{{ $creatorTitle }}</span>
    </div>
    @if($poll->expires_at && !$isClosed)
    <div class="info-row">
      <span class="info-label">Batas Waktu Suara:</span>
      <span class="info-value" style="color: #b91c1c;">{{ $poll->expires_at->translatedFormat('d F Y, H:i') }} WIB</span>
    </div>
    @endif
  </div>

  @if(!empty($poll->description))
    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; margin: 16px 0; font-size: 13px; color: #475569;">
      <strong>Deskripsi Musyawarah:</strong><br>
      {{ $poll->description }}
    </div>
  @endif

  @if($isClosed)
    <div style="background-color: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; padding: 16px; margin: 18px 0; color: #065f46;">
      <strong>📊 Ringkasan Hasil Keputusan:</strong><br>
      <p style="margin: 6px 0 0 0; font-size: 14px;">{{ $resultMessage }}</p>
    </div>
  @else
    <div style="margin: 18px 0;">
      <strong style="font-size: 13px; color: #334155;">Pilihan Opsi Suara:</strong>
      <ul style="margin: 8px 0; padding-left: 20px; font-size: 13px; color: #475569;">
        @foreach($poll->options as $option)
          <li style="margin-bottom: 4px;">{{ $option->text }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="btn-container">
    @if($isClosed)
      <a href="{{ $actionUrl }}" class="btn">📊 Lihat Detail Hasil Voting</a>
    @else
      <a href="{{ $actionUrl }}" class="btn" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%);">🗳️ Berikan Suara Anda Sekarang</a>
    @endif
  </div>

  <p style="margin-top: 24px; font-size: 12px; color: #94a3b8; text-align: center;">
    Jika tombol di atas tidak dapat diklik, salin dan buka tautan berikut di browser Anda:<br>
    <a href="{{ $actionUrl }}" style="color: #3b82f6; word-break: break-all;">{{ $actionUrl }}</a>
  </p>
@endsection
