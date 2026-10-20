@extends('layouts.app')

@section('title', 'Data Keluarga - SIWIL AI')
@section('page-title', 'Data Keluarga')

@section('content')
<div style="max-width: 1000px; margin: 0 auto;">
    <h1 class="page-title">Data Keluarga (KK)</h1>

    <div class="data-card" style="margin-bottom: 1.5rem;">
        <div class="data-card-header">
            <span class="data-card-title">Informasi Kartu Keluarga</span>
        </div>
        <div class="data-card-body" style="padding: 1.25rem;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div>
                    <span style="display: block; font-size: 0.85rem; color: var(--text-muted);">No. Kartu Keluarga</span>
                    <strong style="font-size: 1.1rem;">{{ $user->kk_number }}</strong>
                </div>
                <div>
                    <span style="display: block; font-size: 0.85rem; color: var(--text-muted);">Status Data Keluarga</span>
                    @if($family)
                        <span class="badge badge-green">Terverifikasi</span>
                    @else
                        <span class="badge badge-yellow">Menunggu Verifikasi RT</span>
                    @endif
                </div>
                @if($family)
                    <div style="grid-column: span 2;">
                        <span style="display: block; font-size: 0.85rem; color: var(--text-muted);">Alamat</span>
                        <strong>{{ $family->address }}</strong>, RT {{ $family->rt->number ?? '-' }} / RW {{ $family->rt->rw->number ?? '-' }}, Desa {{ $family->rt->rw->village->name ?? '-' }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="data-card">
        <div class="data-card-header">
            <span class="data-card-title">Anggota Keluarga</span>
            <span class="badge badge-blue">{{ $members->count() }} Anggota</span>
        </div>
        @if($members->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>NIK</th>
                        <th>Nama Lengkap</th>
                        <th>Status Hubungan</th>
                        <th>Jenis Kelamin</th>
                        <th>Tempat, Tgl Lahir</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($members as $i => $member)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td style="font-weight: 600; font-family: monospace;">{{ $member->nik }}</td>
                        <td style="font-weight: 500;">
                            {{ $member->full_name }}
                            @if($member->nik === $user->username)
                                <span class="badge badge-blue" style="margin-left: 0.5rem; font-size: 0.7rem;">Anda</span>
                            @endif
                        </td>
                        <td>
                            @if($member->is_head_of_family)
                                <span class="badge badge-green">Kepala Keluarga</span>
                            @else
                                <span class="badge badge-gray">Anggota</span>
                            @endif
                        </td>
                        <td>{{ $member->gender === 'LAKI_LAKI' ? 'Laki-laki' : ($member->gender === 'PEREMPUAN' ? 'Perempuan' : '-') }}</td>
                        <td>
                            {{ $member->birth_place ?? '-' }}, 
                            {{ $member->birth_date ? \Carbon\Carbon::parse($member->birth_date)->format('d M Y') : '-' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width: 48px; height: 48px; margin-bottom: 1rem; color: #94a3b8;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <p>Data anggota keluarga belum tersedia.<br>Silakan hubungi RT setempat untuk mendaftarkan anggota keluarga Anda ke dalam sistem.</p>
            </div>
        @endif
    </div>
</div>
@endsection
