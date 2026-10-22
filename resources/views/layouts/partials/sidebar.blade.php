<aside class="sidebar" id="sidebar" aria-label="Navigasi Utama">
    <div class="sidebar-logo">
        <a href="{{ url('/dashboard') }}" class="sidebar-brand-link" title="SIWIL AI Dashboard">
            <div class="sidebar-logo-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    <path d="m9 12 2 2 4-4"/>
                </svg>
            </div>
            <div class="sidebar-brand-meta">
                <span class="sidebar-logo-text">SIWIL AI</span>
                <span class="sidebar-logo-sub">Sistem Wilayah Terpadu</span>
            </div>
        </a>
        <button type="button" class="sidebar-close-btn" onclick="toggleSidebar()" aria-label="Tutup navigasi" title="Tutup Menu">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
    </div>

    <nav class="sidebar-nav">
        @php
            $user = Auth::user();
            $role = $user->role;
            $botWaNumber = config('services.whatsapp.bot_number', env('WHATSAPP_BOT_NUMBER', '628981295171'));
            $botUsername = config('services.telegram.bot_username', env('TELEGRAM_BOT_USERNAME', 'cevltBot'));
            $userFullName = $user->resident?->full_name ?? $user->name ?? $user->username;
            $waConnected = !empty($user->whatsapp_number) || !empty($user->whatsapp_lid);
            $teleConnected = !empty($user->telegram_id);
            $waUrl = "https://wa.me/{$botWaNumber}?text=" . urlencode("Halo SIWIL, saya {$userFullName}");
            $teleUrl = "https://t.me/{$botUsername}";
        @endphp

        {{-- KATEGORI: MENU UTAMA --}}
        <div class="sidebar-category">
            <span class="sidebar-category-title">Menu Utama</span>
            <span class="sidebar-category-line"></span>
        </div>

        <a href="{{ url('/dashboard') }}" class="nav-item {{ request()->is('dashboard') && !request()->is('dashboard/*') && !request()->has('open_chat') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
            <span>Dashboard</span>
        </a>

        @if($role !== 'WARGA')
        <a href="{{ route('areas.index') }}" class="nav-item {{ request()->is('dashboard/areas*') || request()->is('dashboard/villages*') || request()->is('dashboard/residents*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            <span>{{ $role === 'RT' ? 'Data Warga RT' : ($role === 'RW' ? 'Data Warga RW' : 'Wilayah & Penduduk') }}</span>
        </a>
        @endif

        <a href="{{ url('/dashboard/announcements') }}" class="nav-item {{ request()->is('dashboard/announcements*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 11 18-5v12L3 13v-2z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/></svg>
            <span>Pengumuman Desa</span>
        </a>

        {{-- KATEGORI: LAYANAN WARGA --}}
        <div class="sidebar-category">
            <span class="sidebar-category-title">{{ $role === 'WARGA' ? 'Layanan & Warga' : 'Layanan Desa' }}</span>
            <span class="sidebar-category-line"></span>
        </div>

        <a href="{{ route('documents.index') }}" class="nav-item {{ request()->is('dashboard/documents*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            <span>{{ $role === 'WARGA' ? 'Pengajuan Surat' : 'Layanan Surat' }}</span>
        </a>

        <a href="{{ route('complaints.index') }}" class="nav-item {{ request()->is('dashboard/complaints*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            <span>{{ $role === 'WARGA' ? 'Pengaduan' : 'Pengaduan Warga' }}</span>
        </a>

        <a href="{{ route('polls.index') }}" class="nav-item {{ request()->is('dashboard/polls*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13h-.68l-2 2h1.91L19 17H5l1.78-2h2.05l-2-2H6l-3 3v2c0 1.1.89 2 1.99 2H19c1.1 0 2-.9 2-2v-2l-3-3zm-1-5.05l-4.95 4.95-3.54-3.54 4.95-4.95L16.99 7.95z"/></svg>
            <span>Musyawarah</span>
        </a>

        @if($role === 'WARGA')
        <a href="{{ route('families.my-family') }}" class="nav-item {{ request()->routeIs('families.my-family') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            <span>Data Keluarga</span>
        </a>
        @endif

        {{-- KATEGORI: PEMERINTAHAN & LAPORAN --}}
        @if(in_array($role, ['SUPER_ADMIN', 'ADMIN_DESA']) || $role !== 'WARGA')
        <div class="sidebar-category">
            <span class="sidebar-category-title">Pemerintahan & Laporan</span>
            <span class="sidebar-category-line"></span>
        </div>

        @if(in_array($role, ['SUPER_ADMIN', 'ADMIN_DESA']))
        <a href="{{ route('developments.index') }}" class="nav-item {{ request()->is('dashboard/developments*') || request()->is('dashboard/budgets*') || request()->is('dashboard/spatial-analytics*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 22h20"/><path d="M17 2v20"/><path d="M7 10v12"/><path d="M12 6v16"/><path d="M17 6H7"/><path d="M17 10H7"/></svg>
            <span>Pembangunan & APBDes</span>
        </a>
        @endif

        @if($role !== 'WARGA')
        <a href="{{ route('reports.index') }}" class="nav-item {{ request()->is('dashboard/reports*') || request()->is('dashboard/audit-logs*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
            </svg>
            <span>Laporan & Audit Log</span>
        </a>
        @endif
        @endif

        {{-- KATEGORI: SISTEM & PENGATURAN --}}
        <div class="sidebar-category">
            <span class="sidebar-category-title">Administrasi & Sistem</span>
            <span class="sidebar-category-line"></span>
        </div>

        @if(!in_array($role, ['RT', 'RW', 'WARGA']))
        <a href="{{ url('/dashboard/users') }}" class="nav-item {{ request()->is('dashboard/users*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
            <span>Manajemen User</span>
        </a>
        @endif

        @if($role === 'SUPER_ADMIN')
        <a href="{{ url('/dashboard/ai-knowledge') }}" class="nav-item {{ request()->is('dashboard/ai-knowledge*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m2 14 10-10 10 10"/><path d="M4 12v8h16v-8"/><path d="M10 12v5h4v-5"/></svg>
            <span>Kelola AI Knowledge</span>
        </a>
        @endif

        <a href="{{ url('/dashboard/settings') }}" class="nav-item {{ request()->is('dashboard/settings*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
            <span>Pengaturan Akun</span>
        </a>

        {{-- KATEGORI: KONEKSI BOT --}}
        <div class="sidebar-category">
            <span class="sidebar-category-title">Koneksi Bot</span>
            <span class="sidebar-category-line"></span>
        </div>

        {{-- WhatsApp Bot Link --}}
        <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer" class="nav-item nav-item-bot nav-item-wa" title="Buka Chat Bot WhatsApp (+{{ $botWaNumber }})">
            <div class="bot-item-left">
                <div class="bot-icon-badge bot-icon-wa">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91C2.13 13.66 2.59 15.36 3.45 16.86L2.05 22L7.3 20.62C8.75 21.41 10.38 21.83 12.04 21.83C17.5 21.83 21.95 17.38 21.95 11.92C21.95 9.27 20.92 6.78 19.05 4.91C17.18 3.03 14.69 2 12.04 2M12.05 3.67C14.25 3.67 16.31 4.53 17.87 6.09C19.42 7.65 20.28 9.72 20.28 11.92C20.28 16.46 16.58 20.15 12.04 20.15C10.56 20.15 9.11 19.76 7.85 19L7.55 18.83L4.43 19.65L5.26 16.61L5.06 16.29C4.24 15 3.8 13.47 3.8 11.91C3.81 7.37 7.5 3.67 12.05 3.67M9.06 7.04C8.88 7.04 8.58 7.11 8.33 7.38C8.08 7.65 7.37 8.32 7.37 9.68C7.37 11.04 8.36 12.35 8.5 12.53C8.64 12.71 10.43 15.48 13.18 16.67C13.84 16.95 14.34 17.11 14.74 17.24C15.4 17.45 16.01 17.42 16.49 17.35C17.03 17.27 18.14 16.68 18.37 16.03C18.6 15.38 18.6 14.83 18.53 14.71C18.46 14.59 18.28 14.53 18.01 14.39C17.74 14.25 16.42 13.6 16.17 13.51C15.92 13.42 15.74 13.38 15.56 13.65C15.38 13.92 14.86 14.53 14.7 14.71C14.54 14.89 14.38 14.91 14.11 14.78C13.84 14.64 12.97 14.36 11.94 13.44C11.14 12.72 10.6 11.84 10.44 11.57C10.28 11.3 10.42 11.15 10.56 11.02C10.68 10.9 10.83 10.71 10.97 10.55C11.11 10.39 11.15 10.28 11.24 10.1C11.33 9.92 11.29 9.76 11.22 9.63C11.15 9.5 10.62 8.2 10.4 7.67C10.19 7.15 9.97 7.22 9.8 7.21C9.65 7.2 9.47 7.04 9.06 7.04Z"/>
                    </svg>
                </div>
                <div class="bot-text-meta">
                    <span class="bot-label">WhatsApp</span>
                    <span class="bot-subtext">Chat Bot</span>
                </div>
            </div>
            <div class="bot-actions">
                @if($waConnected)
                    <button type="button" class="bot-status-pill bot-status-pill-active" onclick="event.preventDefault(); event.stopPropagation(); openConnectModal('whatsapp');" title="WhatsApp Terhubung (Klik untuk ubah/putuskan)">
                        <span class="live-dot live-dot-green"></span>
                        <span>Aktif</span>
                    </button>
                @else
                    <button type="button" class="bot-status-pill bot-status-pill-connect" onclick="event.preventDefault(); event.stopPropagation(); openConnectModal('whatsapp');" title="Hubungkan nomor WhatsApp">
                        <span>Tautkan</span>
                    </button>
                @endif
                <span class="bot-external-icon" title="Buka Chat WhatsApp">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                        <polyline points="15 3 21 3 21 9"></polyline>
                        <line x1="10" y1="14" x2="21" y2="3"></line>
                    </svg>
                </span>
            </div>
        </a>

        {{-- Telegram Bot Link --}}
        <a href="{{ $teleUrl }}" target="_blank" rel="noopener noreferrer" class="nav-item nav-item-bot nav-item-tele" title="Buka Obrolan Telegram (&#64;{{ $botUsername }})">
            <div class="bot-item-left">
                <div class="bot-icon-badge bot-icon-tele">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 0 0-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"/>
                    </svg>
                </div>
                <div class="bot-text-meta">
                    <span class="bot-label">Telegram</span>
                    <span class="bot-subtext">&#64;{{ $botUsername }}</span>
                </div>
            </div>
            <div class="bot-actions">
                @if($teleConnected)
                    <button type="button" class="bot-status-pill bot-status-pill-active" onclick="event.preventDefault(); event.stopPropagation(); openConnectModal('telegram');" title="Telegram Terhubung (Klik untuk ubah/putuskan)">
                        <span class="live-dot live-dot-blue"></span>
                        <span>Aktif</span>
                    </button>
                @else
                    <button type="button" class="bot-status-pill bot-status-pill-connect" onclick="event.preventDefault(); event.stopPropagation(); openConnectModal('telegram');" title="Hubungkan akun Telegram">
                        <span>Tautkan</span>
                    </button>
                @endif
                <span class="bot-external-icon" title="Buka Telegram">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                        <polyline points="15 3 21 3 21 9"></polyline>
                        <line x1="10" y1="14" x2="21" y2="3"></line>
                    </svg>
                </span>
            </div>
        </a>
    </nav>

    @if($role === 'WARGA')
    @php 
        $isEmergency = Auth::user()->emergencies()->where('status', '!=', 'RESOLVED')->exists(); 
    @endphp
    <div style="padding: 0.85rem 0.85rem 0.5rem; margin-top: auto;">
        @if($isEmergency)
            <div style="background: rgba(239, 68, 68, 0.12); border: 1px solid var(--danger); padding: 0.75rem; border-radius: 10px; text-align: center;">
                <span style="color: var(--danger); font-weight: bold; font-size: 0.85rem; display: flex; align-items: center; justify-content: center; gap: 0.4rem;">
                    <span class="live-dot" style="background: var(--danger); animation: pulseDotDanger 1.5s infinite;"></span>
                    SOS AKTIF
                </span>
            </div>
        @else
            <form id="sos-form" action="{{ route('emergencies.store') }}" method="POST">
                @csrf
                <input type="hidden" name="type" value="DARURAT UMUM">
                <input type="hidden" name="location_lat" id="sos_lat">
                <input type="hidden" name="location_lng" id="sos_lng">
                <button type="button" onclick="triggerSos()" class="btn btn-danger btn-sos" style="width: 100%;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    <span>TOMBOL SOS</span>
                </button>
            </form>
            <script>
            async function triggerSos() {
                const ok = await window.siwilConfirm({
                    title: 'SIARKAN KEADAAN DARURAT (SOS)?',
                    message: 'Peringatan darurat akan langsung dikirimkan ke perangkat desa & RT/RW disertai data lokasi Anda saat ini. Pastikan Anda benar-benar dalam situasi darurat.',
                    confirmText: 'Ya, Kirim Sinyal SOS',
                    type: 'danger'
                });
                if (ok) {
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(function(position) {
                            document.getElementById('sos_lat').value = position.coords.latitude;
                            document.getElementById('sos_lng').value = position.coords.longitude;
                            document.getElementById('sos-form').submit();
                        }, function() {
                            document.getElementById('sos-form').submit();
                        });
                    } else {
                        document.getElementById('sos-form').submit();
                    }
                }
            }
            </script>
        @endif
    </div>
    @endif

    <div class="sidebar-footer">
        <div style="font-weight: 600; letter-spacing: 0.02em;">SIWIL AI <span style="opacity: 0.7; font-weight: 400;">v1.0</span></div>
        <div style="margin-top: 0.2rem; font-size: 0.72rem; color: var(--text-muted);">
            Created by <span style="font-weight: 700; color: var(--accent-1);">Alfarel</span>
        </div>
    </div>
</aside>
