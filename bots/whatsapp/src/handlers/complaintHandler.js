import { siwilApi } from '../api/siwilClient.js';
import { normalizePhoneNumber, formatComplaintStatus } from '../utils/formatter.js';
import { sendButton } from '../utils/buttonHelper.js';
import { userSession } from '../state/userSession.js';
import { authHandler } from './authHandler.js';

export const complaintHandler = {
  /**
   * Start complaint submission wizard or parse single line format
   */
  async startComplaint(sock, jid, text) {
    const user = await authHandler.ensureAuthenticated(sock, jid);
    if (!user) return;

    // Check if quick format used: "lapor Judul # Deskripsi # Lokasi"
    if (text.includes('#')) {
      return this.handleQuickComplaint(sock, jid, text);
    }

    userSession.set(jid, 'AWAITING_COMPLAINT_TITLE', {});

    const prompt = `Sampaikan pengaduan lingkungan langsung ke pengurus RT/RW.\n\n` +
      `*Langkah 1/3:* Masukkan Topik / Judul Pengaduan\n` +
      `_Contoh: Lampu Jalan Dekat Pos Ronda Padam_`;

    await sendButton(
      sock,
      jid,
      'Pengaduan Warga',
      prompt,
      'Ketik judul pengaduan',
      [
        { name: 'quick_reply', displayText: '📋 Cek Status Laporan', id: 'menu_status_lapor' },
        { name: 'quick_reply', displayText: '❌ Batalkan', id: 'action_batal' }
      ]
    );
  },

  /**
   * Quick complaint: "lapor Lampu Jalan Rusak # Sudah 3 hari mati # Depan No. 12"
   */
  async handleQuickComplaint(sock, jid, text) {
    const clean = text.replace(/^(lapor|pengaduan)\s*/i, '');
    const parts = clean.split('#').map(p => p.trim());

    if (parts.length < 2) {
      await sendButton(
        sock,
        jid,
        'Format Belum Lengkap',
        `Format cepat pengaduan:\n*lapor [Judul] # [Keterangan] # [Lokasi]*\n\n_Contoh:_\n*lapor Saluran Mampet # Sampah menumpuk # Depan RT 02*`,
        'Pilih opsi di bawah',
        [
          { name: 'quick_reply', displayText: '📝 Gunakan Panduan', id: 'lapor_baru' },
          { name: 'quick_reply', displayText: '🏠 Menu Utama', id: 'menu_utama' }
        ]
      );
      return;
    }

    const title = parts[0];
    const description = parts[1];
    const location = parts[2] || '';

    await this.submitComplaint(sock, jid, title, description, location);
  },

  /**
   * Wizard Step 1: Handle Complaint Title
   */
  async handleTitleStep(sock, jid, text) {
    const title = text.trim();
    if (!title || title === 'action_batal') {
      userSession.clear(jid);
      await sendButton(
        sock,
        jid,
        'Dibatalkan',
        'Pengaduan telah dibatalkan.',
        'SIWIL Pengaduan',
        [{ name: 'quick_reply', displayText: '🏠 Menu Utama', id: 'menu_utama' }]
      );
      return;
    }

    userSession.set(jid, 'AWAITING_COMPLAINT_DESC', { title });

    const prompt = `*Topik:* *${title}*\n\n` +
      `*Langkah 2/3:* Tuliskan rincian atau kronologi permasalahan secara jelas:`;

    await sendButton(
      sock,
      jid,
      'Rincian Pengaduan',
      prompt,
      'Ketik keterangan permasalahan',
      [{ name: 'quick_reply', displayText: '❌ Batalkan', id: 'action_batal' }]
    );
  },

  /**
   * Wizard Step 2: Handle Complaint Description
   */
  async handleDescStep(sock, jid, text, session) {
    const description = text.trim();
    if (!description || description === 'action_batal') {
      userSession.clear(jid);
      await sendButton(
        sock,
        jid,
        'Dibatalkan',
        'Pengaduan dibatalkan.',
        'SIWIL Pengaduan',
        [{ name: 'quick_reply', displayText: '🏠 Menu Utama', id: 'menu_utama' }]
      );
      return;
    }

    const title = session.data?.title;
    userSession.set(jid, 'AWAITING_COMPLAINT_LOC', { title, description });

    const prompt = `*Langkah 3/3:* Tuliskan lokasi kejadian (misal: *Depan Rumah No. 12 RT 01*).\n\n` +
      `> Tekan *Lewati Lokasi* jika di area umum atau rumah Anda sendiri.`;

    await sendButton(
      sock,
      jid,
      'Lokasi Kejadian',
      prompt,
      'Ketik lokasi atau tekan Lewati',
      [
        { name: 'quick_reply', displayText: '➡️ Lewati Lokasi', id: 'loc_skip' },
        { name: 'quick_reply', displayText: '❌ Batalkan', id: 'action_batal' }
      ]
    );
  },

  /**
   * Wizard Step 3: Handle Location & Submit
   */
  async handleLocationStep(sock, jid, text, session) {
    let location = text.trim();
    if (location.toLowerCase() === 'lewati' || location === '-' || location === 'loc_skip') {
      location = '';
    }

    const { title, description } = session.data || {};
    if (!title || !description) {
      userSession.clear(jid);
      await sendButton(
        sock,
        jid,
        'Sesi Berakhir',
        'Sesi pengaduan telah berakhir. Silakan ulangi kembali.',
        'SIWIL Pengaduan',
        [{ name: 'quick_reply', displayText: '📝 Buat Pengaduan Baru', id: 'menu_lapor' }]
      );
      return;
    }

    await this.submitComplaint(sock, jid, title, description, location);
  },

  /**
   * Submit to API
   */
  async submitComplaint(sock, jid, title, description, location) {
    const phone = normalizePhoneNumber(jid);

    try {
      await sock.sendPresenceUpdate('composing', jid);
    } catch (_) {}

    const result = await siwilApi.createComplaint(phone, title, description, location);
    userSession.clear(jid);

    if (!result.success) {
      await sendButton(
        sock,
        jid,
        'Pengaduan Gagal',
        `❌ *Pengaduan Gagal Dikirim*\n\n${result.error}\n\nSilakan coba beberapa saat lagi.`,
        'SIWIL Pengaduan',
        [
          { name: 'quick_reply', displayText: '🔄 Coba Lagi', id: 'menu_lapor' },
          { name: 'quick_reply', displayText: '🏠 Menu Utama', id: 'menu_utama' }
        ]
      );
      return;
    }

    const c = result.data.data;
    const msg = `✅ *Laporan Pengaduan Berhasil Dikirim*\n\n` +
      `• *Topik:* *${c.title}*\n` +
      `• *Lokasi:* ${c.location || 'Wilayah Lingkungan'}\n` +
      `• *Status:* Menunggu Tindak Lanjut (Pending)\n` +
      `• *Waktu:* ${c.created_at} WIB\n\n` +
      `> Laporan telah diteruskan ke pengurus RT/RW. Perkembangan penanganan akan otomatis dinotifikasikan ke WhatsApp ini.`;

    await sendButton(
      sock,
      jid,
      'Pengaduan Berhasil',
      msg,
      'SIWIL Pengaduan',
      [
        { name: 'quick_reply', displayText: '📋 Cek Status Laporan', id: 'menu_status_lapor' },
        { name: 'quick_reply', displayText: '🏠 Menu Utama', id: 'menu_utama' }
      ]
    );
  },

  /**
   * List complaints history
   */
  async handleListComplaints(sock, jid) {
    const user = await authHandler.ensureAuthenticated(sock, jid);
    if (!user) return;

    try {
      await sock.sendPresenceUpdate('composing', jid);
    } catch (_) {}

    const phone = normalizePhoneNumber(jid);
    const result = await siwilApi.getComplaints(phone);

    if (!result.success) {
      await sendButton(
        sock,
        jid,
        'Gagal Memuat',
        '❌ *Gagal Memuat Riwayat Pengaduan*\n\nSilakan coba beberapa saat lagi.',
        'SIWIL Pengaduan',
        [{ name: 'quick_reply', displayText: '🏠 Menu Utama', id: 'menu_utama' }]
      );
      return;
    }

    const complaints = result.data;
    if (!complaints || complaints.length === 0) {
      await sendButton(
        sock,
        jid,
        'Belum Ada Aduan',
        '📭 *Belum Ada Riwayat Pengaduan*\n\nAnda belum pernah menyampaikan laporan pengaduan.',
        'SIWIL Pengaduan',
        [
          { name: 'quick_reply', displayText: '📢 Sampaikan Aduan', id: 'menu_lapor' },
          { name: 'quick_reply', displayText: '🏠 Menu Utama', id: 'menu_utama' }
        ]
      );
      return;
    }

    let msg = `*Riwayat Pengaduan Anda:*\n\n`;

    complaints.slice(0, 5).forEach((c, idx) => {
      const statusBadge = formatComplaintStatus(c.status);
      msg += `${idx + 1}. *${c.title}*\n` +
        `   • Status: ${statusBadge}\n` +
        `   • Lokasi: ${c.location || '-'}\n` +
        `   • Tanggal: ${c.date}\n\n`;
    });

    await sendButton(
      sock,
      jid,
      '📢 Riwayat Pengaduan Warga',
      msg.trim(),
      'Ketik 3 untuk lapor baru, atau menu untuk kembali',
      [
        { name: 'quick_reply', displayText: 'Sampaikan Aduan Baru', id: '3' },
        { name: 'quick_reply', displayText: 'Menu Utama', id: 'menu' }
      ]
    );
  }
};
