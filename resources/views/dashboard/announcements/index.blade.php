@extends('layouts.app')

@section('title', 'Pengumuman & Informasi Desa - SIWIL AI')
@section('page-title', 'Pengumuman & Informasi Desa')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 class="page-title" style="margin-bottom: 0.25rem;">Pengumuman & Informasi Desa</h1>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0;">
                Pemberitahuan resmi dan kabar penting dari Pemerintah Desa untuk seluruh warga dan pengurus wilayah.
            </p>
        </div>

        @if($isDesa)
            <a href="{{ route('announcements.create') }}" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.4rem; font-weight: 600;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                + Buat Pengumuman Baru
            </a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 1.5rem;">
            {{ session('success') }}
        </div>
    @endif

    {{-- 1. PENGUMUMAN DESA / PENGUMUMAN RESMI (DI ATAS) --}}
    @if(isset($announcements) && $announcements->count() > 0)
        <div style="margin-bottom: 2.5rem;">
            <div style="margin-bottom: 1rem;">
                <h2 style="font-size: 1.15rem; font-weight: 700; color: var(--text-primary); margin: 0 0 0.25rem 0; display: flex; align-items: center; gap: 0.5rem;">
                    <span style="display: inline-flex; width: 30px; height: 30px; border-radius: 8px; background: rgba(37, 99, 235, 0.15); color: #2563eb; align-items: center; justify-content: center;">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 11 18-5v12L3 13v-2z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/></svg>
                    </span>
                    Pengumuman Resmi & Warta Desa
                </h2>
                <p style="font-size: 0.82rem; color: var(--text-muted); margin: 0;">Pemberitahuan aktif dan kabar penting dari Pemerintah Desa</p>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem;">
                @foreach($announcements as $announcement)
                <div class="stat-card" style="display: flex; flex-direction: column; justify-content: space-between; align-items: stretch; cursor: default; height: 100%; border-radius: 14px; position: relative; overflow: hidden;">
                    <div>
                        {{-- Header Top Badges --}}
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; gap: 0.5rem; flex-wrap: wrap;">
                            <span style="font-size: 0.75rem; color: var(--accent-1); font-weight: 700; display: inline-flex; align-items: center; gap: 0.35rem;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 13px; height: 13px;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                {{ $announcement->published_at ? $announcement->published_at->translatedFormat('d M Y') : $announcement->created_at->translatedFormat('d M Y') }}
                            </span>

                            @if($isDesa)
                                @if($announcement->is_active)
                                    <span class="badge badge-green" style="font-size: 0.68rem; padding: 2px 8px;">Terbit</span>
                                @else
                                    <span class="badge badge-yellow" style="font-size: 0.68rem; padding: 2px 8px;">Draf</span>
                                @endif
                            @endif
                        </div>

                        {{-- Title --}}
                        <h3 style="font-size: 1.15rem; color: var(--text-primary); margin-bottom: 0.75rem; font-weight: 700; line-height: 1.4;">
                            <a href="{{ route('announcements.show', $announcement->id) }}" style="color: inherit; text-decoration: none;">
                                {{ $announcement->title }}
                            </a>
                        </h3>

                        {{-- Content Excerpt --}}
                        <p style="color: var(--text-secondary); font-size: 0.88rem; line-height: 1.55; margin-bottom: 1.25rem;">
                            {{ Str::limit(strip_tags($announcement->content), 140) }}
                        </p>
                    </div>

                    {{-- Footer Area --}}
                    <div style="border-top: 1px solid var(--border-glass); padding-top: 0.85rem; margin-top: auto; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                        <div style="font-size: 0.78rem; color: var(--text-muted); display: flex; align-items: center; gap: 0.4rem;">
                            <div style="width: 22px; height: 22px; border-radius: 50%; background: var(--accent-gradient); color: white; display: inline-flex; align-items: center; justify-content: center; font-size: 0.65rem; font-weight: 700;">
                                {{ strtoupper(substr($announcement->author->name ?? 'PD', 0, 1)) }}
                            </div>
                            <span>{{ $announcement->author->name ?? 'Pemerintah Desa' }}</span>
                        </div>

                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <a href="{{ route('announcements.show', $announcement->id) }}" style="font-size: 0.8rem; font-weight: 700; color: var(--accent-1); text-decoration: none; display: inline-flex; align-items: center; gap: 0.2rem;">
                                Baca &rarr;
                            </a>

                            @if($isDesa)
                                <a href="{{ route('announcements.edit', $announcement->id) }}" title="Edit Pengumuman" style="color: var(--text-muted); padding: 4px; display: inline-flex; align-items: center; transition: color 0.2s;">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </a>
                                <form id="delete-announcement-{{ $announcement->id }}" action="{{ route('announcements.destroy', $announcement->id) }}" method="POST" style="margin: 0; display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" onclick="confirmDeleteAnnouncement('{{ $announcement->id }}', '{{ addslashes($announcement->title) }}')" title="Hapus Pengumuman" style="background: none; border: none; color: var(--danger); padding: 4px; cursor: pointer; display: inline-flex; align-items: center; opacity: 0.75; transition: opacity 0.2s;">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            <div style="margin-top: 2rem;">
                {{ $announcements->links() }}
            </div>
        </div>
    @else
        <div class="data-card" style="margin-bottom: 2.5rem;">
            <div style="padding: 3.5rem 1.5rem; text-align: center; color: var(--text-muted);">
                <div style="width: 58px; height: 58px; border-radius: 50%; background: rgba(37, 99, 235, 0.08); color: var(--accent-1); display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="width: 28px; height: 28px;"><path d="m3 11 18-5v12L3 13v-2z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/></svg>
                </div>
                <h3 style="font-size: 1.15rem; color: var(--text-primary); font-weight: 700; margin-bottom: 0.35rem;">Belum Ada Pengumuman Desa</h3>
                <p style="font-size: 0.85rem; max-width: 480px; margin: 0 auto 1.5rem; line-height: 1.5;">
                    Semua kabar kegiatan, jadwal penyaluran bantuan, atau himbauan resmi dari Pemerintah Desa akan ditampilkan di sini untuk seluruh warga.
                </p>
                @if($isDesa)
                    <a href="{{ route('announcements.create') }}" class="btn btn-primary btn-sm" style="display: inline-flex; align-items: center; gap: 0.4rem;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Buat Pengumuman Pertama
                    </a>
                @endif
            </div>
        </div>
    @endif

    {{-- 2. KEPUTUSAN & HASIL MUSYAWARAH DESA (DI BAWAH) --}}
    @if(isset($completedPolls) && $completedPolls->count() > 0)
        <div style="margin-bottom: 2rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;">
                <div>
                    <h2 style="font-size: 1.15rem; font-weight: 700; color: var(--text-primary); margin: 0 0 0.25rem 0; display: flex; align-items: center; gap: 0.5rem;">
                        <span style="display: inline-flex; width: 30px; height: 30px; border-radius: 8px; background: rgba(16, 185, 129, 0.15); color: #10b981; align-items: center; justify-content: center;">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        </span>
                        Keputusan & Hasil Musyawarah Desa
                    </h2>
                    <p style="font-size: 0.82rem; color: var(--text-muted); margin: 0;">Hasil pemungutan suara dan keputusan bersama warga wilayah</p>
                </div>
                <a href="{{ route('polls.index') }}" class="btn btn-outline btn-sm" style="font-size: 0.78rem;">Lihat Semua Musyawarah &rarr;</a>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.25rem;">
                @foreach($completedPolls as $cPoll)
                    @php
                        $winOpt = $cPoll->winning_option;
                        $tVotes = $cPoll->votes->count();
                        $wVotes = $winOpt ? $winOpt->votes->count() : 0;
                        $pct = $tVotes > 0 ? round(($wVotes / $tVotes) * 100) : 0;
                    @endphp
                    <div class="stat-card" style="border-radius: 12px; border: 1px solid rgba(16, 185, 129, 0.25); background: var(--bg-card); display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <span class="badge badge-green" style="font-size: 0.68rem; padding: 2px 7px;">Hasil Musyawarah</span>
                                <span style="font-size: 0.75rem; color: var(--text-muted);">{{ $cPoll->updated_at->format('d M Y') }}</span>
                            </div>
                            <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--text-primary); margin: 0 0 0.5rem 0;">
                                <a href="{{ route('polls.show', $cPoll->id) }}" style="color: inherit; text-decoration: none;">
                                    {{ $cPoll->title }}
                                </a>
                            </h3>
                            @if($winOpt)
                                <div style="background: rgba(16, 185, 129, 0.08); border-radius: 8px; padding: 0.65rem 0.75rem; margin-bottom: 0.85rem; font-size: 0.85rem; color: var(--text-secondary);">
                                    🏆 <strong>Pilihan Terbanyak:</strong> <span style="color: #10b981; font-weight: 700;">{{ $winOpt->text }}</span>
                                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">{{ $wVotes }} dari {{ $tVotes }} suara ({{ $pct }}%)</div>
                                </div>
                            @endif
                        </div>
                        <div style="border-top: 1px solid var(--border-glass); padding-top: 0.75rem; display: flex; justify-content: space-between; align-items: center; font-size: 0.78rem;">
                            <span style="color: var(--text-muted);">Oleh: {{ $cPoll->creator->name ?? 'Pengurus' }}</span>
                            <a href="{{ route('polls.show', $cPoll->id) }}" style="color: var(--accent-1); font-weight: 600; text-decoration: none;">Rincian Suara &rarr;</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
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
