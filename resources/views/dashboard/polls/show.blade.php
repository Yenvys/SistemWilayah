@extends('layouts.app')

@section('title', 'Hasil Polling - SIWIL AI')
@section('page-title', 'Detail Polling')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h1 class="page-title" style="margin-bottom: 0;">{{ $poll->title }}</h1>
        <a href="{{ route('polls.index') }}" class="btn btn-outline">Kembali</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger" style="background: rgba(239,68,68,0.1); border-color: var(--danger); color: var(--danger);">{{ session('error') }}</div>
    @endif

    @php
        $isExpired = $poll->expires_at && $poll->expires_at->isPast();
        $isActive = $poll->status === 'ACTIVE' && !$isExpired;
        $winningOption = $poll->winning_option;
        $canClose = $isActive && ($poll->created_by === Auth::id() || in_array(Auth::user()->role, ['SUPER_ADMIN', 'ADMIN_DESA']));
    @endphp

    @if(!$isActive)
        <div style="background: rgba(34, 197, 94, 0.08); border: 1px solid rgba(34, 197, 94, 0.25); border-radius: 12px; padding: 1.25rem 1.5rem; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(34, 197, 94, 0.15); color: #16a34a; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width: 20px; height: 20px;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </div>
                <div>
                    <h4 style="margin: 0; font-size: 1.05rem; color: var(--text-primary); font-weight: 700;">Musyawarah Telah Selesai</h4>
                    <p style="margin: 0.2rem 0 0 0; font-size: 0.85rem; color: var(--text-muted);">
                        @if($winningOption && $totalVotes > 0)
                            Hasil keputusan terbanyak: <strong style="color: #16a34a;">"{{ $winningOption->text }}"</strong> dengan perolehan {{ $winningOption->votes->count() }} suara ({{ round(($winningOption->votes->count() / $totalVotes) * 100) }}%).
                        @else
                            Musyawarah telah ditutup. Belum ada suara yang masuk.
                        @endif
                    </p>
                </div>
            </div>
            <span class="badge badge-green" style="font-size: 0.8rem; padding: 0.4rem 0.85rem;">Status: CLOSED (Selesai)</span>
        </div>
    @endif

    <div class="dashboard-grid">
        {{-- Info Card --}}
        <div class="data-card">
            <div class="data-card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h2 class="data-card-title">Informasi Musyawarah</h2>
                <span class="badge {{ $isActive ? 'badge-green' : 'badge-red' }}">{{ $isActive ? 'SEDANG AKTIF' : 'SELESAI / TUTUP' }}</span>
            </div>
            <div class="data-card-body">
                <table style="width: 100%; border: none;">
                    <tr style="border-bottom: 1px solid var(--border-glass);">
                        <td style="width: 38%; color: var(--text-secondary); padding: 10px 0;">Cakupan Wilayah</td>
                        <td style="font-weight: 600; padding: 10px 0;">
                            @if($poll->level === 'RT')
                                <span class="badge badge-yellow" style="font-size: 0.75rem;">Tingkat RT</span>
                                RT {{ $poll->rt->number ?? '-' }} / RW {{ $poll->rw->number ?? ($poll->rt->rw->number ?? '-') }}
                            @elseif($poll->level === 'RW')
                                <span class="badge badge-blue" style="font-size: 0.75rem;">Tingkat RW</span>
                                RW {{ $poll->rw->number ?? '-' }}
                            @else
                                <span class="badge badge-green" style="font-size: 0.75rem;">Tingkat Desa</span>
                                Seluruh Desa {{ $poll->village->name ?? '' }}
                            @endif
                        </td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--border-glass);">
                        <td style="color: var(--text-secondary); padding: 10px 0;">Penyelenggara</td>
                        <td style="font-weight: 600; padding: 10px 0;">
                            {{ $poll->creator->name ?? ($poll->creator->username ?? 'Pengurus Wilayah') }}
                            <small style="color: var(--text-muted); font-weight: normal;">({{ $poll->creator->role ?? '' }})</small>
                        </td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--border-glass);">
                        <td style="color: var(--text-secondary); padding: 10px 0;">Waktu Diterbitkan</td>
                        <td style="font-weight: 600; padding: 10px 0;">{{ $poll->created_at->format('d M Y, H:i') }}</td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--border-glass);">
                        <td style="color: var(--text-secondary); padding: 10px 0;">Batas Waktu Voting</td>
                        <td style="font-weight: 600; padding: 10px 0;">{{ $poll->expires_at ? $poll->expires_at->format('d M Y') : 'Hingga Diselesaikan Pengurus' }}</td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--border-glass);">
                        <td style="color: var(--text-secondary); padding: 10px 0;">Total Suara Masuk</td>
                        <td style="font-weight: 700; color: var(--accent-1); padding: 10px 0;">{{ $totalVotes }} Suara Warga</td>
                    </tr>
                    @if($poll->description)
                    <tr>
                        <td style="color: var(--text-secondary); padding: 10px 0; vertical-align: top;">Pokok Bahasan</td>
                        <td style="padding: 10px 0; line-height: 1.5; color: var(--text-primary);">{{ $poll->description }}</td>
                    </tr>
                    @endif
                </table>

                @if($canClose)
                    <div style="margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px dashed var(--border-glass);">
                        <form id="closePollForm" action="{{ route('polls.close', $poll->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="button" onclick="confirmClosePoll()" class="btn btn-outline" style="width: 100%; border-color: var(--warning); color: var(--warning); display: flex; align-items: center; justify-content: center; gap: 0.5rem; font-weight: 600;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="9" y1="9" x2="15" y2="15"></line><line x1="15" y1="9" x2="9" y2="15"></line></svg>
                                Selesaikan Musyawarah & Umumkan Hasil ke Warga
                            </button>
                        </form>
                        <script>
                        async function confirmClosePoll() {
                            const ok = await window.siwilConfirm({
                                title: 'Selesaikan Musyawarah?',
                                message: 'Apakah Anda yakin ingin menyelesaikan musyawarah ini sekarang? Notifikasi hasil keputusan akhir akan otomatis disiarkan ke seluruh warga di wilayah terkait.',
                                confirmText: 'Ya, Selesaikan & Umumkan',
                                type: 'warning'
                            });
                            if (ok) {
                                document.getElementById('closePollForm').submit();
                            }
                        }
                        </script>
                    </div>
                @endif
            </div>
        </div>

        {{-- Voting / Results Card --}}
        <div class="data-card">
            <div class="data-card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h2 class="data-card-title">
                    @if($userVoted && $isActive)
                        Hasil Sementara Musyawarah
                    @elseif(!$isActive)
                        Rekapitulasi Hasil Akhir
                    @else
                        Form Partisipasi Suara Warga
                    @endif
                </h2>
                @if($userVoted)
                    <span class="badge badge-blue">Anda Telah Memilih</span>
                @endif
            </div>
            <div class="data-card-body">
                @if($userVoted || !$isActive)
                    {{-- Tampilan Rekapitulasi Suara --}}
                    <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                        @foreach($poll->options as $option)
                            @php
                                $voteCount = $option->votes->count();
                                $percent = $totalVotes > 0 ? round(($voteCount / $totalVotes) * 100) : 0;
                                $isSelected = $userVoted && $userVoted->poll_option_id === $option->id;
                                $isWinner = !$isActive && $winningOption && $winningOption->id === $option->id && $voteCount > 0;
                            @endphp
                            <div style="background: {{ $isSelected ? 'rgba(99, 102, 241, 0.04)' : ($isWinner ? 'rgba(34, 197, 94, 0.04)' : 'transparent') }}; padding: 0.75rem 1rem; border-radius: 10px; border: 1px solid {{ $isWinner ? 'rgba(34, 197, 94, 0.3)' : ($isSelected ? 'rgba(99, 102, 241, 0.25)' : 'var(--border-glass)') }};">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem; flex-wrap: wrap; gap: 0.25rem;">
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <span style="font-weight: 700; color: var(--text-primary); font-size: 1rem;">
                                            {{ $option->text }}
                                        </span>
                                        @if($isSelected)
                                            <span class="badge badge-blue" style="font-size: 0.7rem;">Pilihan Anda</span>
                                        @endif
                                        @if($isWinner)
                                            <span class="badge badge-green" style="font-size: 0.7rem;">Hasil Terbanyak</span>
                                        @endif
                                    </div>
                                    <div style="font-weight: 700; color: {{ $isWinner ? '#16a34a' : 'var(--accent-1)' }}; font-size: 0.95rem;">
                                        {{ $percent }}% <span style="font-size: 0.8rem; font-weight: normal; color: var(--text-muted);">({{ $voteCount }} Suara)</span>
                                    </div>
                                </div>
                                <div style="width: 100%; height: 20px; background: var(--bg-body); border-radius: 6px; overflow: hidden; border: 1px solid var(--border-glass);">
                                    <div style="height: 100%; width: {{ $percent }}%; background: {{ $isWinner ? 'linear-gradient(90deg, #22c55e, #16a34a)' : ($isSelected ? 'var(--accent-gradient)' : 'linear-gradient(90deg, #94a3b8, #cbd5e1)') }}; border-radius: 6px; transition: width 0.6s ease;"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($userVoted && $isActive)
                        <div style="margin-top: 1.5rem; text-align: center; color: var(--text-muted); font-size: 0.85rem; padding: 0.75rem; background: var(--bg-body); border-radius: 8px;">
                            Suara Anda telah tercatat dengan aman. Hasil akhir resmi akan diumumkan melalui notifikasi ketika musyawarah ditutup oleh pengurus.
                        </div>
                    @endif
                @else
                    {{-- Form Pemilihan Suara (Voting) untuk Warga --}}
                    <form action="{{ route('polls.vote', $poll->id) }}" method="POST">
                        @csrf
                        <div style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 1.25rem;">
                            Silakan pilih salah satu opsi jawaban di bawah ini untuk berpartisipasi dalam musyawarah bersama:
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 0.85rem;">
                            @foreach($poll->options as $index => $option)
                                <label style="display: flex; align-items: center; gap: 1rem; padding: 1rem 1.25rem; border: 2px solid var(--border-glass); border-radius: 12px; cursor: pointer; transition: all 0.2s;" 
                                    onmouseover="this.style.borderColor='var(--accent-1)'; this.style.background='rgba(99,102,241,0.04)';" 
                                    onmouseout="this.style.borderColor='var(--border-glass)'; this.style.background='transparent';">
                                    <input type="radio" name="option_id" value="{{ $option->id }}" required style="width: 20px; height: 20px; accent-color: var(--accent-1);">
                                    <div>
                                        <span style="font-weight: 700; font-size: 1.05rem; color: var(--text-primary);">{{ $option->text }}</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        <button type="submit" class="btn btn-primary" style="margin-top: 1.5rem; width: 100%; padding: 0.85rem; font-weight: 700; font-size: 1rem; box-shadow: 0 4px 14px rgba(99, 102, 241, 0.3);">
                            Kirimkan Suara Pilihan Saya
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endsection
