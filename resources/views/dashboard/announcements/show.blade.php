@extends('layouts.app')

@section('title', $announcement->title . ' - Pengumuman Desa')
@section('page-title', 'Detail Pengumuman Desa')

@section('content')
<div style="max-width: 820px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <a href="{{ route('announcements.index') }}" class="btn btn-outline btn-sm" style="display: inline-flex; align-items: center; gap: 0.4rem;">
            &larr; Kembali ke Daftar Pengumuman
        </a>

        @if($isDesa)
            <div style="display: flex; gap: 0.5rem; align-items: center;">
                <a href="{{ route('announcements.edit', $announcement->id) }}" class="btn btn-outline btn-sm" style="display: inline-flex; align-items: center; gap: 0.35rem;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Edit
                </a>
                <form id="delete-announcement-{{ $announcement->id }}" action="{{ route('announcements.destroy', $announcement->id) }}" method="POST" style="margin: 0;">
                    @csrf
                    @method('DELETE')
                    <button type="button" onclick="confirmDeleteAnnouncement('{{ $announcement->id }}', '{{ addslashes($announcement->title) }}')" class="btn btn-danger btn-sm" style="display: inline-flex; align-items: center; gap: 0.35rem;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        Hapus
                    </button>
                </form>
            </div>
        @endif
    </div>

    <div class="data-card" style="margin-bottom: 2rem;">
        <div class="data-card-body" style="padding: 2.25rem;">
            {{-- Header Badges & Meta --}}
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    <span class="badge badge-blue" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                        PENGUMUMAN RESMI DESA
                    </span>
                    @if($isDesa)
                        @if($announcement->is_active)
                            <span class="badge badge-green" style="font-size: 0.75rem;">Terbit (Publik)</span>
                        @else
                            <span class="badge badge-yellow" style="font-size: 0.75rem;">Draf (Nonaktif)</span>
                        @endif
                    @endif
                </div>
                <div style="font-size: 0.85rem; color: var(--text-muted); display: flex; align-items: center; gap: 0.4rem;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    {{ $announcement->published_at ? $announcement->published_at->translatedFormat('d F Y, H:i') : $announcement->created_at->translatedFormat('d F Y, H:i') }}
                </div>
            </div>

            {{-- Title --}}
            <h1 style="font-size: 1.65rem; color: var(--text-primary); font-weight: 800; line-height: 1.35; margin-bottom: 1.5rem;">
                {{ $announcement->title }}
            </h1>

            {{-- Author info bar --}}
            <div style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; background: var(--bg-body); border-radius: 10px; border: 1px solid var(--border-glass); margin-bottom: 2rem;">
                <div style="width: 38px; height: 38px; border-radius: 50%; background: var(--accent-gradient); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.9rem;">
                    {{ strtoupper(substr($announcement->author->name ?? 'PD', 0, 2)) }}
                </div>
                <div>
                    <div style="font-weight: 700; font-size: 0.9rem; color: var(--text-primary);">
                        {{ $announcement->author->name ?? 'Pemerintah Desa' }}
                    </div>
                    <div style="font-size: 0.78rem; color: var(--text-muted);">
                        Pemerintah Desa / Administrator Sistem Informasi Wilayah
                    </div>
                </div>
            </div>

            {{-- Full Content --}}
            <div style="font-size: 1rem; color: var(--text-secondary); line-height: 1.8; word-break: break-word; white-space: pre-wrap;">
{!! nl2br(e($announcement->content)) !!}
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
async function confirmDeleteAnnouncement(id, title) {
    if (typeof window.siwilConfirm === 'function') {
        const confirmed = await window.siwilConfirm({
            title: 'Hapus Pengumuman Desa?',
            message: 'Apakah Anda yakin ingin menghapus pengumuman "' + title + '"? Data yang telah dihapus tidak dapat dipulihkan kembali.',
            confirmText: 'Ya, Hapus Pengumuman',
            cancelText: 'Batal',
            type: 'danger'
        });
        if (confirmed) {
            document.getElementById('delete-announcement-' + id).submit();
        }
    } else {
        if (confirm('Hapus pengumuman "' + title + '"?')) {
            document.getElementById('delete-announcement-' + id).submit();
        }
    }
}
</script>
@endsection
