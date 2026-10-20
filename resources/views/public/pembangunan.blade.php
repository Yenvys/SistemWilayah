@extends('layouts.public')

@section('title', 'Peta Pembangunan - Desa ' . ($village->name ?? ''))
@section('page-title', 'Peta Pembangunan Desa ' . ($village->name ?? ''))
@section('page-subtitle', 'Transparansi proyek pembangunan infrastruktur Desa ' . ($village->name ?? '') . '.')

@section('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    #map { height: 600px; width: 100%; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border: 1px solid var(--border-glass); z-index: 1; }
    .project-card { margin-bottom: 1.5rem; border-left: 4px solid var(--accent-1); transition: transform 0.2s; }
    .project-card:hover { transform: translateY(-3px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); cursor: pointer; }
    .progress-bar-container { width: 100%; height: 8px; background: var(--border-glass); border-radius: 4px; margin-top: 0.5rem; overflow: hidden; }
    .progress-bar { height: 100%; background: var(--accent-1); border-radius: 4px; }
</style>
@endsection

@section('content')
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; margin-bottom: 3rem;">
    <!-- Map Container -->
    <div>
        <div id="map"></div>
    </div>

    <!-- Project List -->
    <div style="max-height: 600px; overflow-y: auto; padding-right: 0.5rem;">
        <h2 style="font-size: 1.25rem; margin-bottom: 1.5rem; position: sticky; top: 0; background: var(--bg-body); padding: 0.5rem 0; z-index: 2;">Daftar Proyek Pembangunan</h2>
        
        @if($developments->count() > 0)
            @foreach($developments as $dev)
            <div class="card project-card" onclick="focusMap({{ $dev->location_lat ?? 0 }}, {{ $dev->location_lng ?? 0 }})">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                    <h3 style="font-size: 1.1rem; color: var(--text-primary); margin-bottom: 0.25rem;">{{ $dev->title }}</h3>
                    <span class="badge" style="font-size: 0.75rem; padding: 0.2rem 0.5rem; border-radius: 4px; 
                        background: {{ $dev->status === 'COMPLETED' ? '#dcfce7' : ($dev->status === 'ONGOING' ? '#dbeafe' : '#fef3c7') }}; 
                        color: {{ $dev->status === 'COMPLETED' ? '#166534' : ($dev->status === 'ONGOING' ? '#1e40af' : '#92400e') }};">
                        {{ $dev->status }}
                    </span>
                </div>
                
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                    {{ $dev->description }}
                </p>
                
                <div style="font-size: 0.9rem; margin-bottom: 0.25rem;">
                    <strong>Anggaran:</strong> Rp {{ number_format($dev->budget_allocated, 0, ',', '.') }}
                </div>
                <div style="font-size: 0.9rem; margin-bottom: 0.75rem;">
                    <strong>Sumber Dana:</strong> {{ $dev->source_of_fund }}
                </div>
                
                <div style="display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 600;">
                    <span>Progres</span>
                    <span>{{ $dev->progress_percentage }}%</span>
                </div>
                <div class="progress-bar-container">
                    <div class="progress-bar" style="width: {{ $dev->progress_percentage }}%; background: {{ $dev->progress_percentage == 100 ? '#10b981' : 'var(--accent-1)' }};"></div>
                </div>
            </div>
            @endforeach
        @else
            <div class="card" style="text-align: center; color: var(--text-muted); padding: 3rem 1rem;">
                Belum ada data proyek pembangunan.
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    let map;
    
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize map (Default focus to Indonesia roughly if no points, else calculate bounds)
        map = L.map('map').setView([-6.200000, 106.816666], 13); // Default Jakarta
        
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);

        const developments = {!! json_encode($developments) !!};
        const markers = [];

        developments.forEach(dev => {
            if (dev.location_lat && dev.location_lng) {
                const lat = parseFloat(dev.location_lat);
                const lng = parseFloat(dev.location_lng);
                
                if(!isNaN(lat) && !isNaN(lng)) {
                    let color = 'blue';
                    if(dev.status === 'COMPLETED') color = 'green';
                    if(dev.status === 'PLANNED') color = 'yellow';
                    
                    // Simple custom icon SVG based on status
                    const svgIcon = L.divIcon({
                        className: 'custom-div-icon',
                        html: `<div style="background-color: ${color === 'green' ? '#10b981' : (color === 'yellow' ? '#f59e0b' : '#3b82f6')}; width: 24px; height: 24px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>`,
                        iconSize: [24, 24],
                        iconAnchor: [12, 12]
                    });

                    const marker = L.marker([lat, lng], {icon: svgIcon}).addTo(map);
                    
                    // Create popup content
                    const popupContent = `
                        <div style="font-family: 'Inter', sans-serif; min-width: 200px;">
                            <h4 style="font-family: 'Outfit', sans-serif; font-size: 1.1rem; margin-bottom: 0.5rem;">${dev.title}</h4>
                            <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 0.5rem;">${dev.description || 'Tidak ada deskripsi'}</p>
                            <div style="font-size: 0.9rem; margin-bottom: 0.25rem;"><strong>Anggaran:</strong> Rp ${new Intl.NumberFormat('id-ID').format(dev.budget_allocated)}</div>
                            <div style="font-size: 0.9rem; margin-bottom: 0.5rem;"><strong>Progres:</strong> ${dev.progress_percentage}%</div>
                            <span style="font-size: 0.75rem; padding: 2px 6px; border-radius: 4px; background: #e2e8f0;">${dev.status}</span>
                        </div>
                    `;
                    
                    marker.bindPopup(popupContent);
                    markers.push(marker);
                }
            }
        });

        // Fit map bounds if there are markers
        if (markers.length > 0) {
            const group = new L.featureGroup(markers);
            map.fitBounds(group.getBounds().pad(0.1));
        }
    });

    function focusMap(lat, lng) {
        if (lat && lng && !isNaN(lat) && !isNaN(lng)) {
            map.setView([lat, lng], 16, {
                animate: true,
                duration: 1
            });
        }
    }
</script>
@endsection
