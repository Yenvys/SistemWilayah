import { siwilApi } from '../api/siwilClient.js';
import { normalizePhoneNumber } from '../utils/formatter.js';
import { sendButton } from '../utils/buttonHelper.js';
import { userSession } from '../state/userSession.js';

export const authHandler = {
  /**
   * Check user status and return user data or prompt login
   */
  async ensureAuthenticated(sock, jid) {
    const phone = normalizePhoneNumber(jid);
    const result = await siwilApi.checkUser(phone);

    if (result.success && result.data?.linked) {
      return result.data.user;
    }

    const msg = `Nomor WhatsApp Anda (*+${phone}*) belum terhubung dengan akun SIWIL.\n\n` +
      `Silakan login menggunakan salah satu identitas:\n` +
      `• *NIK:* \`login 3201016666666666 rahasia123\`\n` +
      `• *Email:* \`login warga@email.com rahasia123\`\n` +
      `• *No. HP:* \`login 081234567890 rahasia123\`\n\n` +
      `_Format: *login [NIK/Email/No.HP] [Password]*_`;

    await sendButton(
      sock,
      jid,
      'Akun Belum Terhubung',
      msg,
      'SIWIL Layanan Warga',
      [
        { name: 'quick_reply', displayText: 'Menu Utama', id: 'menu' },
        { name: 'quick_reply', displayText: 'Layanan Surat', id: '1' }
      ]
    );
    return null;
  },

  /**
   * Handle user login (accepts NIK, Email, or Phone Number)
   */
  async handleLogin(sock, jid, text) {
    const parts = text.trim().split(/\s+/);
    const phone = normalizePhoneNumber(jid);

    if (parts.length < 3) {
      const helpMsg = `Gunakan format login berikut:\n\n` +
        `*login [NIK / Email / No. HP] [Password]*\n\n` +
        `*Contoh:*\n` +
        `• NIK: \`login 3201016666666666 rahasia123\`\n` +
        `• Email: \`login warga@email.com rahasia123\`\n` +
        `• No. HP: \`login 081234567890 rahasia123\``;

      await sendButton(
        sock,
        jid,
        'Format Login SIWIL',
        helpMsg,
        'Ketik login [identitas] [password]',
        [{ name: 'quick_reply', displayText: 'Menu Utama', id: 'menu' }]
      );
      return;
    }

    const loginId = parts[1];
    const password = parts.slice(2).join(' ');

    try {
      await sock.sendPresenceUpdate('composing', jid);
    } catch (_) {}

    const result = await siwilApi.login(loginId, password, phone);

    if (!result.success) {
      await sendButton(
        sock,
        jid,
        'Login Gagal',
        `❌ *Gagal Terhubung*\n\n${result.error}\n\nPastikan NIK, Email, atau No. HP dan password sudah benar.`,
        'SIWIL Layanan Warga',
        [
          { name: 'quick_reply', displayText: '🔑 Petunjuk Login', id: 'login_help' },
          { name: 'quick_reply', displayText: '🏠 Menu Utama', id: 'menu_utama' }
        ]
      );
      return;
    }

    userSession.clear(jid);
    const user = result.data.user;

    const successMsg = `✅ *Login Berhasil*\n\n` +
      `Selamat datang, *${user.name}*!\n` +
      `• *NIK:* \`${user.nik || user.username}\`\n` +
      `• *Wilayah:* RT ${user.rt} / RW ${user.rw}, ${user.village}\n` +
      `• *WhatsApp:* \`+${phone}\`\n\n` +
      `Akun Anda telah terhubung. Silakan pilih layanan di bawah:`;

    await sendButton(
      sock,
      jid,
      'Login Berhasil',
      successMsg,
      'SIWIL Layanan Warga',
      [
        { name: 'quick_reply', displayText: '📄 Pengajuan Surat', id: 'menu_surat' },
        { name: 'quick_reply', displayText: '📢 Pengaduan Warga', id: 'menu_lapor' },
        { name: 'quick_reply', displayText: '🗳️ Polling Warga', id: 'menu_polling' }
      ]
    );
  },

  /**
   * Handle user logout
   */
  async handleLogout(sock, jid) {
    const phone = normalizePhoneNumber(jid);

    try {
      await sock.sendPresenceUpdate('composing', jid);
    } catch (_) {}

    const result = await siwilApi.logout(phone);
    userSession.clear(jid);

    if (result.success) {
      await sendButton(
        sock,
        jid,
        'Logout Berhasil',
        `🔒 *Berhasil Logout*\n\nNomor WhatsApp Anda telah diputuskan dari akun SIWIL.\nKetik *login* kapan saja untuk masuk kembali.`,
        'SIWIL Layanan Warga',
        [{ name: 'quick_reply', displayText: 'Menu Utama', id: 'menu' }]
      );
    } else {
      await sendButton(
        sock,
        jid,
        'Gagal Logout',
        `❌ *Gagal Logout*\n\n${result.error || 'Terjadi kesalahan sistem.'}`,
        'SIWIL Layanan Warga',
        [{ name: 'quick_reply', displayText: 'Menu Utama', id: 'menu' }]
      );
    }
  },

  /**
   * Show user profile
   */
  async handleProfile(sock, jid) {
    const phone = normalizePhoneNumber(jid);
    const result = await siwilApi.checkUser(phone);

    if (!result.success || !result.data?.linked) {
      await this.ensureAuthenticated(sock, jid);
      return;
    }

    const u = result.data.user;
    const msg = `*Profil Warga SIWIL*\n\n` +
      `• *Nama:* *${u.name}*\n` +
      `• *NIK:* \`${u.username}\`\n` +
      `• *Peran:* ${u.role}\n` +
      `• *Wilayah:* RT ${u.rt} / RW ${u.rw}, ${u.village}\n` +
      `• *WhatsApp:* \`+${phone}\`\n\n` +
      `> Data ini digunakan untuk permohonan surat & administrasi lingkungan.`;

    await sendButton(
      sock,
      jid,
      'Profil Akun',
      msg,
      'SIWIL Layanan Warga',
      [
        { name: 'quick_reply', displayText: '📄 Pengajuan Surat', id: 'menu_surat' },
        { name: 'quick_reply', displayText: '📋 Status Surat', id: 'menu_status_surat' },
        { name: 'quick_reply', displayText: '🔓 Logout Akun', id: 'action_logout' },
        { name: 'quick_reply', displayText: '🏠 Menu Utama', id: 'menu_utama' }
      ]
    );
  }
};
