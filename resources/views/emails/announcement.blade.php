@extends('emails.layout')

@section('content')
  <div style="text-align: center;">
    <span class="badge badge-blue">INFORMASI PUBLIK & PENGUMUMAN</span>
  </div>

  <div class="greeting">Halo, Warga Terdaftar SIWIL!</div>

  <p style="margin-top: 0; color: #334155;">
    Ada informasi publik resmi terbaru yang diterbitkan untuk seluruh warga lingkungan kita:
  </p>

  <div style="background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 20px; margin: 20px 0;">
    <h2 style="margin: 0 0 10px 0; font-size: 18px; color: #0f172a; line-height: 1.4;">
      {{ $announcement->title }}
    </h2>
    <div style="font-size: 12px; color: #64748b; margin-bottom: 16px;">
      Diterbitkan oleh: <strong>{{ $announcement->author?->name ?? 'Pemerintah Wilayah' }}</strong> &bull; 
      {{ ($announcement->published_at ?? now())->translatedFormat('d F Y, H:i') }} WIB
    </div>

    <div style="font-size: 14px; color: #334155; line-height: 1.6; white-space: pre-line;">
      {{ $announcement->content }}
    </div>
  </div>

  <div class="btn-container">
    <a href="{{ $actionUrl }}" class="btn">📰 Buka Papan Informasi SIWIL</a>
  </div>

  <p style="margin-top: 24px; font-size: 12px; color: #94a3b8; text-align: center;">
    Jika tombol di atas tidak dapat diklik, salin dan buka tautan berikut di browser Anda:<br>
    <a href="{{ $actionUrl }}" style="color: #3b82f6; word-break: break-all;">{{ $actionUrl }}</a>
  </p>
@endsection
