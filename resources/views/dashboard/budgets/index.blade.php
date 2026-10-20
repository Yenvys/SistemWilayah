@extends('layouts.app')

@section('title', 'APBDes - SIWIL AI')
@section('page-title', 'Data APBDes')

@section('content')
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-title">Total Pendapatan</span>
            <div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>
        </div>
        <div class="stat-value">Rp {{ number_format($budgets->where('type', 'PENDAPATAN')->sum('amount_budget'), 0, ',', '.') }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-title">Total Belanja</span>
            <div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>
        </div>
        <div class="stat-value">Rp {{ number_format($budgets->where('type', 'BELANJA')->sum('amount_budget'), 0, ',', '.') }}</div>
    </div>
</div>

<div class="data-card">
    <div class="data-card-header">
        <h3 class="data-card-title">Manajemen APBDes</h3>
    </div>
    <div class="data-card-body">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Tahun</th>
                        <th>Tipe</th>
                        <th>Kategori</th>
                        <th>Anggaran</th>
                        <th>Realisasi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($budgets as $budget)
                        <tr>
                            <td>{{ $budget->year }}</td>
                            <td><span class="badge {{ $budget->type === 'PENDAPATAN' ? 'badge-green' : 'badge-red' }}">{{ $budget->type }}</span></td>
                            <td>{{ $budget->category }}</td>
                            <td>Rp {{ number_format($budget->amount_budget, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($budget->amount_realized, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty-state">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:48px;height:48px;margin-bottom:1rem;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                <div>Belum ada data APBDes</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top: 1rem;">
            {{ $budgets->links() }}
        </div>
    </div>
</div>
@endsection
