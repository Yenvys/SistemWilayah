@extends('layouts.app')

@section('title', 'Daftar Pengaduan - SIWIL AI')
@section('page-title', 'Pengaduan Masyarakat')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2 style="font-size: 1.5rem; color: var(--text-primary);">Daftar Pengaduan</h2>
        @if(Auth::user()->role === 'WARGA')
        <a href="{{ route('complaints.create') }}" class="btn btn-primary">Buat Pengaduan Baru</a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="data-card">
        <div class="data-card-header">
            <span class="data-card-title">Semua Pengaduan</span>
        </div>
        @if(isset($complaints) && $complaints->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>Pelapor</th>
                        <th>Judul Pengaduan</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($complaints as $complaint)
                    @php
                        $cData = [
                            'id' => $complaint->id,
                            'title' => $complaint->title,
                            'reporter' => $complaint->user->name ?? 'Anonim',
                            'status' => $complaint->status,
                            'status_badge' => match($complaint->status) {
                                'PROCESSING' => 'badge-blue',
                                'RESOLVED' => 'badge-green',
                                'REJECTED' => 'badge-red',
                                default => 'badge-yellow',
                            },
                            'created_at' => $complaint->created_at->format('d M Y, H:i'),
                            'description' => $complaint->description,
                            'location' => $complaint->location ?? '-',
                            'maps_url' => ($complaint->location && strpos($complaint->location, ',') !== false) 
                                ? 'https://www.google.com/maps/search/?api=1&query=' . urlencode(str_replace(' ', '', $complaint->location)) 
                                : null,
                            'photo_url' => $complaint->photo_path ? asset('storage/' . $complaint->photo_path) : null,
                            'can_manage' => Auth::user()->role !== 'WARGA',
                            'show_url' => route('complaints.show', $complaint->id),
                            'update_url' => route('complaints.update-status', $complaint->id),
                        ];
                    @endphp
                    <tr>
                        <td style="font-weight:500;">{{ $complaint->user->name ?? 'Anonim' }}</td>
                        <td>{{ $complaint->title }}</td>
                        <td>
                            <span class="badge 
                                @if($complaint->status == 'PENDING') badge-yellow 
                                @elseif($complaint->status == 'PROCESSING') badge-blue 
                                @elseif($complaint->status == 'RESOLVED') badge-green 
                                @else badge-red @endif">
                                {{ $complaint->status }}
                            </span>
                        </td>
                        <td>{{ $complaint->created_at->format('d M Y') }}</td>
                        <td>
                            <div style="display:inline-flex; gap:0.4rem; align-items:center;">
                                <button type="button" class="btn-icon btn-icon-view" onclick='openComplaintModal(@json($cData))' title="Detail & Tindak Lanjut" aria-label="Detail Pengaduan">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="padding: 1rem;">
                {{ $complaints->links() }}
            </div>
        @else
            <div style="padding: 3rem; text-align: center; color: var(--text-muted);">Belum ada data pengaduan.</div>
        @endif
    </div>

    {{-- Modal Popup Detail & Tindak Lanjut Pengaduan --}}
    <div id="complaintModalOverlay" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 9999; justify-content: center; align-items: center; padding: 1rem; animation: fadeIn 0.2s ease;">
        <div style="background: var(--bg-card); border: 1px solid var(--border-glass); border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); width: 100%; max-width: 620px; max-height: 90vh; overflow-y: auto; display: flex; flex-direction: column;">
            {{-- Modal Header --}}
            <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-glass); display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; background: var(--bg-card); z-index: 2;">
                <div>
                    <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--text-primary); margin: 0;">Detail Pengaduan Warga</h3>
                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.15rem;" id="modalComplaintDate">-</div>
                </div>
                <button type="button" onclick="closeComplaintModal()" style="background: transparent; border: none; font-size: 1.5rem; cursor: pointer; color: var(--text-muted); line-height: 1; padding: 4px;">&times;</button>
            </div>

            {{-- Modal Body --}}
            <div style="padding: 1.5rem;">
                {{-- Status & Pelapor --}}
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; padding: 0.75rem 1rem; border-radius: 10px; background: rgba(37, 99, 235, 0.05); border: 1px solid var(--border-glass);">
                    <div>
                        <span style="font-size: 0.78rem; color: var(--text-muted);">Pelapor:</span>
                        <div style="font-weight: 700; font-size: 0.95rem; color: var(--text-primary);" id="modalComplaintReporter">-</div>
                    </div>
                    <div>
                        <span id="modalComplaintStatusBadge" class="badge">PENDING</span>
                    </div>
                </div>

                {{-- Judul & Deskripsi --}}
                <div style="margin-bottom: 1.25rem;">
                    <label style="font-size: 0.78rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 0.35rem;">
                        Judul Laporan
                    </label>
                    <div id="modalComplaintTitle" style="font-size: 1.05rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.85rem;">
                        -
                    </div>

                    <label style="font-size: 0.78rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 0.35rem;">
                        Deskripsi Masalah
                    </label>
                    <div id="modalComplaintDesc" style="font-size: 0.9rem; color: var(--text-secondary); line-height: 1.5; background: var(--bg-body); padding: 0.85rem 1rem; border-radius: 10px; border: 1px solid var(--border-glass); white-space: pre-wrap;">
                        -
                    </div>
                </div>

                {{-- Lokasi --}}
                <div style="margin-bottom: 1.25rem; display: flex; justify-content: space-between; align-items: center; background: var(--bg-body); padding: 0.75rem 1rem; border-radius: 10px; border: 1px solid var(--border-glass);">
                    <div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">Lokasi Kejadian</div>
                        <div id="modalComplaintLocation" style="font-weight: 600; font-size: 0.88rem; color: var(--text-primary);">-</div>
                    </div>
                    <a id="modalComplaintMapsBtn" href="#" target="_blank" class="btn btn-outline btn-sm" style="display: none; font-size: 0.78rem; padding: 0.35rem 0.65rem;">
                        Buka di Maps &rarr;
                    </a>
                </div>

                {{-- Foto Bukti --}}
                <div id="modalComplaintPhotoBox" style="margin-bottom: 1.5rem; display: none;">
                    <label style="font-size: 0.78rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 0.35rem;">
                        Foto Bukti Kejadian
                    </label>
                    <div style="text-align: center; background: var(--bg-body); padding: 0.5rem; border-radius: 10px; border: 1px solid var(--border-glass);">
                        <img id="modalComplaintPhoto" src="" alt="Bukti Foto" style="max-width: 100%; max-height: 280px; object-fit: contain; border-radius: 8px;">
                    </div>
                </div>

                {{-- Tindak Lanjut Action Buttons (RT, RW, Desa) --}}
                <div id="modalComplaintActionBox" style="padding-top: 1rem; border-top: 1px solid var(--border-glass); display: none;">
                    <label style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 0.65rem;">
                        Tindak Lanjut & Update Status Pengaduan:
                    </label>
                    
                    <form id="modalComplaintActionForm" action="" method="POST" style="margin: 0; display: flex; gap: 0.65rem; flex-wrap: wrap;">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" id="modalComplaintNewStatus" value="">

                        <button type="button" id="modalBtnProcessing" onclick="submitComplaintStatus('PROCESSING')" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.4rem; font-weight: 600;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 15px; height: 15px;"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            Diproses
                        </button>

                        <button type="button" id="modalBtnResolved" onclick="submitComplaintStatus('RESOLVED')" class="btn btn-success" style="display: inline-flex; align-items: center; gap: 0.4rem; font-weight: 600;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 15px; height: 15px;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            Selesai
                        </button>

                        <button type="button" id="modalBtnRejected" onclick="submitComplaintStatus('REJECTED')" class="btn btn-danger" style="display: inline-flex; align-items: center; gap: 0.4rem; font-weight: 600;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 15px; height: 15px;"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                            Ditolak
                        </button>
                    </form>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--border-glass); background: var(--bg-body); display: flex; justify-content: space-between; align-items: center;">
                <a id="modalComplaintFullPageLink" href="#" style="font-size: 0.85rem; color: var(--accent-1); text-decoration: none; font-weight: 600;">
                    Buka Halaman Penuh &rarr;
                </a>
                <button type="button" onclick="closeComplaintModal()" class="btn btn-outline btn-sm">Tutup</button>
            </div>
        </div>
    </div>

    <script>
    function openComplaintModal(c) {
        document.getElementById('modalComplaintReporter').innerText = c.reporter;
        document.getElementById('modalComplaintDate').innerText = c.created_at;
        document.getElementById('modalComplaintTitle').innerText = c.title;
        document.getElementById('modalComplaintDesc').innerText = c.description;
        document.getElementById('modalComplaintLocation').innerText = c.location;

        const badge = document.getElementById('modalComplaintStatusBadge');
        badge.innerText = c.status;
        badge.className = 'badge ' + c.status_badge;

        // Maps link
        const mapsBtn = document.getElementById('modalComplaintMapsBtn');
        if (c.maps_url) {
            mapsBtn.style.display = 'inline-flex';
            mapsBtn.href = c.maps_url;
        } else {
            mapsBtn.style.display = 'none';
        }

        // Photo
        const photoBox = document.getElementById('modalComplaintPhotoBox');
        const photoImg = document.getElementById('modalComplaintPhoto');
        if (c.photo_url) {
            photoBox.style.display = 'block';
            photoImg.src = c.photo_url;
        } else {
            photoBox.style.display = 'none';
        }

        // Full Page link
        document.getElementById('modalComplaintFullPageLink').href = c.show_url;

        // Action Form
        const actionBox = document.getElementById('modalComplaintActionBox');
        const actionForm = document.getElementById('modalComplaintActionForm');
        actionForm.action = c.update_url;

        if (c.can_manage) {
            actionBox.style.display = 'block';
            const btnProc = document.getElementById('modalBtnProcessing');
            const btnRes = document.getElementById('modalBtnResolved');
            const btnRej = document.getElementById('modalBtnRejected');
            if (btnProc) btnProc.style.display = (c.status === 'PROCESSING') ? 'none' : 'inline-flex';
            if (btnRes) btnRes.style.display = (c.status === 'RESOLVED') ? 'none' : 'inline-flex';
            if (btnRej) btnRej.style.display = (c.status === 'REJECTED') ? 'none' : 'inline-flex';
        } else {
            actionBox.style.display = 'none';
        }

        document.getElementById('complaintModalOverlay').style.display = 'flex';
    }

    async function submitComplaintStatus(status) {
        const configs = {
            'PROCESSING': {
                title: 'Proses Pengaduan?',
                message: 'Apakah Anda yakin ingin memproses pengaduan warga ini? Warga pelapor akan menerima notifikasi otomatis bahwa laporannya sedang ditindaklanjuti.',
                confirmText: 'Ya, Proses Sekarang',
                type: 'info'
            },
            'RESOLVED': {
                title: 'Selesaikan Pengaduan?',
                message: 'Apakah Anda yakin ingin menyelesaikan pengaduan ini? Warga pelapor akan menerima notifikasi otomatis bahwa masalah telah selesai ditangani.',
                confirmText: 'Ya, Selesaikan',
                type: 'success'
            },
            'REJECTED': {
                title: 'Tolak Pengaduan?',
                message: 'Apakah Anda yakin ingin menolak pengaduan ini? Warga pelapor akan menerima pemberitahuan mengenai status ini.',
                confirmText: 'Ya, Tolak',
                type: 'danger'
            }
        };

        const config = configs[status] || {
            title: 'Konfirmasi Tindak Lanjut',
            message: 'Apakah Anda yakin ingin memperbarui status pengaduan ini?',
            confirmText: 'Lanjutkan',
            type: 'info'
        };

        let confirmed = false;
        try {
            if (typeof window.siwilConfirm === 'function') {
                confirmed = await window.siwilConfirm(config);
            } else {
                confirmed = window.confirm(config.message);
            }
        } catch (e) {
            console.warn('Confirmation error, fallback to native confirm:', e);
            confirmed = window.confirm(config.message);
        }

        if (confirmed) {
            document.getElementById('modalComplaintNewStatus').value = status;
            document.getElementById('modalComplaintActionForm').submit();
        }
    }

    function closeComplaintModal() {
        document.getElementById('complaintModalOverlay').style.display = 'none';
    }

    document.getElementById('complaintModalOverlay').addEventListener('click', function(e) {
        if (e.target === this) closeComplaintModal();
    });
    </script>
@endsection
