@extends('layouts.app')

@section('title', 'Daftar Notifikasi - SIWIL AI')
@section('page-title', 'Pusat Notifikasi')

@section('content')
@php
    $unreadCount = Auth::user()->unreadNotificationsCount();
@endphp

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 class="page-title" style="margin-bottom: 0.25rem;">Pusat Notifikasi</h1>
        <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0;">Pemberitahuan terkini mengenai status surat, aduan, dan aktivitas Anda.</p>
    </div>
    <div id="markAllContainer">
        @if($unreadCount > 0)
            <form action="{{ route('notifications.read-all') }}" method="POST" id="markAllForm" style="margin: 0;">
                @csrf
                <button type="submit" class="btn btn-outline btn-sm" id="btnMarkAll" style="display: inline-flex; align-items: center; gap: 0.4rem;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><polyline points="20 6 9 17 4 12"/></svg>
                    Tandai Semua Dibaca
                </button>
            </form>
        @else
            <button type="button" class="btn btn-outline btn-sm" disabled style="opacity: 0.65; cursor: not-allowed; display: inline-flex; align-items: center; gap: 0.4rem;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px; color: var(--success);"><polyline points="20 6 9 17 4 12"/></svg>
                Semua Sudah Dibaca
            </button>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success" id="sessionAlert">{{ session('success') }}</div>
@endif

<div class="data-card">
    <div class="data-card-body" style="padding: 0;">
        @if($notifications->count() > 0)
            <div style="display: flex; flex-direction: column;" id="notificationList">
                @foreach($notifications as $notif)
                    @php
                        $targetUrl = $notif->url;
                        if ($targetUrl) {
                            $parsed = parse_url($targetUrl);
                            if (isset($parsed['host']) && $parsed['host'] === 'localhost' && isset($parsed['port']) && (int)$parsed['port'] === 8000) {
                                $targetUrl = url($parsed['path'] ?? '/');
                            }
                        }
                    @endphp
                    <div class="notif-card-row" id="notif-row-{{ $notif->id }}" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-glass); background: {{ $notif->is_read ? 'transparent' : 'rgba(37, 99, 235, 0.04)' }}; display: flex; gap: 1rem; align-items: flex-start; transition: background 0.2s;">
                        <div class="notif-indicator-dot" style="margin-top: 5px; width: 10px; height: 10px; border-radius: 50%; background: {{ $notif->is_read ? 'transparent' : 'var(--accent-1)' }}; flex-shrink: 0; box-shadow: {{ $notif->is_read ? 'none' : '0 0 0 3px rgba(37, 99, 235, 0.2)' }};"></div>
                        <div style="flex: 1;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem; gap: 1rem; flex-wrap: wrap;">
                                <div style="font-weight: 700; font-size: 0.95rem; color: var(--text-primary);">
                                    {{ $notif->title }}
                                </div>
                                <span style="font-size: 0.75rem; color: var(--text-muted); white-space: nowrap;">
                                    {{ $notif->created_at->diffForHumans() }}
                                </span>
                            </div>
                            <div style="font-size: 0.85rem; color: var(--text-secondary); line-height: 1.45; margin-bottom: 0.75rem;">
                                {{ $notif->message }}
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                                <div style="display: flex; gap: 0.5rem; align-items: center;">
                                    <span class="badge {{ $notif->type === 'SURAT' ? 'badge-blue' : ($notif->type === 'PENGADUAN' ? 'badge-yellow' : 'badge-gray') }}" style="font-size: 0.7rem;">
                                        {{ $notif->type }}
                                    </span>
                                    <span class="status-read-badge" style="font-size: 0.75rem; color: var(--text-muted); display: {{ $notif->is_read ? 'inline-flex' : 'none' }}; align-items: center; gap: 0.25rem;">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 13px; height: 13px; color: var(--success);"><polyline points="20 6 9 17 4 12"/></svg>
                                        Sudah Dibaca
                                    </span>
                                </div>
                                <div style="display: flex; gap: 0.5rem; align-items: center;">
                                    @if(!$notif->is_read)
                                        <form action="{{ route('notifications.read', $notif->id) }}" method="POST" style="margin: 0;" class="mark-read-single-form" data-id="{{ $notif->id }}">
                                            @csrf
                                            <button type="submit" class="btn btn-outline btn-sm" style="font-size: 0.75rem; padding: 4px 10px; display: inline-flex; align-items: center; gap: 0.35rem; cursor: pointer;">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 12px; height: 12px;"><polyline points="20 6 9 17 4 12"/></svg>
                                                Tandai Dibaca
                                            </button>
                                        </form>
                                    @endif
                                    @if($targetUrl)
                                        <a href="{{ $targetUrl }}" class="btn btn-primary btn-sm" style="font-size: 0.75rem; padding: 4px 12px; text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem;">
                                            Lihat Detail &rarr;
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div style="padding: 1.25rem; border-top: 1px solid var(--border-glass);">
                {{ $notifications->links() }}
            </div>
        @else
            <div style="padding: 4rem 1.5rem; text-align: center; color: var(--text-muted);">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width: 48px; height: 48px; margin: 0 auto 0.75rem; opacity: 0.4;"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                <div style="font-weight: 600; color: var(--text-primary); margin-bottom: 0.25rem;">Tidak Ada Notifikasi</div>
                <p style="font-size: 0.85rem; margin: 0;">Semua pembaruan proses layanan surat atau pengaduan akan ditampilkan di sini.</p>
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handler for single mark as read
    document.querySelectorAll('.mark-read-single-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = form.querySelector('button');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = 'Memproses...';

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const row = form.closest('.notif-card-row');
                    if (row) {
                        row.style.background = 'transparent';
                        const dot = row.querySelector('.notif-indicator-dot');
                        if (dot) {
                            dot.style.background = 'transparent';
                            dot.style.boxShadow = 'none';
                        }
                        const badge = row.querySelector('.status-read-badge');
                        if (badge) badge.style.display = 'inline-flex';
                    }
                    form.remove();

                    // Update topbar notification badge if available
                    updateNavNotifCount(data.unread_count);
                    if (data.unread_count === 0) {
                        setMarkAllAsCompleted();
                    }
                } else {
                    form.submit();
                }
            })
            .catch(() => {
                form.submit();
            });
        });
    });

    // Handler for mark all as read
    const markAllForm = document.getElementById('markAllForm');
    if (markAllForm) {
        markAllForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('btnMarkAll');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = 'Memproses...';
            }

            fetch(markAllForm.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': markAllForm.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update all rows in view
                    document.querySelectorAll('.notif-card-row').forEach(row => {
                        row.style.background = 'transparent';
                        const dot = row.querySelector('.notif-indicator-dot');
                        if (dot) {
                            dot.style.background = 'transparent';
                            dot.style.boxShadow = 'none';
                        }
                        const badge = row.querySelector('.status-read-badge');
                        if (badge) badge.style.display = 'inline-flex';
                    });
                    document.querySelectorAll('.mark-read-single-form').forEach(f => f.remove());
                    setMarkAllAsCompleted();
                    updateNavNotifCount(0);
                } else {
                    markAllForm.submit();
                }
            })
            .catch(() => {
                markAllForm.submit();
            });
        });
    }

    function setMarkAllAsCompleted() {
        const container = document.getElementById('markAllContainer');
        if (container) {
            container.innerHTML = `
                <button type="button" class="btn btn-outline btn-sm" disabled style="opacity: 0.65; cursor: not-allowed; display: inline-flex; align-items: center; gap: 0.4rem;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px; color: var(--success);"><polyline points="20 6 9 17 4 12"/></svg>
                    Semua Sudah Dibaca
                </button>
            `;
        }
    }

    function updateNavNotifCount(count) {
        const badge = document.querySelector('#notifToggle span');
        if (badge) {
            if (count > 0) {
                badge.textContent = count > 9 ? '9+' : count;
            } else {
                badge.remove();
            }
        }
        const topnavBtn = document.getElementById('topnavMarkAllReadForm');
        if (topnavBtn && count === 0) {
            topnavBtn.remove();
        }
    }
});
</script>
@endsection
