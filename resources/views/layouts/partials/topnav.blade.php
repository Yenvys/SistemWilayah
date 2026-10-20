<header class="topnav" role="banner">
    <div class="topnav-left">
        <button type="button" class="menu-toggle" onclick="toggleSidebar()" aria-label="Buka navigasi menu" title="Buka/Tutup Navigasi">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="menu-icon-default">
                <line x1="3" y1="6" x2="21" y2="6"/>
                <line x1="3" y1="12" x2="21" y2="12"/>
                <line x1="3" y1="18" x2="21" y2="18"/>
            </svg>
        </button>
        <div class="topnav-heading">
            <span class="topnav-title">@yield('page-title', 'Dashboard')</span>
        </div>
    </div>

    <div class="topnav-right">
        <button id="themeToggle" onclick="toggleTheme()" title="Ubah Tema" style="background:transparent; border:none; cursor:pointer; color:var(--text-primary); margin-right:15px; display:flex; align-items:center; justify-content:center; padding:8px; border-radius:50%; transition:background 0.2s;">
            <svg id="themeIconLight" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px; display:none;"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
            <svg id="themeIconDark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px; display:none;"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
        </button>

<style>
    @media (max-width: 640px) {
        .notif-dropdown-menu {
            position: fixed !important;
            top: 62px !important;
            left: 12px !important;
            right: 12px !important;
            width: auto !important;
            max-width: calc(100vw - 24px) !important;
            margin-top: 0 !important;
            z-index: 10002 !important;
            border-radius: 14px !important;
            box-shadow: 0 16px 45px rgba(0, 0, 0, 0.4) !important;
        }

        .user-dropdown-menu {
            position: fixed !important;
            top: 62px !important;
            right: 12px !important;
            width: 220px !important;
            z-index: 10002 !important;
            box-shadow: 0 16px 45px rgba(0, 0, 0, 0.4) !important;
        }
    }
</style>

{{-- Mobile Dropdown Dimming Backdrop --}}
<div id="topnavDropdownBackdrop" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.35); backdrop-filter: blur(2px); -webkit-backdrop-filter: blur(2px); z-index: 10001;" onclick="closeAllTopnavDropdowns()"></div>

        {{-- Notification Bell --}}
        @php
            $unreadNotifCount = Auth::user()->unreadNotificationsCount();
            $recentNotifications = Auth::user()->appNotifications()->take(5)->get();
        @endphp
        <div class="notification-dropdown-container" style="position: relative; margin-right: 12px;">
            <button id="notifToggle" onclick="toggleNotifDropdown(event)" title="Notifikasi" style="background: transparent; border: none; cursor: pointer; color: var(--text-primary); display: flex; align-items: center; justify-content: center; padding: 8px; border-radius: 50%; position: relative; transition: background 0.2s;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px;">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
                @if($unreadNotifCount > 0)
                    <span style="position: absolute; top: 2px; right: 2px; width: 18px; height: 18px; background: #ef4444; color: white; font-size: 0.65rem; font-weight: 700; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 0 0 2px var(--bg-card);">
                        {{ $unreadNotifCount > 9 ? '9+' : $unreadNotifCount }}
                    </span>
                @endif
            </button>

            <div id="notifDropdown" class="notif-dropdown-menu" style="display: none; position: absolute; right: 0; top: 100%; margin-top: 10px; background: var(--bg-card); border: 1px solid var(--border-glass); border-radius: 12px; box-shadow: var(--shadow-hover); width: 340px; max-width: 90vw; z-index: 10002; overflow: hidden;">
                <div style="padding: 0.85rem 1rem; border-bottom: 1px solid var(--border-glass); display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-weight: 700; font-size: 0.95rem; color: var(--text-primary);">Notifikasi</span>
                    @if($unreadNotifCount > 0)
                        <form action="{{ route('notifications.read-all') }}" method="POST" style="margin: 0;" id="topnavMarkAllReadForm">
                            @csrf
                            <button type="submit" id="topnavBtnMarkAll" style="background: none; border: none; color: var(--accent-1); font-size: 0.78rem; font-weight: 600; cursor: pointer; padding: 0;">Tandai Semua Dibaca</button>
                        </form>
                    @endif
                </div>

                <div style="max-height: 360px; overflow-y: auto;">
                    @forelse($recentNotifications as $notif)
                        <form action="{{ route('notifications.read', $notif->id) }}" method="POST" style="margin: 0;">
                            @csrf
                            <button type="submit" style="width: 100%; text-align: left; padding: 0.85rem 1rem; border: none; border-bottom: 1px solid var(--border-glass); background: {{ $notif->is_read ? 'transparent' : 'rgba(37, 99, 235, 0.05)' }}; cursor: pointer; display: flex; gap: 0.75rem; align-items: flex-start; transition: background 0.15s; font-family: inherit;">
                                <div style="margin-top: 5px; width: 8px; height: 8px; border-radius: 50%; background: {{ $notif->is_read ? 'transparent' : 'var(--accent-1)' }}; flex-shrink: 0;"></div>
                                <div style="flex: 1;">
                                    <div style="font-weight: 600; font-size: 0.85rem; color: var(--text-primary); margin-bottom: 0.2rem;">
                                        {{ $notif->title }}
                                    </div>
                                    <div style="font-size: 0.78rem; color: var(--text-secondary); line-height: 1.35; margin-bottom: 0.3rem;">
                                        {{ $notif->message }}
                                    </div>
                                    <div style="font-size: 0.7rem; color: var(--text-muted);">
                                        {{ $notif->created_at->diffForHumans() }}
                                    </div>
                                </div>
                            </button>
                        </form>
                    @empty
                        <div style="padding: 2.5rem 1rem; text-align: center; color: var(--text-muted); font-size: 0.85rem;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width: 32px; height: 32px; margin: 0 auto 0.5rem; opacity: 0.4;"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                            Belum ada notifikasi baru
                        </div>
                    @endforelse
                </div>

                @if($recentNotifications->count() > 0)
                    <div style="padding: 0.6rem; text-align: center; border-top: 1px solid var(--border-glass); background: var(--bg-body);">
                        <a href="{{ route('notifications.index') }}" style="font-size: 0.8rem; font-weight: 600; color: var(--accent-1); text-decoration: none;">Lihat Semua Notifikasi</a>
                    </div>
                @endif
            </div>
        </div>

        <div class="user-dropdown-container" style="position: relative;">
            <div class="user-info" onclick="toggleUserDropdown(event)" style="cursor: pointer; display: flex; align-items: center; gap: var(--space-md); padding: 5px; border-radius: var(--radius-base); transition: background 0.2s;">
                <div class="user-avatar">
                    @php 
                        $displayName = Auth::user()->resident ? Auth::user()->resident->full_name : Auth::user()->username;
                    @endphp
                    {{ strtoupper(substr($displayName, 0, 2)) }}
                </div>
                <div class="user-details">
                    <div class="user-name">{{ $displayName }}</div>
                    <div class="user-role">{{ str_replace('_', ' ', Auth::user()->role) }}</div>
                </div>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; margin-left: 5px; color: var(--text-muted);"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </div>
            
            <div id="userDropdown" class="user-dropdown-menu" style="display: none; position: absolute; right: 0; top: 100%; margin-top: 10px; background: var(--bg-card); border: 1px solid var(--border-glass); border-radius: var(--radius-base); box-shadow: var(--shadow-hover); width: 220px; z-index: 10002; padding: 0.5rem 0; overflow: hidden;">
                <a href="{{ route('settings.index') }}" style="display: flex; align-items: center; padding: 0.75rem 1rem; color: var(--text-primary); text-decoration: none; font-size: 0.9rem; font-weight: 500; transition: background 0.2s;">
                    <svg style="width:16px;height:16px;margin-right:12px;color:var(--text-muted);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg> 
                    Pengaturan Profil
                </a>
                <form action="{{ url('/logout') }}" method="POST" style="margin: 0; border-top: 1px solid var(--border-glass);">
                    @csrf
                    <button type="submit" style="display: flex; align-items: center; width: 100%; text-align: left; background: none; border: none; padding: 0.75rem 1rem; color: var(--danger); cursor: pointer; font-size: 0.9rem; font-weight: 600; font-family: inherit; transition: background 0.2s;">
                        <svg style="width:16px;height:16px;margin-right:12px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg> 
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>

<script>
function closeAllTopnavDropdowns() {
    const notifDropdown = document.getElementById('notifDropdown');
    const userDropdown = document.getElementById('userDropdown');
    const backdrop = document.getElementById('topnavDropdownBackdrop');
    if (notifDropdown) notifDropdown.style.display = 'none';
    if (userDropdown) userDropdown.style.display = 'none';
    if (backdrop) backdrop.style.display = 'none';
}

function toggleNotifDropdown(event) {
    event.stopPropagation();
    const dropdown = document.getElementById('notifDropdown');
    const userDropdown = document.getElementById('userDropdown');
    const backdrop = document.getElementById('topnavDropdownBackdrop');
    if (userDropdown) userDropdown.style.display = 'none';
    if (dropdown) {
        const isOpening = dropdown.style.display !== 'block';
        dropdown.style.display = isOpening ? 'block' : 'none';
        if (backdrop) backdrop.style.display = isOpening ? 'block' : 'none';
    }
}

function toggleUserDropdown(event) {
    if (event) event.stopPropagation();
    const dropdown = document.getElementById('userDropdown');
    const notifDropdown = document.getElementById('notifDropdown');
    const backdrop = document.getElementById('topnavDropdownBackdrop');
    if (notifDropdown) notifDropdown.style.display = 'none';
    if (dropdown) {
        const isOpening = dropdown.style.display !== 'block';
        dropdown.style.display = isOpening ? 'block' : 'none';
        if (backdrop) backdrop.style.display = isOpening ? 'block' : 'none';
    }
}

document.addEventListener('click', function(e) {
    const notifDropdown = document.getElementById('notifDropdown');
    const userDropdown = document.getElementById('userDropdown');
    const notifContainer = e.target.closest('.notification-dropdown-container');
    const userContainer = e.target.closest('.user-dropdown-container');

    if (!notifContainer && !userContainer) {
        closeAllTopnavDropdowns();
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const topnavForm = document.getElementById('topnavMarkAllReadForm');
    if (topnavForm) {
        topnavForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('topnavBtnMarkAll');
            if (btn) {
                btn.disabled = true;
                btn.innerText = 'Memproses...';
            }

            fetch(topnavForm.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': topnavForm.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Remove blue dots in topnav
                    const dropdown = document.getElementById('notifDropdown');
                    if (dropdown) {
                        dropdown.querySelectorAll('form button').forEach(b => {
                            b.style.background = 'transparent';
                            const dot = b.querySelector('div');
                            if (dot) dot.style.background = 'transparent';
                        });
                    }
                    // Remove badge
                    const badge = document.querySelector('#notifToggle span');
                    if (badge) badge.remove();
                    if (btn) {
                        btn.innerText = '✓ Semua Dibaca';
                        btn.style.color = 'var(--text-muted)';
                        btn.style.cursor = 'default';
                    }
                } else {
                    topnavForm.submit();
                }
            })
            .catch(() => {
                topnavForm.submit();
            });
        });
    }
});
</script>
