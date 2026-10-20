@extends('layouts.app')

@section('title', 'Musyawarah Digital - SIWIL AI')
@section('page-title', 'Musyawarah / Polling')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2 style="font-size: 1.5rem; color: var(--text-primary);">Daftar Musyawarah / Polling</h2>
        @if(Auth::user()->role !== 'WARGA')
        <a href="{{ route('polls.create') }}" class="btn btn-primary">Buat Polling Baru</a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="data-card">
        <div class="data-card-header">
            <span class="data-card-title">Semua Polling</span>
        </div>
        @if(isset($polls) && $polls->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>Judul Polling</th>
                        <th>Cakupan Wilayah</th>
                        <th>Dibuat Oleh</th>
                        <th>Partisipasi</th>
                        <th>Status</th>
                        <th>Batas Waktu</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($polls as $poll)
                    <tr>
                        <td style="font-weight: 600; max-width: 260px;">
                            <a href="{{ route('polls.show', $poll->id) }}" style="color: var(--primary-color); text-decoration: none;">
                                {{ $poll->title }}
                            </a>
                        </td>
                        <td>
                            @if(($poll->level ?? 'DESA') === 'RT' && $poll->rt)
                                <span class="badge badge-info" style="font-size: 0.72rem;">RT {{ $poll->rt->rt_number }} / RW {{ $poll->rt->rw->rw_number ?? '-' }}</span>
                            @elseif(($poll->level ?? 'DESA') === 'RW' && $poll->rw)
                                <span class="badge badge-warning" style="font-size: 0.72rem;">RW {{ $poll->rw->rw_number }}</span>
                            @else
                                <span class="badge badge-primary" style="font-size: 0.72rem;">Tingkat Desa</span>
                            @endif
                        </td>
                        <td>{{ $poll->creator->name ?? '-' }}</td>
                        <td>
                            <span style="font-weight: 600;">{{ $poll->votes_count ?? $poll->votes->count() }}</span>
                            <span style="font-size: 0.8rem; color: var(--text-muted);">suara</span>
                        </td>
                        <td>
                            @php
                                $isExpired = $poll->expires_at && $poll->expires_at->isPast();
                                $displayStatus = ($poll->status === 'ACTIVE' && !$isExpired) ? 'AKTIF' : 'SELESAI';
                            @endphp
                            <span class="badge {{ $displayStatus === 'AKTIF' ? 'badge-green' : 'badge-red' }}">
                                {{ $displayStatus }}
                            </span>
                        </td>
                        <td>{{ $poll->expires_at ? $poll->expires_at->format('d M Y') : 'Tanpa Batas' }}</td>
                        <td>
                            <div style="display:inline-flex; gap:0.4rem; align-items:center;">
                                <a href="{{ route('polls.show', $poll->id) }}" class="btn-icon btn-icon-view" title="Lihat / Ikuti Musyawarah" aria-label="Lihat / Ikuti Musyawarah">
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
            <div style="padding: 1rem;">
                {{ $polls->links() }}
            </div>
        @else
            <div style="padding: 3rem; text-align: center; color: var(--text-muted);">Belum ada polling / musyawarah.</div>
        @endif
    </div>
@endsection
