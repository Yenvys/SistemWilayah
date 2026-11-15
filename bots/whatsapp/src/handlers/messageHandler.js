import { jidNormalizedUser } from '@whiskeysockets/baileys';
import { userSession } from '../state/userSession.js';
import { authHandler } from './authHandler.js';
import { documentHandler } from './documentHandler.js';
import { complaintHandler } from './complaintHandler.js';
import { pollHandler } from './pollHandler.js';
import { siwilApi } from '../api/siwilClient.js';
import { normalizePhoneNumber, extractMessageContent, formatToJid } from '../utils/formatter.js';
import { sendButton, sendInteractiveMessage } from '../utils/buttonHelper.js';
import { logger } from '../utils/logger.js';

export async function handleIncomingMessage(sock, msg) {
  try {
    if (!msg.message || msg.key.fromMe) return;

    const rawJid = msg.key.remoteJid;
    if (!rawJid || rawJid.endsWith('@g.us') || rawJid === 'status@broadcast') {
      return;
    }

    const jid = jidNormalizedUser(rawJid);
    const phone = normalizePhoneNumber(rawJid);
    const text = extractMessageContent(msg);
    if (!text) return;

    logger.info(`Message/Button from ${phone} (${jid}): "${text}"`);

    const lower = text.trim().toLowerCase();

    // 1. Cancel action (batal / cancel)
    if (lower === 'batal' || lower === 'cancel' || lower === 'action_batal') {
      userSession.clear(jid);
      await sendInteractiveMessage(sock, jid, {
        title: '❌ Tindakan Dibatalkan',
        body: 'Tindakan sebelumnya telah dibatalkan.',
        footer: 'SIWIL Layanan Warga',
        buttons: [
          { name: 'quick_reply', id: 'menu', displayText: '🏠 Menu Utama' },
          { name: 'quick_reply', id: '1', displayText: '📄 Pengajuan Surat' }
        ]
      });
      return;
    }

    // 2. Greetings and Main Menu triggers
    const isGreeting = [
      'menu', 'menu_utama', 'bantuan', 'help', 'start', 'mulai', 'p', 'ping', 'tes', 'test', 'info'
    ].includes(lower) ||
      lower.startsWith('halo') ||
      lower.startsWith('hai') ||
      lower.startsWith('hi ') ||
      lower === 'hi' ||
      lower.startsWith('assalam');

    if (isGreeting) {
      userSession.clear(jid);
      return await sendMainMenu(sock, jid);
    }

    // 3. Check ongoing conversational state
    const session = userSession.get(jid);
    if (session) {
      switch (session.state) {
        case 'AWAITING_DOC_TYPE':
          return await documentHandler.handleChooseDocType(sock, jid, text, session);
        case 'AWAITING_DOC_NOTES':
          return await documentHandler.handleSubmitDocRequest(sock, jid, text, session);
        case 'AWAITING_COMPLAINT_TITLE':
          return await complaintHandler.handleTitleStep(sock, jid, text);
        case 'AWAITING_COMPLAINT_DESC':
          return await complaintHandler.handleDescStep(sock, jid, text, session);
        case 'AWAITING_COMPLAINT_LOC':
          return await complaintHandler.handleLocationStep(sock, jid, text, session);
      }
    }

    // 4. Command & Button Action Routers

    // Direct Button Click: Document Selection from List
    if (lower.startsWith('doc_type_')) {
      const code = text.replace(/^doc_type_/i, '').trim();
      return await documentHandler.handleDirectDocType(sock, jid, code);
    }

    // Direct Button Click: Download PDF
    if (lower.startsWith('unduh_') || lower.startsWith('download_')) {
      const docNum = text.replace(/^(unduh_|download_)/i, '').trim();
      return await documentHandler.handleDownloadPdf(sock, jid, `unduh ${docNum}`);
    }

    // Direct Button Click: Vote Poll
    if (lower.startsWith('vote_')) {
      // format: vote_{pollId}_{optionNumber}
      const parts = text.split('_');
      if (parts.length >= 3) {
        const pollId = parts[1];
        const optionNum = parts[2];
        return await pollHandler.handleVote(sock, jid, `vote ${pollId} ${optionNum}`);
      }
    }

    // Auth (Login with NIK, Email, or Phone Number)
    if (lower === 'login_help' || lower === 'help_login') {
      return await authHandler.ensureAuthenticated(sock, jid);
    }

    if (lower.startsWith('login')) {
      return await authHandler.handleLogin(sock, jid, text);
    }

    if (lower === 'logout' || lower === 'keluar' || lower === 'action_logout') {
      return await authHandler.handleLogout(sock, jid);
    }

    if (lower === 'profil' || lower === 'profile' || lower === 'akun' || lower === 'menu_profil' || lower === '6') {
      return await authHandler.handleProfile(sock, jid);
    }

    // Layanan Surat
    if (lower === '1' || lower === 'surat' || lower === 'ajukan surat' || lower === 'menu_surat') {
      return await documentHandler.startDocumentRequest(sock, jid);
    }

    if (
      lower === '2' ||
      lower === 'status' ||
      lower === 'status surat' ||
      lower === 'cek surat' ||
      lower === 'surat status' ||
      lower === 'surat_status' ||
      lower === 'menu_status_surat'
    ) {
      return await documentHandler.handleListDocuments(sock, jid);
    }

    if (lower.startsWith('unduh') || lower.startsWith('download')) {
      return await documentHandler.handleDownloadPdf(sock, jid, text);
    }

    // Layanan Pengaduan
    if (
      lower === '3' ||
      lower === 'lapor' ||
      lower === 'pengaduan' ||
      lower === 'menu_lapor' ||
      lower === 'lapor_baru' ||
      lower.startsWith('lapor ')
    ) {
      return await complaintHandler.startComplaint(sock, jid, text);
    }

    if (
      lower === '4' ||
      lower === 'status lapor' ||
      lower === 'cek pengaduan' ||
      lower === 'status pengaduan' ||
      lower === 'lapor_status' ||
      lower === 'menu_status_lapor'
    ) {
      return await complaintHandler.handleListComplaints(sock, jid);
    }

    // Layanan Polling & Musyawarah
    if (
      lower === '5' ||
      lower === 'polling' ||
      lower === 'musyawarah' ||
      lower === 'menu_polling' ||
      lower === 'vote'
    ) {
      if (lower.startsWith('vote ') && lower.split(/\s+/).length >= 3) {
        return await pollHandler.handleVote(sock, jid, text);
      }
      return await pollHandler.handleListPolls(sock, jid);
    }

    if (lower.startsWith('vote ')) {
      return await pollHandler.handleVote(sock, jid, text);
    }

    // Fallback response
    await sendHelpFallback(sock, jid);
  } catch (err) {
    logger.error('Error handling message:', err);
    try {
      await sendInteractiveMessage(sock, msg.key.remoteJid, {
        title: '⚠️ Terjadi Kesalahan',
        body: 'Terjadi gangguan saat memproses pesan Anda. Ketik *menu* untuk kembali ke Menu Utama.',
        footer: 'SIWIL Layanan Warga',
        buttons: [{ name: 'quick_reply', id: 'menu', displayText: '🏠 Buka Menu Utama' }]
      });
    } catch (_) {}
  }
}

/**
 * Send Main Menu with Clean Formatted Layout
 */
async function sendMainMenu(sock, jid) {
  const phone = normalizePhoneNumber(jid);
  const check = await siwilApi.checkUser(phone);

  const title = '🏛️ SIWIL LAYANAN WARGA';
  let body = '';
  const footer = "💡 Ketik angka pilihan di atas (contoh: 1) atau ketik 'batal' untuk membatalkan proses.";
  const listButtons = [];

  if (check.success && check.data?.linked) {
    const u = check.data.user;
    body = `Halo, *${u.name}*! 👋\n` +
      `📍 RT ${u.rt || '-'} / RW ${u.rw || '-'}, ${u.village || '-'}\n` +
      `📱 Role: *${u.role || 'WARGA'}*\n\n` +
      `Silakan pilih layanan warga yang ingin diakses:`;

    listButtons.push({
      name: 'single_select',
      displayText: '📋 Menu Layanan',
      sections: [
        {
          title: '📄 Pelayanan Administrasi Surat',
          rows: [
            { id: '1', title: 'Pengajuan Surat Baru', description: 'Buat permohonan surat pengantar RT/RW' },
            { id: '2', title: 'Riwayat & Unduh Surat', description: 'Cek status verifikasi & unduh file PDF surat' },
          ]
        },
        {
          title: '📢 Layanan Pengaduan Warga',
          rows: [
            { id: '3', title: 'Sampaikan Pengaduan', description: 'Lapor masalah lingkungan, fasilitas, atau keamanan' },
            { id: '4', title: 'Cek Status Pengaduan', description: 'Pantau progres penanganan aduan Anda' },
          ]
        },
        {
          title: '🗳️ Partisipasi & Kependudukan',
          rows: [
            { id: '5', title: 'Polling & Musyawarah', description: 'Ikuti voting keputusan musyawarah warga' },
            { id: '6', title: 'Profil Akun & Data Kependudukan', description: 'Lihat data kependudukan dan kontak terdaftar' },
          ]
        }
      ]
    });
  } else {
    body = `Halo! Selamat datang di layanan mandiri SIWIL. 👋\n\n` +
      `⚠️ *Nomor WhatsApp Anda belum terdaftar / terhubung dengan akun SIWIL.*\n\n` +
      `Silakan hubungkan akun warga Anda dengan mengetik:\n` +
      `👉 \`login <NIK/Email/NoHP> <password>\`\n\n` +
      `_Contoh:_\n` +
      `• \`login 3201016666666666 rahasia123\`\n` +
      `• \`login warga@email.com rahasia123\``;

    listButtons.push({
      name: 'quick_reply',
      id: 'help_login',
      displayText: 'Bantuan Login / Hubungkan Akun'
    });
  }

  await sendButton(sock, jid, title, body, footer, listButtons);
}

/**
 * Fallback prompt with quick navigation options
 */
async function sendHelpFallback(sock, jid) {
  await sendButton(
    sock,
    jid,
    '❓ PERINTAH TIDAK DIKENALI',
    'Pesan tidak dikenali oleh sistem SIWIL.\nSilakan pilih menu bantuan di bawah ini:',
    'SIWIL Layanan Warga',
    [
      { name: 'quick_reply', displayText: 'Menu Utama', id: 'menu' },
      { name: 'quick_reply', displayText: 'Pengajuan Surat Baru', id: '1' },
      { name: 'quick_reply', displayText: 'Sampaikan Pengaduan', id: '3' }
    ]
  );
}
