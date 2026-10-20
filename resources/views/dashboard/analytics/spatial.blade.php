@extends('layouts.app')

@section('title', 'Analitik Spasial & Peringatan Dini')
@section('page-title', 'Analitik Spasial')

@section('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    #map { height: 500px; width: 100%; z-index: 1; }
    .alert-banner {
        background: #fef2f2;
        border-left: 4px solid #ef4444;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }
</style>
@endsection

@section('content')

@if($unresolvedEmergencies > 0)
<div class="alert-banner">
    <div style="background: #ef4444; color: white; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
    </div>
    <div>
        <h4 style="margin: 0; color: #991b1b; font-weight: 600;">PERINGATAN DINI: Darurat Aktif!</h4>
        <p style="margin: 0; color: #b91c1c;">Terdapat {{ $unresolvedEmergencies }} laporan darurat (SOS) yang belum diselesaikan. Segera tindak lanjuti!</p>
    </div>
</div>
@endif

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <div class="stat-card" style="border-top: 4px solid #3b82f6;">
        <div style="color: var(--text-muted);">Total Pengaduan (30 Hari)</div>
        <div class="stat-value" style="color: #3b82f6;">{{ $recentComplaints }}</div>
    </div>
    <div class="stat-card" style="border-top: 4px solid #ef4444;">
        <div style="color: var(--text-muted);">Total Darurat (30 Hari)</div>
        <div class="stat-value" style="color: #ef4444;">{{ $emergencies->count() }}</div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Peta Sebaran Laporan (Heatmap Spasial)</h6>
    </div>
    <div class="card-body">
        <div id="map"></div>
        
        <div style="display: flex; gap: 1.5rem; margin-top: 1rem; justify-content: center;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <div style="width: 16px; height: 16px; border-radius: 50%; background: #3b82f6;"></div>
                <span>Pengaduan Warga</span>
            </div>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <div style="width: 16px; height: 16px; border-radius: 50%; background: #ef4444;"></div>
                <span>Panggilan Darurat (SOS)</span>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const map = L.map('map').setView([-6.200000, 106.816666], 13);
        
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);

        const complaints = {!! json_encode($complaints) !!};
        const emergencies = {!! json_encode($emergencies) !!};
        const markers = [];

        // Plot Complaints
        complaints.forEach(c => {
            if (c.location_lat && c.location_lng) {
                const lat = parseFloat(c.location_lat);
                const lng = parseFloat(c.location_lng);
                if(!isNaN(lat) && !isNaN(lng)) {
                    const icon = L.divIcon({
                        className: 'custom-div-icon',
                        html: `<div style="background-color: #3b82f6; width: 16px; height: 16px; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 4px rgba(0,0,0,0.5);"></div>`,
                        iconSize: [16, 16],
                        iconAnchor: [8, 8]
                    });
                    const marker = L.marker([lat, lng], {icon: icon}).addTo(map);
                    marker.bindPopup(`<strong>Pengaduan:</strong><br>${c.description}<br><small>${c.status}</small>`);
                    markers.push(marker);
                }
            }
        });

        // Plot Emergencies
        emergencies.forEach(e => {
            if (e.location_lat && e.location_lng) {
                const lat = parseFloat(e.location_lat);
                const lng = parseFloat(e.location_lng);
                if(!isNaN(lat) && !isNaN(lng)) {
                    const icon = L.divIcon({
                        className: 'custom-div-icon',
                        html: `<div style="background-color: #ef4444; width: 24px; height: 24px; border-radius: 50%; border: 3px solid white; box-shadow: 0 0 8px rgba(239,68,68,0.8); ${e.status === 'ACTIVE' ? 'animation: pulse 1s infinite alternate;' : ''}"></div>`,
                        iconSize: [24, 24],
                        iconAnchor: [12, 12]
                    });
                    const marker = L.marker([lat, lng], {icon: icon}).addTo(map);
                    marker.bindPopup(`<strong>Darurat (SOS)</strong><br>${e.type}<br><small>${e.status}</small>`);
                    markers.push(marker);
                }
            }
        });

        // Fit map bounds
        if (markers.length > 0) {
            const group = new L.featureGroup(markers);
            map.fitBounds(group.getBounds().pad(0.1));
        }

        // Add pulse animation style
        const style = document.createElement('style');
        style.innerHTML = `
            @keyframes pulse {
                0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
                70% { transform: scale(1.1); box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); }
                100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
            }
        `;
        document.head.appendChild(style);
    });
</script>
@endsection
