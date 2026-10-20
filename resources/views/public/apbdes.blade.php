@extends('layouts.public')

@section('title', 'Transparansi APBDes - Desa ' . ($village->name ?? ''))
@section('page-title', 'Transparansi APBDes Desa ' . ($village->name ?? ''))
@section('page-subtitle', 'Anggaran Pendapatan dan Belanja Desa (APBDes) Tahun ' . $year)

@section('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endsection

@section('content')
<div style="display: flex; justify-content: flex-end; margin-bottom: 2rem;">
    <form action="{{ route('public.apbdes') }}" method="GET" style="display: flex; gap: 1rem; align-items: center;">
        <label for="year" style="font-weight: 500;">Pilih Tahun:</label>
        <select name="year" id="year" onchange="this.form.submit()" style="padding: 0.5rem 1rem; border-radius: 8px; border: 1px solid var(--border-glass); outline: none; font-family: inherit;">
            @foreach($years as $y)
                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
        </select>
    </form>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
    <div class="card" style="border-top: 4px solid #10b981;">
        <h3 style="color: var(--text-muted); font-size: 1rem; margin-bottom: 0.5rem;">Total Pendapatan</h3>
        <p style="font-size: 1.75rem; font-weight: 700; color: #10b981;">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</p>
    </div>
    <div class="card" style="border-top: 4px solid #ef4444;">
        <h3 style="color: var(--text-muted); font-size: 1rem; margin-bottom: 0.5rem;">Total Belanja</h3>
        <p style="font-size: 1.75rem; font-weight: 700; color: #ef4444;">Rp {{ number_format($totalBelanja, 0, ',', '.') }}</p>
    </div>
    <div class="card" style="border-top: 4px solid #3b82f6;">
        <h3 style="color: var(--text-muted); font-size: 1rem; margin-bottom: 0.5rem;">Total Pembiayaan</h3>
        <p style="font-size: 1.75rem; font-weight: 700; color: #3b82f6;">Rp {{ number_format($totalPembiayaan, 0, ',', '.') }}</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 3rem;">
    <div class="card">
        <h2 style="margin-bottom: 1.5rem; font-size: 1.25rem;">Proporsi Anggaran</h2>
        <div style="position: relative; height: 300px;">
            <canvas id="apbdesChart"></canvas>
        </div>
    </div>
    
    <div class="card">
        <h2 style="margin-bottom: 1.5rem; font-size: 1.25rem;">Rincian Pendapatan</h2>
        @if($pendapatan->count() > 0)
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                @foreach($pendapatan as $item)
                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-glass); padding-bottom: 0.5rem;">
                    <div>
                        <div style="font-weight: 500;">{{ $item->category }}</div>
                        <div style="font-size: 0.85rem; color: var(--text-muted);">{{ $item->description }}</div>
                    </div>
                    <div style="font-weight: 600;">Rp {{ number_format($item->amount_budget, 0, ',', '.') }}</div>
                </div>
                @endforeach
            </div>
        @else
            <p style="color: var(--text-muted); text-align: center; padding: 2rem 0;">Belum ada data pendapatan tahun {{ $year }}</p>
        @endif
    </div>
</div>

<div class="card">
    <h2 style="margin-bottom: 1.5rem; font-size: 1.25rem;">Rincian Belanja</h2>
    @if($belanja->count() > 0)
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: var(--bg-body); text-align: left;">
                    <th style="padding: 1rem; border-bottom: 1px solid var(--border-glass);">Kategori/Bidang</th>
                    <th style="padding: 1rem; border-bottom: 1px solid var(--border-glass);">Anggaran</th>
                    <th style="padding: 1rem; border-bottom: 1px solid var(--border-glass);">Realisasi</th>
                    <th style="padding: 1rem; border-bottom: 1px solid var(--border-glass);">%</th>
                </tr>
            </thead>
            <tbody>
                @foreach($belanja as $item)
                @php
                    $percent = $item->amount_budget > 0 ? round(($item->amount_realized / $item->amount_budget) * 100) : 0;
                @endphp
                <tr>
                    <td style="padding: 1rem; border-bottom: 1px solid var(--border-glass);">
                        <div style="font-weight: 500;">{{ $item->category }}</div>
                        <div style="font-size: 0.85rem; color: var(--text-muted);">{{ $item->description }}</div>
                    </td>
                    <td style="padding: 1rem; border-bottom: 1px solid var(--border-glass); font-weight: 500;">Rp {{ number_format($item->amount_budget, 0, ',', '.') }}</td>
                    <td style="padding: 1rem; border-bottom: 1px solid var(--border-glass); color: #10b981; font-weight: 500;">Rp {{ number_format($item->amount_realized, 0, ',', '.') }}</td>
                    <td style="padding: 1rem; border-bottom: 1px solid var(--border-glass);">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="flex-grow: 1; height: 8px; background: var(--border-glass); border-radius: 4px; overflow: hidden;">
                                <div style="height: 100%; width: {{ $percent }}%; background: #10b981;"></div>
                            </div>
                            <span style="font-size: 0.85rem; font-weight: 600;">{{ $percent }}%</span>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="color: var(--text-muted); text-align: center; padding: 3rem 0;">Belum ada data belanja tahun {{ $year }}</p>
    @endif
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('apbdesChart');
    if(ctx) {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Pendapatan', 'Belanja', 'Pembiayaan'],
                datasets: [{
                    data: [{{ $totalPendapatan }}, {{ $totalBelanja }}, {{ $totalPembiayaan }}],
                    backgroundColor: ['#10b981', '#ef4444', '#3b82f6'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { font: { family: "'Outfit', sans-serif" } }
                    }
                },
                cutout: '70%'
            }
        });
    }
});
</script>
@endsection
