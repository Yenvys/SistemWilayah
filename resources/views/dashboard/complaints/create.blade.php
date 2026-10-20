@extends('layouts.app')

@section('title', 'Buat Pengaduan - SIWIL AI')
@section('page-title', 'Buat Pengaduan')

@section('content')
    <div class="data-card" style="max-width: 800px; margin: 0 auto;">
        <div class="data-card-header">
            <span class="data-card-title">Form Laporan Pengaduan</span>
        </div>
        <div style="padding: 2rem;">
            <form action="{{ route('complaints.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="form-group">
                    <label class="form-label">Judul Pengaduan</label>
                    <input type="text" name="title" class="form-input" required placeholder="Misal: Jalan Berlubang di RT 03">
                </div>

                <div class="form-group">
                    <label class="form-label">Deskripsi Lengkap</label>
                    <textarea name="description" class="form-textarea" required placeholder="Jelaskan detail permasalahan..."></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Lokasi Kejadian (Geotag)</label>
                    <div style="display: flex; gap: 0.5rem;">
                        <input type="text" id="location" name="location" class="form-input" placeholder="Tulis alamat atau gunakan tombol lokasi">
                        <button type="button" id="btn-location" class="btn btn-outline" style="white-space: nowrap;">📍 Ambil Lokasi</button>
                    </div>
                    <small id="location-status" style="color: var(--text-muted); display: block; margin-top: 0.5rem;"></small>
                </div>

                <div class="form-group">
                    <label class="form-label">Unggah Foto Bukti</label>
                    <input type="file" name="photo" class="form-input" accept="image/*">
                    <div style="margin-top: 0.5rem; font-size: 0.8rem; color: var(--text-muted);">Format: JPG, PNG. Maksimal 2MB. (Wajib jika ada)</div>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary">Kirim Laporan</button>
                    <a href="{{ route('complaints.index') }}" class="btn btn-outline">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    document.getElementById('btn-location').addEventListener('click', function() {
        const status = document.getElementById('location-status');
        const locInput = document.getElementById('location');
        
        if (!navigator.geolocation) {
            status.textContent = 'Geolocation tidak didukung oleh browser Anda.';
            status.style.color = 'var(--danger)';
            return;
        }

        status.textContent = 'Mencari lokasi...';
        status.style.color = 'var(--accent-1)';
        
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                locInput.value = lat + ', ' + lng;
                status.textContent = 'Lokasi berhasil didapatkan!';
                status.style.color = 'var(--success)';
            },
            function(error) {
                status.textContent = 'Gagal mendapatkan lokasi: ' + error.message;
                status.style.color = 'var(--danger)';
            }
        );
    });
</script>
@endsection
