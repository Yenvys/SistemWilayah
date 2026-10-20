@extends('layouts.public')

@section('title', 'Statistik Kependudukan - Desa ' . ($village->name ?? ''))
@section('page-title', 'Statistik Demografi Desa ' . ($village->name ?? ''))
@section('page-subtitle', 'Informasi terpadu statistik kependudukan Desa ' . ($village->name ?? '') . '.')

@section('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endsection

@section('content')
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
    <div class="card" style="text-align: center; background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(56, 189, 248, 0.1)); border-color: var(--accent-1);">
        <h3 style="color: var(--accent-1); font-size: 1.1rem; margin-bottom: 0.5rem;">Total Penduduk</h3>
        <p style="font-size: 2.5rem; font-weight: 800; color: var(--text-primary);">{{ number_format($totalResidents, 0, ',', '.') }} <span style="font-size: 1rem; font-weight: 500; color: var(--text-muted);">Jiwa</span></p>
    </div>
    
    <div class="card" style="text-align: center;">
        <h3 style="color: var(--text-muted); font-size: 1.1rem; margin-bottom: 0.5rem;">Laki-laki</h3>
        <p style="font-size: 2rem; font-weight: 700; color: #3b82f6;">{{ number_format($maleCount, 0, ',', '.') }}</p>
        <span style="font-size: 0.9rem; color: var(--text-muted);">
            {{ $totalResidents > 0 ? round(($maleCount / $totalResidents) * 100, 1) : 0 }}%
        </span>
    </div>
    
    <div class="card" style="text-align: center;">
        <h3 style="color: var(--text-muted); font-size: 1.1rem; margin-bottom: 0.5rem;">Perempuan</h3>
        <p style="font-size: 2rem; font-weight: 700; color: #ec4899;">{{ number_format($femaleCount, 0, ',', '.') }}</p>
        <span style="font-size: 0.9rem; color: var(--text-muted);">
            {{ $totalResidents > 0 ? round(($femaleCount / $totalResidents) * 100, 1) : 0 }}%
        </span>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 2rem; margin-bottom: 2rem;">
    <div class="card">
        <h2 style="margin-bottom: 1.5rem; font-size: 1.25rem;">Demografi Usia (Generasi)</h2>
        <div style="position: relative; height: 300px;">
            <canvas id="ageChart"></canvas>
        </div>
    </div>
    
    <div class="card">
        <h2 style="margin-bottom: 1.5rem; font-size: 1.25rem;">Tingkat Pendidikan</h2>
        <div style="position: relative; height: 300px;">
            <canvas id="educationChart"></canvas>
        </div>
    </div>
</div>

<div class="card" style="margin-bottom: 3rem;">
    <h2 style="margin-bottom: 1.5rem; font-size: 1.25rem;">10 Pekerjaan Terbanyak</h2>
    <div style="position: relative; height: 350px;">
        <canvas id="occupationChart"></canvas>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Colors
    const primaryColors = ['#6366f1', '#8b5cf6', '#ec4899', '#f43f5e', '#f59e0b', '#10b981', '#3b82f6', '#06b6d4', '#14b8a6', '#84cc16'];
    
    // Age Chart
    const ageCtx = document.getElementById('ageChart');
    if(ageCtx) {
        new Chart(ageCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode(array_keys($ageStats)) !!},
                datasets: [{
                    label: 'Jumlah Penduduk',
                    data: {!! json_encode(array_values($ageStats)) !!},
                    backgroundColor: '#8b5cf6',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // Education Chart
    const eduCtx = document.getElementById('educationChart');
    if(eduCtx) {
        new Chart(eduCtx, {
            type: 'pie',
            data: {
                labels: {!! json_encode($educationStats->pluck('education')->map(fn($v) => $v ?: 'Tidak Diketahui')) !!},
                datasets: [{
                    data: {!! json_encode($educationStats->pluck('total')) !!},
                    backgroundColor: primaryColors,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right', labels: { font: { family: "'Outfit', sans-serif" } } }
                }
            }
        });
    }

    // Occupation Chart
    const occCtx = document.getElementById('occupationChart');
    if(occCtx) {
        new Chart(occCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($occupationStats->pluck('occupation')->map(fn($v) => $v ?: 'Belum/Tidak Bekerja')) !!},
                datasets: [{
                    label: 'Jumlah',
                    data: {!! json_encode($occupationStats->pluck('total')) !!},
                    backgroundColor: '#3b82f6',
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y', // Horizontal bar chart
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                    y: { grid: { display: false } }
                }
            }
        });
    }
});
</script>
@endsection
