{{-- Connect WA & Telegram Modal --}}
<div id="modalConnectBot" class="siwil-confirm-overlay" style="display: none;" onclick="if(event.target === this) closeConnectModal()">
    <div class="siwil-confirm-dialog" style="max-width: 520px; text-align: left; align-items: stretch; padding: 1.5rem;">
        
        {{-- Modal Header --}}
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-glass); padding-bottom: 0.85rem; margin-bottom: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <div id="connectModalHeaderIcon" style="width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; background: rgba(37, 211, 102, 0.15); color: #25D366;">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor">
                        <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91C2.13 13.66 2.59 15.36 3.45 16.86L2.05 22L7.3 20.62C8.75 21.41 10.38 21.83 12.04 21.83C17.5 21.83 21.95 17.38 21.95 11.92C21.95 9.27 20.92 6.78 19.05 4.91C17.18 3.03 14.69 2 12.04 2M12.05 3.67C14.25 3.67 16.31 4.53 17.87 6.09C19.42 7.65 20.28 9.72 20.28 11.92C20.28 16.46 16.58 20.15 12.04 20.15C10.56 20.15 9.11 19.76 7.85 19L7.55 18.83L4.43 19.65L5.26 16.61L5.06 16.29C4.24 15 3.8 13.47 3.8 11.91C3.81 7.37 7.5 3.67 12.05 3.67M9.06 7.04C8.88 7.04 8.58 7.11 8.33 7.38C8.08 7.65 7.37 8.32 7.37 9.68C7.37 11.04 8.36 12.35 8.5 12.53C8.64 12.71 10.43 15.48 13.18 16.67C13.84 16.95 14.34 17.11 14.74 17.24C15.4 17.45 16.01 17.42 16.49 17.35C17.03 17.27 18.14 16.68 18.37 16.03C18.6 15.38 18.6 14.83 18.53 14.71C18.46 14.59 18.28 14.53 18.01 14.39C17.74 14.25 16.42 13.6 16.17 13.51C15.92 13.42 15.74 13.38 15.56 13.65C15.38 13.92 14.86 14.53 14.7 14.71C14.54 14.89 14.38 14.91 14.11 14.78C13.84 14.64 12.97 14.36 11.94 13.44C11.14 12.72 10.6 11.84 10.44 11.57C10.28 11.3 10.42 11.15 10.56 11.02C10.68 10.9 10.83 10.71 10.97 10.55C11.11 10.39 11.15 10.28 11.24 10.1C11.33 9.92 11.29 9.76 11.22 9.63C11.15 9.5 10.62 8.2 10.4 7.67C10.19 7.15 9.97 7.22 9.8 7.21C9.65 7.2 9.47 7.04 9.06 7.04Z"/>
                    </svg>
                </div>
                <div>
                    <h3 id="connectModalTitle" style="font-size: 1.05rem; font-weight: 700; color: var(--text-primary); margin: 0;">Hubungkan WhatsApp & Telegram</h3>
                    <p style="font-size: 0.78rem; color: var(--text-muted); margin: 2px 0 0 0;">Layanan otomatisasi & notifikasi SIWIL</p>
                </div>
            </div>
            <button type="button" onclick="closeConnectModal()" style="background: none; border: none; font-size: 1.4rem; cursor: pointer; color: var(--text-muted); padding: 0.2rem 0.5rem; line-height: 1;">&times;</button>
        </div>

        {{-- Tab Switcher --}}
        <div style="display: flex; gap: 0.5rem; background: var(--bg-body); padding: 4px; border-radius: 10px; margin-bottom: 1.25rem;">
            <button type="button" id="tabBtnWa" onclick="switchConnectTab('whatsapp')" style="flex: 1; padding: 0.55rem; border: none; border-radius: 8px; font-weight: 600; font-size: 0.85rem; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 0.4rem; background: var(--bg-card); color: #25D366; box-shadow: 0 2px 6px rgba(0,0,0,0.06);">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                    <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91C2.13 13.66 2.59 15.36 3.45 16.86L2.05 22L7.3 20.62C8.75 21.41 10.38 21.83 12.04 21.83C17.5 21.83 21.95 17.38 21.95 11.92C21.95 9.27 20.92 6.78 19.05 4.91C17.18 3.03 14.69 2 12.04 2M12.05 3.67C14.25 3.67 16.31 4.53 17.87 6.09C19.42 7.65 20.28 9.72 20.28 11.92C20.28 16.46 16.58 20.15 12.04 20.15C10.56 20.15 9.11 19.76 7.85 19L7.55 18.83L4.43 19.65L5.26 16.61L5.06 16.29C4.24 15 3.8 13.47 3.8 11.91C3.81 7.37 7.5 3.67 12.05 3.67M9.06 7.04C8.88 7.04 8.58 7.11 8.33 7.38C8.08 7.65 7.37 8.32 7.37 9.68C7.37 11.04 8.36 12.35 8.5 12.53C8.64 12.71 10.43 15.48 13.18 16.67C13.84 16.95 14.34 17.11 14.74 17.24C15.4 17.45 16.01 17.42 16.49 17.35C17.03 17.27 18.14 16.68 18.37 16.03C18.6 15.38 18.6 14.83 18.53 14.71C18.46 14.59 18.28 14.53 18.01 14.39C17.74 14.25 16.42 13.6 16.17 13.51C15.92 13.42 15.74 13.38 15.56 13.65C15.38 13.92 14.86 14.53 14.7 14.71C14.54 14.89 14.38 14.91 14.11 14.78C13.84 14.64 12.97 14.36 11.94 13.44C11.14 12.72 10.6 11.84 10.44 11.57C10.28 11.3 10.42 11.15 10.56 11.02C10.68 10.9 10.83 10.71 10.97 10.55C11.11 10.39 11.15 10.28 11.24 10.1C11.33 9.92 11.29 9.76 11.22 9.63C11.15 9.5 10.62 8.2 10.4 7.67C10.19 7.15 9.97 7.22 9.8 7.21C9.65 7.2 9.47 7.04 9.06 7.04Z"/>
                </svg>
                WhatsApp Bot
            </button>
            <button type="button" id="tabBtnTele" onclick="switchConnectTab('telegram')" style="flex: 1; padding: 0.55rem; border: none; border-radius: 8px; font-weight: 600; font-size: 0.85rem; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 0.4rem; background: transparent; color: var(--text-secondary);">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/>
                </svg>
                Telegram Bot
            </button>
        </div>

        {{-- WhatsApp Panel --}}
        <div id="panelConnectWa">
            @php
                $userWa = Auth::user()->whatsapp_number;
                if (empty($userWa) && Auth::user()->whatsapp_lid && Auth::user()->phone_number) {
                    $userWa = \App\Services\WhatsAppService::normalizePhoneNumber(Auth::user()->phone_number);
                } elseif ($userWa && strlen($userWa) >= 12 && !str_starts_with($userWa, '62') && !str_starts_with($userWa, '0') && Auth::user()->phone_number) {
                    $userWa = \App\Services\WhatsAppService::normalizePhoneNumber(Auth::user()->phone_number);
                }
                $displayWa = $userWa;
                if ($userWa && str_starts_with($userWa, '62') && strlen($userWa) >= 10) {
                    $displayWa = '+' . substr($userWa, 0, 2) . ' ' . substr($userWa, 2, 3) . '-' . substr($userWa, 5, 4) . '-' . substr($userWa, 9);
                }
                $isWaConnected = !empty(Auth::user()->whatsapp_number) || !empty(Auth::user()->whatsapp_lid);
                $botWaNumber = config('services.whatsapp.bot_number', env('WHATSAPP_BOT_NUMBER', '628981295171'));
            @endphp

            @if($isWaConnected)
                <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 12px; padding: 1rem; margin-bottom: 1.25rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.4rem;">
                        <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #10b981;"></span>
                        <strong style="color: #10b981; font-size: 0.9rem;">Akun WhatsApp Terhubung</strong>
                    </div>
                    <p style="font-size: 0.85rem; color: var(--text-secondary); margin: 0;">
                        Nomor: <code style="font-weight: 700; color: var(--text-primary); background: var(--bg-card); padding: 2px 6px; border-radius: 4px;">{{ $displayWa ?? Auth::user()->whatsapp_number }}</code>
                    </p>
                    <p style="font-size: 0.78rem; color: var(--text-muted); margin-top: 0.35rem;">
                        Anda akan menerima notifikasi pengajuan surat, tindak lanjut pengaduan, dan polling musyawarah di nomor ini.
                    </p>
                </div>

                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <a href="https://wa.me/{{ $botWaNumber }}?text={{ urlencode('Halo SIWIL, saya ' . (Auth::user()->resident?->full_name ?? Auth::user()->username)) }}" target="_blank" class="btn" style="background: #25D366; color: white; display: flex; align-items: center; justify-content: center; gap: 0.5rem; text-decoration: none; padding: 0.7rem; border-radius: 10px; font-weight: 600;">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                            <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91C2.13 13.66 2.59 15.36 3.45 16.86L2.05 22L7.3 20.62C8.75 21.41 10.38 21.83 12.04 21.83C17.5 21.83 21.95 17.38 21.95 11.92C21.95 9.27 20.92 6.78 19.05 4.91C17.18 3.03 14.69 2 12.04 2M12.05 3.67C14.25 3.67 16.31 4.53 17.87 6.09C19.42 7.65 20.28 9.72 20.28 11.92C20.28 16.46 16.58 20.15 12.04 20.15C10.56 20.15 9.11 19.76 7.85 19L7.55 18.83L4.43 19.65L5.26 16.61L5.06 16.29C4.24 15 3.8 13.47 3.8 11.91C3.81 7.37 7.5 3.67 12.05 3.67M9.06 7.04C8.88 7.04 8.58 7.11 8.33 7.38C8.08 7.65 7.37 8.32 7.37 9.68C7.37 11.04 8.36 12.35 8.5 12.53C8.64 12.71 10.43 15.48 13.18 16.67C13.84 16.95 14.34 17.11 14.74 17.24C15.4 17.45 16.01 17.42 16.49 17.35C17.03 17.27 18.14 16.68 18.37 16.03C18.6 15.38 18.6 14.83 18.53 14.71C18.46 14.59 18.28 14.53 18.01 14.39C17.74 14.25 16.42 13.6 16.17 13.51C15.92 13.42 15.74 13.38 15.56 13.65C15.38 13.92 14.86 14.53 14.7 14.71C14.54 14.89 14.38 14.91 14.11 14.78C13.84 14.64 12.97 14.36 11.94 13.44C11.14 12.72 10.6 11.84 10.44 11.57C10.28 11.3 10.42 11.15 10.56 11.02C10.68 10.9 10.83 10.71 10.97 10.55C11.11 10.39 11.15 10.28 11.24 10.1C11.33 9.92 11.29 9.76 11.22 9.63C11.15 9.5 10.62 8.2 10.4 7.67C10.19 7.15 9.97 7.22 9.8 7.21C9.65 7.2 9.47 7.04 9.06 7.04Z"/>
                        </svg>
                        Buka Obrolan Bot WhatsApp
                    </a>

                    <div style="display: flex; gap: 0.5rem;">
                        <button type="button" onclick="showChangeWaForm()" class="btn btn-outline" style="flex: 1; padding: 0.6rem; font-size: 0.85rem;">
                            Ubah Nomor
                        </button>
                        <button type="button" onclick="disconnectWhatsApp()" class="btn btn-outline" style="color: var(--danger); border-color: rgba(239, 68, 68, 0.3); padding: 0.6rem; font-size: 0.85rem;">
                            Putuskan
                        </button>
                    </div>
                </div>

                <div id="changeWaFormBox" style="display: none; margin-top: 1rem; padding-top: 1rem; border-top: 1px dashed var(--border-glass);">
                    <label class="form-label" style="font-size: 0.82rem;">Masukkan Nomor WhatsApp Baru:</label>
                    <div style="display: flex; gap: 0.5rem; margin-top: 0.35rem;">
                        <input type="text" id="inputNewWaNumber" class="form-input" placeholder="Contoh: 081234567890" style="flex: 1;">
                        <button type="button" onclick="submitConnectWa('inputNewWaNumber')" class="btn btn-primary" style="padding: 0 1rem; font-size: 0.85rem;">Simpan</button>
                    </div>
                </div>
            @else
                <p style="font-size: 0.85rem; color: var(--text-secondary); line-height: 1.5; margin-bottom: 1rem;">
                    Hubungkan akun Anda dengan Bot WhatsApp SIWIL (<code>+{{ $botWaNumber }}</code>) untuk menerima notifikasi permohonan surat, pengaduan wilayah, dan voting musyawarah secara real-time.
                </p>

                <div style="margin-bottom: 1.25rem;">
                    <label class="form-label" for="inputConnectWa" style="font-size: 0.82rem; font-weight: 600;">Nomor WhatsApp Anda:</label>
                    <input type="text" id="inputConnectWa" class="form-input" value="{{ Auth::user()->phone_number ?? '' }}" placeholder="Contoh: 081234567890" style="margin-top: 0.35rem;">
                    <small style="color: var(--text-muted); font-size: 0.75rem; display: block; margin-top: 4px;">Pastikan nomor aktif dan terpasang di aplikasi WhatsApp.</small>
                </div>

                <div style="display: flex; flex-direction: column; gap: 0.6rem;">
                    <button type="button" onclick="submitConnectWa('inputConnectWa')" id="btnSubmitConnectWa" class="btn btn-primary" style="background: #25D366; border-color: #25D366; color: white; display: flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.75rem; font-weight: 600;">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                            <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91C2.13 13.66 2.59 15.36 3.45 16.86L2.05 22L7.3 20.62C8.75 21.41 10.38 21.83 12.04 21.83C17.5 21.83 21.95 17.38 21.95 11.92C21.95 9.27 20.92 6.78 19.05 4.91C17.18 3.03 14.69 2 12.04 2M12.05 3.67C14.25 3.67 16.31 4.53 17.87 6.09C19.42 7.65 20.28 9.72 20.28 11.92C20.28 16.46 16.58 20.15 12.04 20.15C10.56 20.15 9.11 19.76 7.85 19L7.55 18.83L4.43 19.65L5.26 16.61L5.06 16.29C4.24 15 3.8 13.47 3.8 11.91C3.81 7.37 7.5 3.67 12.05 3.67M9.06 7.04C8.88 7.04 8.58 7.11 8.33 7.38C8.08 7.65 7.37 8.32 7.37 9.68C7.37 11.04 8.36 12.35 8.5 12.53C8.64 12.71 10.43 15.48 13.18 16.67C13.84 16.95 14.34 17.11 14.74 17.24C15.4 17.45 16.01 17.42 16.49 17.35C17.03 17.27 18.14 16.68 18.37 16.03C18.6 15.38 18.6 14.83 18.53 14.71C18.46 14.59 18.28 14.53 18.01 14.39C17.74 14.25 16.42 13.6 16.17 13.51C15.92 13.42 15.74 13.38 15.56 13.65C15.38 13.92 14.86 14.53 14.7 14.71C14.54 14.89 14.38 14.91 14.11 14.78C13.84 14.64 12.97 14.36 11.94 13.44C11.14 12.72 10.6 11.84 10.44 11.57C10.28 11.3 10.42 11.15 10.56 11.02C10.68 10.9 10.83 10.71 10.97 10.55C11.11 10.39 11.15 10.28 11.24 10.1C11.33 9.92 11.29 9.76 11.22 9.63C11.15 9.5 10.62 8.2 10.4 7.67C10.19 7.15 9.97 7.22 9.8 7.21C9.65 7.2 9.47 7.04 9.06 7.04Z"/>
                        </svg>
                        Hubungkan WhatsApp Sekarang
                    </button>

                    <a href="https://wa.me/{{ $botWaNumber }}?text={{ urlencode('Halo SIWIL, saya ingin menghubungkan akun ' . (Auth::user()->resident?->full_name ?? Auth::user()->username)) }}" target="_blank" class="btn btn-outline" style="display: flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.65rem; text-decoration: none; font-size: 0.85rem;">
                        Chat Langsung ke Bot (+{{ $botWaNumber }})
                    </a>
                </div>
            @endif
        </div>

        {{-- Telegram Panel --}}
        <div id="panelConnectTele" style="display: none;">
            @php
                $userTele = Auth::user()->telegram_id;
                $botUsername = config('services.telegram.bot_username', env('TELEGRAM_BOT_USERNAME', 'cevltBot'));
            @endphp

            @if($userTele)
                <div style="background: rgba(34, 158, 217, 0.1); border: 1px solid rgba(34, 158, 217, 0.3); border-radius: 12px; padding: 1rem; margin-bottom: 1.25rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.4rem;">
                        <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #229ED9;"></span>
                        <strong style="color: #229ED9; font-size: 0.9rem;">Akun Telegram Terhubung</strong>
                    </div>
                    <p style="font-size: 0.85rem; color: var(--text-secondary); margin: 0;">
                        Telegram ID: <code style="font-weight: 700; color: var(--text-primary); background: var(--bg-card); padding: 2px 6px; border-radius: 4px;">{{ $userTele }}</code>
                    </p>
                    <p style="font-size: 0.78rem; color: var(--text-muted); margin-top: 0.35rem;">
                        Bot Telegram resmi SIWIL aktif: <strong>&#64;{{ $botUsername }}</strong>
                    </p>
                </div>

                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <a href="https://t.me/{{ $botUsername }}" target="_blank" class="btn" style="background: #229ED9; color: white; display: flex; align-items: center; justify-content: center; gap: 0.5rem; text-decoration: none; padding: 0.7rem; border-radius: 10px; font-weight: 600;">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/>
                        </svg>
                        Buka Bot di Telegram (&#64;{{ $botUsername }})
                    </a>

                    <div style="display: flex; gap: 0.5rem;">
                        <button type="button" onclick="startTelegramConnect()" class="btn btn-outline" style="flex: 1; padding: 0.6rem; font-size: 0.85rem;">
                            Hubungkan Ulang
                        </button>
                        <button type="button" onclick="disconnectTelegram()" class="btn btn-outline" style="color: var(--danger); border-color: rgba(239, 68, 68, 0.3); padding: 0.6rem; font-size: 0.85rem;">
                            Putuskan
                        </button>
                    </div>
                </div>
            @else
                <p style="font-size: 0.85rem; color: var(--text-secondary); line-height: 1.5; margin-bottom: 1.25rem;">
                    Dapatkan kemudahan akses layanan desa melalui Bot Telegram resmi <strong>&#64;{{ $botUsername }}</strong>. Klik tombol di bawah ini untuk membuka bot secara langsung dan menautkan akun Anda secara otomatis.
                </p>

                <div style="display: flex; flex-direction: column; gap: 0.65rem;">
                    <button type="button" onclick="startTelegramConnect()" id="btnStartTeleConnect" class="btn" style="background: #229ED9; color: white; display: flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.75rem; font-weight: 600; border-radius: 10px;">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/>
                        </svg>
                        Hubungkan ke Telegram (&#64;{{ $botUsername }})
                    </button>

                    <div id="teleLinkBox" style="display: none; padding: 0.75rem; background: var(--bg-body); border-radius: 8px; text-align: center;">
                        <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.5rem;">Jika Telegram tidak terbuka otomatis, klik tautan ini:</p>
                        <a href="#" id="teleDirectLink" target="_blank" style="color: #229ED9; font-weight: 600; font-size: 0.85rem;">Buka Tautan Verifikasi Telegram &rarr;</a>
                    </div>
                </div>
            @endif
        </div>

    </div>
</div>

<script>
window.openConnectModal = function(tab = 'whatsapp') {
    const modal = document.getElementById('modalConnectBot');
    if (!modal) return;
    modal.style.display = 'flex';
    setTimeout(() => modal.classList.add('active'), 10);
    switchConnectTab(tab);
};

window.closeConnectModal = function() {
    const modal = document.getElementById('modalConnectBot');
    if (!modal) return;
    modal.classList.remove('active');
    setTimeout(() => { modal.style.display = 'none'; }, 200);
};

window.switchConnectTab = function(tab) {
    const tabWa = document.getElementById('tabBtnWa');
    const tabTele = document.getElementById('tabBtnTele');
    const panelWa = document.getElementById('panelConnectWa');
    const panelTele = document.getElementById('panelConnectTele');
    const headerIcon = document.getElementById('connectModalHeaderIcon');
    const title = document.getElementById('connectModalTitle');

    if (tab === 'whatsapp') {
        tabWa.style.background = 'var(--bg-card)';
        tabWa.style.color = '#25D366';
        tabWa.style.boxShadow = '0 2px 6px rgba(0,0,0,0.06)';

        tabTele.style.background = 'transparent';
        tabTele.style.color = 'var(--text-secondary)';
        tabTele.style.boxShadow = 'none';

        panelWa.style.display = 'block';
        panelTele.style.display = 'none';

        headerIcon.style.background = 'rgba(37, 211, 102, 0.15)';
        headerIcon.style.color = '#25D366';
        headerIcon.innerHTML = `<svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91C2.13 13.66 2.59 15.36 3.45 16.86L2.05 22L7.3 20.62C8.75 21.41 10.38 21.83 12.04 21.83C17.5 21.83 21.95 17.38 21.95 11.92C21.95 9.27 20.92 6.78 19.05 4.91C17.18 3.03 14.69 2 12.04 2M12.05 3.67C14.25 3.67 16.31 4.53 17.87 6.09C19.42 7.65 20.28 9.72 20.28 11.92C20.28 16.46 16.58 20.15 12.04 20.15C10.56 20.15 9.11 19.76 7.85 19L7.55 18.83L4.43 19.65L5.26 16.61L5.06 16.29C4.24 15 3.8 13.47 3.8 11.91C3.81 7.37 7.5 3.67 12.05 3.67M9.06 7.04C8.88 7.04 8.58 7.11 8.33 7.38C8.08 7.65 7.37 8.32 7.37 9.68C7.37 11.04 8.36 12.35 8.5 12.53C8.64 12.71 10.43 15.48 13.18 16.67C13.84 16.95 14.34 17.11 14.74 17.24C15.4 17.45 16.01 17.42 16.49 17.35C17.03 17.27 18.14 16.68 18.37 16.03C18.6 15.38 18.6 14.83 18.53 14.71C18.46 14.59 18.28 14.53 18.01 14.39C17.74 14.25 16.42 13.6 16.17 13.51C15.92 13.42 15.74 13.38 15.56 13.65C15.38 13.92 14.86 14.53 14.7 14.71C14.54 14.89 14.38 14.91 14.11 14.78C13.84 14.64 12.97 14.36 11.94 13.44C11.14 12.72 10.6 11.84 10.44 11.57C10.28 11.3 10.42 11.15 10.56 11.02C10.68 10.9 10.83 10.71 10.97 10.55C11.11 10.39 11.15 10.28 11.24 10.1C11.33 9.92 11.29 9.76 11.22 9.63C11.15 9.5 10.62 8.2 10.4 7.67C10.19 7.15 9.97 7.22 9.8 7.21C9.65 7.2 9.47 7.04 9.06 7.04Z"/></svg>`;
        title.innerText = 'Hubungkan Akun WhatsApp';
    } else {
        tabTele.style.background = 'var(--bg-card)';
        tabTele.style.color = '#229ED9';
        tabTele.style.boxShadow = '0 2px 6px rgba(0,0,0,0.06)';

        tabWa.style.background = 'transparent';
        tabWa.style.color = 'var(--text-secondary)';
        tabWa.style.boxShadow = 'none';

        panelTele.style.display = 'block';
        panelWa.style.display = 'none';

        headerIcon.style.background = 'rgba(34, 158, 217, 0.15)';
        headerIcon.style.color = '#229ED9';
        headerIcon.innerHTML = `<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>`;
        title.innerText = 'Hubungkan Bot Telegram';
    }
};

window.showChangeWaForm = function() {
    const box = document.getElementById('changeWaFormBox');
    if (box) {
        box.style.display = box.style.display === 'none' ? 'block' : 'none';
    }
};

window.submitConnectWa = async function(inputId) {
    const input = document.getElementById(inputId);
    if (!input || !input.value.trim()) {
        alert('Harap masukkan nomor WhatsApp yang valid.');
        return;
    }

    try {
        const response = await fetch('{{ route("settings.whatsapp.connect") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ whatsapp_number: input.value.trim() })
        });

        const data = await response.json();
        if (response.ok && data.success) {
            alert(data.message);
            if (data.wa_link) {
                window.open(data.wa_link, '_blank');
            }
            window.location.reload();
        } else {
            alert(data.error || 'Gagal menghubungkan WhatsApp.');
        }
    } catch (err) {
        console.error(err);
        alert('Terjadi kesalahan jaringan.');
    }
};

window.disconnectWhatsApp = async function() {
    const confirmed = await window.siwilConfirm({
        title: 'Putuskan Koneksi WhatsApp',
        message: 'Apakah Anda yakin ingin memutuskan koneksi akun WhatsApp? Notifikasi pesan tidak akan dikirim lagi ke nomor ini.',
        confirmText: 'Ya, Putuskan',
        cancelText: 'Batal',
        type: 'danger'
    });
    if (!confirmed) return;
    try {
        const res = await fetch('{{ route("settings.whatsapp.disconnect") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        const data = await res.json();
        if (res.ok) {
            alert(data.message || 'Koneksi WhatsApp diputuskan.');
            window.location.reload();
        }
    } catch (err) {
        console.error(err);
        alert('Terjadi kesalahan.');
    }
};

window.startTelegramConnect = async function() {
    const btn = document.getElementById('btnStartTeleConnect');
    if (btn) {
        btn.disabled = true;
        btn.innerText = 'Menghasilkan Tautan Telegram...';
    }

    try {
        const res = await fetch('/dashboard/settings/telegram/generate-token', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        const data = await res.json();
        if (res.ok && data.deep_link) {
            const linkBox = document.getElementById('teleLinkBox');
            const directLink = document.getElementById('teleDirectLink');
            if (directLink) directLink.href = data.deep_link;
            if (linkBox) linkBox.style.display = 'block';

            window.open(data.deep_link, '_blank');
        } else {
            alert(data.error || 'Gagal menghasilkan token koneksi Telegram.');
        }
    } catch (err) {
        console.error(err);
        alert('Gagal menghubungkan Telegram.');
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = `<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg> Hubungkan ke Telegram`;
        }
    }
};

window.disconnectTelegram = async function() {
    const confirmed = await window.siwilConfirm({
        title: 'Putuskan Akun Telegram',
        message: 'Apakah Anda yakin ingin memutuskan akun Telegram dari SIWIL? Bot tidak akan dapat mengirim pembaruan informasi ke akun Telegram Anda.',
        confirmText: 'Ya, Putuskan',
        cancelText: 'Batal',
        type: 'danger'
    });
    if (!confirmed) return;
    try {
        const res = await fetch('{{ route("settings.telegram.disconnect") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        const data = await res.json();
        if (res.ok) {
            alert(data.message || 'Koneksi Telegram diputuskan.');
            window.location.reload();
        }
    } catch (err) {
        console.error(err);
        alert('Terjadi kesalahan.');
    }
};
</script>
