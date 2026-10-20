@extends('layouts.app')

@section('title', 'Data Desa - SIWIL AI')
@section('page-title', 'Data Desa')

@section('content')
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
        <h1 class="page-title" style="margin-bottom: 0;">Data Desa</h1>
        <a href="{{ route('villages.create') }}" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;"><path d="M12 5v14M5 12h14"/></svg>
            Tambah Desa
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Search --}}
    <div class="data-card" style="margin-bottom: 1rem;">
        <div style="padding: 0.75rem 1.25rem;">
            <form method="GET" action="{{ route('villages.index') }}" data-ajax-filter="true" data-ajax-target="#villagesCardContainer" data-count-badge="#villagesCountBadge" style="display: flex; gap: 0.75rem; align-items: center;">
                <input type="text" name="search" class="form-input" placeholder="Cari nama atau kode desa..." value="{{ request('search') }}" style="max-width: 320px;">
                <button type="submit" class="btn btn-primary btn-sm">Cari</button>
                @if(request('search'))
                    <a href="{{ route('villages.index') }}" class="btn btn-outline btn-sm reset-filter-btn">Reset</a>
                @endif
            </form>
        </div>
    </div>

    <div class="data-card" id="villagesCardContainer">
        <div class="data-card-header">
            <span class="data-card-title">Daftar Desa</span>
            <span id="villagesCountBadge" class="badge badge-blue">{{ $villages->total() }} total</span>
        </div>
        @if($villages->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Desa</th>
                        <th>Kode</th>
                        <th>Jumlah RW</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($villages as $i => $village)
                    <tr>
                        <td>{{ $villages->firstItem() + $i }}</td>
                        <td style="font-weight: 500; color: var(--text-primary);">{{ $village->name }}</td>
                        <td><span class="badge badge-gray">{{ $village->code ?? '-' }}</span></td>
                        <td>{{ $village->rws_count }}</td>
                        <td>{{ $village->created_at->format('d M Y') }}</td>
                        <td>
                            <div style="display: flex; gap: 0.5rem; align-items: center;">
                                <a href="{{ route('villages.edit', $village) }}" class="btn-icon btn-icon-edit" title="Edit Desa" aria-label="Edit Desa">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                </a>
                                <form method="POST" action="{{ route('villages.destroy', $village) }}" onsubmit="return confirm('Yakin ingin menghapus desa ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-icon btn-icon-danger" title="Hapus Desa" aria-label="Hapus Desa">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            <line x1="10" y1="11" x2="10" y2="17"></line>
                                            <line x1="14" y1="11" x2="14" y2="17"></line>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="padding: 1rem 1.25rem;">
                {{ $villages->withQueryString()->links() }}
            </div>
        @else
            <div class="empty-state">Belum ada data desa.</div>
        @endif
    </div>
@endsection
