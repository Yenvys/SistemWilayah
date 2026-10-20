@extends('layouts.public')

@section('title', 'Portal Desa ' . ($village->name ?? 'SIWIL'))
@section('page-title', 'Selamat Datang di Portal Desa ' . ($village->name ?? ''))
@section('page-subtitle', 'Sistem Informasi Warga Pintar & Layanan Administrasi — Transparansi, Partisipasi, dan Pelayanan Publik.')

@section('content')
{{-- Quick Stats --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--space-lg); margin-bottom: var(--space-2xl);">
    <div class="card" style="text-align: center; padding: var(--space-xl);">
        <div style="font-size: 2.5rem; font-weight: 800; color: var(--accent-1);">{{ number_format($totalResidents, 0, ',', '.') }}</div>
        <div style="color: var(--text-secondary); font-size: var(--font-sm); font-weight: 500; margin-top: var(--space-xs);">Jiwa Terdaftar</div>
    </div>
    <div class="card" style="text-align: center; padding: var(--space-xl);">
        <div style="font-size: 2.5rem; font-weight: 800; color: #10b981;">{{ number_format($totalFamilies, 0, ',', '.') }}</div>
        <div style="color: var(--text-secondary); font-size: var(--font-sm); font-weight: 500; margin-top: var(--space-xs);">Kepala Keluarga</div>
    </div>
    <div class="card" style="text-align: center; padding: var(--space-xl);">
        <div style="font-size: 2.5rem; font-weight: 800; color: #f59e0b;">{{ $totalRw }}</div>
        <div style="color: var(--text-secondary); font-size: var(--font-sm); font-weight: 500; margin-top: var(--space-xs);">Rukun Warga (RW)</div>
    </div>
    <div class="card" style="text-align: center; padding: var(--space-xl);">
        <div style="font-size: 2.5rem; font-weight: 800; color: #8b5cf6;">{{ $totalRt }}</div>
        <div style="color: var(--text-secondary); font-size: var(--font-sm); font-weight: 500; margin-top: var(--space-xs);">Rukun Tetangga (RT)</div>
    </div>
</div>

{{-- Menu Navigasi Cepat --}}
<h2 style="font-size: var(--font-xl); margin-bottom: var(--space-lg);">Layanan Informasi Publik</h2>
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: var(--space-lg); margin-bottom: var(--space-2xl);">
    <a href="{{ route('public.apbdes') }}" class="card" style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: var(--space-lg); padding: var(--space-xl);">
        <div style="width: 56px; height: 56px; border-radius: 14px; background: linear-gradient(135deg, #10b981, #059669); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="width:28px;height:28px;"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div>
            <h3 style="font-size: var(--font-md); margin-bottom: var(--space-xs);">Transparansi APBDes</h3>
            <p style="color: var(--text-secondary); font-size: var(--font-sm); line-height: 1.5;">Lihat anggaran pendapatan, belanja, dan realisasi desa secara terbuka.</p>
        </div>
    </a>

    <a href="{{ route('public.statistik') }}" class="card" style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: var(--space-lg); padding: var(--space-xl);">
        <div style="width: 56px; height: 56px; border-radius: 14px; background: linear-gradient(135deg, #3b82f6, #2563eb); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="width:28px;height:28px;"><path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/></svg>
        </div>
        <div>
            <h3 style="font-size: var(--font-md); margin-bottom: var(--space-xs);">Statistik Kependudukan</h3>
            <p style="color: var(--text-secondary); font-size: var(--font-sm); line-height: 1.5;">Data demografi, usia, pendidikan, dan pekerjaan warga desa.</p>
        </div>
    </a>

    <a href="{{ route('public.pembangunan') }}" class="card" style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: var(--space-lg); padding: var(--space-xl);">
        <div style="width: 56px; height: 56px; border-radius: 14px; background: linear-gradient(135deg, #f59e0b, #d97706); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="width:28px;height:28px;"><path d="M2 22h20"/><path d="M17 2v20"/><path d="M7 10v12"/><path d="M12 6v16"/></svg>
        </div>
        <div>
            <h3 style="font-size: var(--font-md); margin-bottom: var(--space-xs);">Peta Pembangunan</h3>
            <p style="color: var(--text-secondary); font-size: var(--font-sm); line-height: 1.5;">Pantau proyek infrastruktur desa dan progres pembangunan.</p>
        </div>
    </a>
</div>

{{-- Pembangunan ringkas --}}
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-lg); margin-bottom: var(--space-2xl);">
    <div class="card" style="text-align: center; padding: var(--space-xl); border-top: 4px solid #3b82f6;">
        <div style="font-size: 2rem; font-weight: 800; color: #3b82f6;">{{ $ongoingDevelopments }}</div>
        <div style="color: var(--text-secondary); font-size: var(--font-sm); margin-top: var(--space-xs);">Proyek Berjalan</div>
    </div>
    <div class="card" style="text-align: center; padding: var(--space-xl); border-top: 4px solid #10b981;">
        <div style="font-size: 2rem; font-weight: 800; color: #10b981;">{{ $completedDevelopments }}</div>
        <div style="color: var(--text-secondary); font-size: var(--font-sm); margin-top: var(--space-xs);">Proyek Selesai</div>
    </div>
</div>

{{-- Pengumuman Terbaru --}}
<h2 style="font-size: var(--font-xl); margin-bottom: var(--space-lg);">Pengumuman Terbaru</h2>
@if($announcements->count() > 0)
    <div style="display: flex; flex-direction: column; gap: var(--space-md); margin-bottom: var(--space-2xl);">
        @foreach($announcements as $announcement)
        <div class="card" style="padding: var(--space-lg);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--space-sm);">
                <h3 style="font-size: var(--font-md); font-weight: 600;">{{ $announcement->title }}</h3>
                <span style="font-size: var(--font-xs); color: var(--text-muted); white-space: nowrap; margin-left: var(--space-md);">{{ $announcement->created_at->diffForHumans() }}</span>
            </div>
            <p style="color: var(--text-secondary); font-size: var(--font-sm); line-height: 1.6;">{{ Str::limit($announcement->content, 200) }}</p>
        </div>
        @endforeach
    </div>
@else
    <div class="card" style="text-align: center; color: var(--text-muted); padding: var(--space-2xl); margin-bottom: var(--space-2xl);">
        Belum ada pengumuman terbaru.
    </div>
@endif

@endsection
