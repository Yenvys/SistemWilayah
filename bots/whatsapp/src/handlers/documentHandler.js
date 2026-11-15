import { siwilApi } from '../api/siwilClient.js';
import { normalizePhoneNumber, formatDocumentStatus } from '../utils/formatter.js';
import { sendButton } from '../utils/buttonHelper.js';
import { cacheMessage } from '../utils/store.js';
import { userSession } from '../state/userSession.js';
import { authHandler } from './authHandler.js';

export const documentHandler = {
  /**
   * Start document request wizard: display available document types
   */
  async startDocumentRequest(sock, jid) {
    const user = await authHandler.ensureAuthenticated(sock, jid);
    if (!user) return;

    try {
      await sock.sendPresenceUpdate('composing', jid);
    } catch (_) {}

    const res = await siwilApi.getDocumentServices();
    if (!res.success || res.data.length === 0) {
      await sendButton(
        sock,
        jid,
        'Layanan Tidak Tersedia',
        'Daftar jenis surat tidak dapat dimuat saat ini. Silakan coba kembali nanti.',
        'SIWIL Layanan Surat',
        [{ name: 'quick_reply', displayText: '🏠 Menu Utama', id: 'menu_utama' }]
      );
      return;
    }

    const services = res.data;

    const listSectionRows = services.map((s, idx) => ({
      title: s.name,
      description: `Pengajuan ${s.name}`,
      id: String(idx + 1)
    }));

    userSession.set(jid, 'AWAITING_DOC_TYPE', { services });

    const bodyText = `Pilih jenis surat resmi yang ingin Anda ajukan:\n\n` +
      `_Surat yang diajukan akan langsung masuk ke antrean verifikasi Ketua RT/RW._`;

    await sendButton(
      sock,
      jid,
      '📄 Pengajuan Surat Warga',
      bodyText,
      'Ketik nomor pilihan di atas atau ketik batal.',
      [
        {
          name: 'single_select',
          displayText: 'Daftar Jenis Surat',
          sections: [
            {
              title: 'Layanan Surat Tersedia',
              rows: listSectionRows
            }
          ]
        },
        { name: 'quick_reply', displayText: 'Riwayat Surat', id: '2' },
        { name: 'quick_reply', displayText: 'Batalkan Pengajuan', id: 'batal' }
      ]
    );
  },

  /**
   * Handle direct selection via single_select button
   */
  async handleDirectDocType(sock, jid, code) {
    const res = await siwilApi.getDocumentServices();
    const services = res.data || [];
    const selectedService = services.find(s => s.code === code) || {
      code,
      name: code.replace(/_/g, ' ')
    };

    userSession.set(jid, 'AWAITING_DOC_NOTES', { selectedService });

    const prompt = `*Jenis Surat Dipilih:* *${selectedService.name}*\n\n` +
      `Silakan ketik *keterangan atau keperluan* surat ini (misal: melamar kerja, buka rekening, dll).\n\n` +
      `👉 Ketik *lewati* jika tidak ada catatan khusus, atau ketik *batal* untuk membatalkan.`;

    await sendButton(
      sock,
      jid,
      '📝 Keperluan Surat',
      prompt,
      "Ketik keterangan Anda atau ketik 'lewati'",
      [
        { name: 'quick_reply', displayText: 'Lewati Catatan', id: 'lewati' },
        { name: 'quick_reply', displayText: 'Batalkan Pengajuan', id: 'batal' }
      ]
    );
  },

  /**
   * Handle user choosing document type by typing number or code
   */
  async handleChooseDocType(sock, jid, text, session) {
    const clean = text.trim();
    if (clean.startsWith('doc_type_')) {
      const code = clean.replace('doc_type_', '');
      return await this.handleDirectDocType(sock, jid, code);
    }

    const choice = parseInt(clean, 10);
    const services = session.data?.services || [];

    if (isNaN(choice) || choice < 1 || choice > services.length) {
      await sendButton(
        sock,
        jid,
        'Pilihan Tidak Valid',
        `Silakan pilih nomor 1 sampai ${services.length}, atau buka daftar surat:`,
        'Pilih opsi di bawah',
        [
          { name: 'quick_reply', displayText: '📄 Buka Daftar Surat', id: 'menu_surat' },
          { name: 'quick_reply', displayText: '❌ Batalkan', id: 'action_batal' }
        ]
      );
      return;
    }

    const selectedService = services[choice - 1];
    return await this.handleDirectDocType(sock, jid, selectedService.code);
  },

  /**
   * Handle user providing notes & submit document request
   */
  async handleSubmitDocRequest(sock, jid, text, session) {
    const selectedService = session.data?.selectedService;
    if (!selectedService) {
      userSession.clear(jid);
      await sendButton(
        sock,
        jid,
        'Sesi Berakhir',
        'Sesi pengajuan surat telah berakhir. Silakan mulai kembali.',
        'SIWIL Layanan Surat',
        [{ name: 'quick_reply', displayText: '📄 Ajukan Surat Baru', id: 'menu_surat' }]
      );
      return;
    }

    let notes = text.trim();
    if (notes.toLowerCase() === 'lewati' || notes === '-' || notes === 'notes_skip') {
      notes = '';
    }

    const phone = normalizePhoneNumber(jid);

    try {
      await sock.sendPresenceUpdate('composing', jid);
    } catch (_) {}

    const result = await siwilApi.createDocumentRequest(phone, selectedService.code, notes);
    userSession.clear(jid);

    if (!result.success) {
      await sendButton(
        sock,
        jid,
        'Pengajuan Gagal',
        `❌ *Pengajuan Gagal*\n\n${result.error}\n\nPastikan data identitas/NIK Anda sudah lengkap.`,
        'SIWIL Layanan Surat',
        [
          { name: 'quick_reply', displayText: '🔄 Coba Lagi', id: 'menu_surat' },
          { name: 'quick_reply', displayText: '🏠 Menu Utama', id: 'menu_utama' }
        ]
      );
      return;
    }

    const d = result.data.data;
    const successText = `✅ *Pengajuan Surat Berhasil*\n\n` +
      `• *Nomor:* \`${d.request_number}\`\n` +
      `• *Layanan:* ${d.type}\n` +
      `• *Tahap:* Menunggu Verifikasi RT\n` +
      `• *Waktu:* ${d.created_at} WIB\n\n` +
      `> Pengajuan telah masuk antrean Ketua RT. Notifikasi & PDF printable akan dikirimkan otomatis saat disetujui.`;

    await sendButton(
      sock,
      jid,
      'Pengajuan Surat Berhasil',
      successText,
      'SIWIL Layanan Surat',
      [
        { name: 'quick_reply', displayText: '📋 Cek Status Permohonan', id: 'menu_status_surat' },
        { name: 'quick_reply', displayText: '📄 Buat Surat Lain', id: 'menu_surat' },
        { name: 'quick_reply', displayText: '🏠 Menu Utama', id: 'menu_utama' }
      ]
    );
  },

  /**
   * Display document requests status list with direct download buttons
   */
  async handleListDocuments(sock, jid) {
    const user = await authHandler.ensureAuthenticated(sock, jid);
    if (!user) return;

    try {
      await sock.sendPresenceUpdate('composing', jid);
    } catch (_) {}

    const phone = normalizePhoneNumber(jid);
    const result = await siwilApi.getDocumentRequests(phone);

    if (!result.success) {
      await sendButton(
        sock,
        jid,
        'Gagal Memuat Riwayat',
        '❌ *Gagal Memuat Riwayat*\n\nTidak dapat mengambil daftar surat Anda saat ini.',
        'SIWIL Layanan Surat',
        [{ name: 'quick_reply', displayText: '🏠 Menu Utama', id: 'menu_utama' }]
      );
      return;
    }

    const docs = result.data;
    if (!docs || docs.length === 0) {
      await sendButton(
        sock,
        jid,
        'Belum Ada Surat',
        '📭 *Belum Ada Pengajuan Surat*\n\nAnda belum memiliki riwayat permohonan surat di sistem SIWIL.',
        'SIWIL Layanan Surat',
        [
          { name: 'quick_reply', displayText: '📄 Ajukan Surat Sekarang', id: 'menu_surat' },
          { name: 'quick_reply', displayText: '🏠 Menu Utama', id: 'menu_utama' }
        ]
      );
      return;
    }

    let msg = `*Riwayat Pengajuan Surat Anda:*\n\n`;
    const buttons = [];

    docs.slice(0, 5).forEach((doc, idx) => {
      const statusBadge = formatDocumentStatus(doc.status);
      msg += `${idx + 1}. *${doc.type}*\n` +
        `   • Nomor: \`${doc.request_number}\`\n` +
        `   • Status: ${statusBadge}\n` +
        `   • Tanggal: ${doc.date}\n\n`;

      if (doc.can_download && buttons.length < 2) {
        buttons.push({
          name: 'quick_reply',
          displayText: `📥 Unduh PDF (${doc.type})`,
          id: `unduh ${doc.request_number}`
        });
      }
    });

    buttons.push({ name: 'quick_reply', displayText: 'Ajukan Surat Baru', id: '1' });
    buttons.push({ name: 'quick_reply', displayText: 'Menu Utama', id: 'menu' });

    await sendButton(
      sock,
      jid,
      '📋 Riwayat Pengajuan Surat',
      msg.trim(),
      'Ketik perintah di atas (contoh: unduh [nomor] atau 1)',
      buttons
    );
  },

  /**
   * Download and send printable PDF directly via WhatsApp
   */
  async handleDownloadPdf(sock, jid, text) {
    const user = await authHandler.ensureAuthenticated(sock, jid);
    if (!user) return;

    const parts = text.trim().split(/\s+/);
    let identifier = '';
    if (parts.length >= 2) {
      identifier = parts[1].trim();
    } else if (text.startsWith('unduh_') || text.startsWith('download_')) {
      identifier = text.replace(/^(unduh_|download_)/i, '').trim();
    }

    if (!identifier) {
      await sendButton(
        sock,
        jid,
        'Nomor Surat Dibutuhkan',
        'Pilih surat yang ingin diunduh melalui riwayat permohonan surat Anda.',
        'SIWIL Layanan Surat',
        [{ name: 'quick_reply', displayText: '📋 Buka Daftar Surat', id: 'menu_status_surat' }]
      );
      return;
    }

    try {
      await sock.sendPresenceUpdate('composing', jid);
    } catch (_) {}

    const res = await siwilApi.downloadDocumentPdf(identifier);

    if (!res.success || !res.data?.base64) {
      await sendButton(
        sock,
        jid,
        'Gagal Mengunduh',
        `❌ *Gagal Mengunduh Dokumen*\n\n${res.error || 'Surat belum disetujui atau nomor surat tidak valid.'}`,
        'SIWIL Layanan Surat',
        [
          { name: 'quick_reply', displayText: '📋 Cek Status Surat', id: 'menu_status_surat' },
          { name: 'quick_reply', displayText: '🏠 Menu Utama', id: 'menu_utama' }
        ]
      );
      return;
    }

    const pdfBuffer = Buffer.from(res.data.base64, 'base64');
    const filename = res.data.filename || `Surat_${identifier}.pdf`;
    const docInfo = res.data.doc;

    const caption = `*Dokumen Resmi SIWIL*\n\n` +
      `• *Nomor:* \`${docInfo?.request_number || identifier}\`\n` +
      `• *Layanan:* ${docInfo?.type || 'Surat Keterangan'}\n` +
      `• *Status:* ✅ *Sah & Selesai (Printable A4)*\n\n` +
      `> Dokumen resmi siap langsung dicetak pada kertas A4.`;

    try {
      const sentDoc = await sock.sendMessage(jid, {
        document: pdfBuffer,
        mimetype: 'application/pdf',
        fileName: filename,
        caption: caption
      });

      if (sentDoc?.key?.id && sentDoc.message) {
        cacheMessage(sentDoc.key.id, sentDoc.message);
      }
    } catch (err) {
      await sendButton(
        sock,
        jid,
        'Pengiriman Gagal',
        `❌ *Gagal Mengirim File:*\n${err.message}`,
        'SIWIL Layanan Surat',
        [{ name: 'quick_reply', displayText: '🏠 Menu Utama', id: 'menu_utama' }]
      );
    }
  }
};
