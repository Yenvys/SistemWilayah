import { siwilApi } from '../api/siwilClient.js';
import { normalizePhoneNumber } from '../utils/formatter.js';
import { sendButton } from '../utils/buttonHelper.js';
import { authHandler } from './authHandler.js';

export const pollHandler = {
  /**
   * List active polls with direct clickable voting buttons
   */
  async handleListPolls(sock, jid) {
    const user = await authHandler.ensureAuthenticated(sock, jid);
    if (!user) return;

    try {
      await sock.sendPresenceUpdate('composing', jid);
    } catch (_) {}

    const phone = normalizePhoneNumber(jid);
    const result = await siwilApi.getPolls(phone);

    if (!result.success) {
      await sendButton(
        sock,
        jid,
        'Gagal Memuat Polling',
        'Tidak dapat memuat daftar musyawarah warga saat ini.',
        'SIWIL Polling',
        [{ name: 'quick_reply', displayText: '🏠 Menu Utama', id: 'menu_utama' }]
      );
      return;
    }

    const polls = result.data;
    if (!polls || polls.length === 0) {
      await sendButton(
        sock,
        jid,
        'Musyawarah Lingkungan',
        'Saat ini belum ada agenda musyawarah atau polling aktif di wilayah Anda.',
        'SIWIL Polling',
        [{ name: 'quick_reply', displayText: '🏠 Menu Utama', id: 'menu_utama' }]
      );
      return;
    }

    // Process active polls
    for (const p of polls) {
      let bodyText = `*${p.title}*\n`;
      if (p.description) {
        bodyText += `${p.description}\n`;
      }
      if (p.expires_at) {
        bodyText += `• Batas Waktu: ${p.expires_at}\n`;
      }
      bodyText += `\n*Hasil Suara Sementara:*\n`;

      p.options.forEach(opt => {
        const mark = opt.is_selected ? ' ✔️ *(Pilihan Anda)*' : '';
        bodyText += `• ${opt.text}: *${opt.votes_count} suara*${mark}\n`;
      });

      const buttons = [];

      if (!p.has_voted) {
        bodyText += `\n> Silakan tekan tombol di bawah untuk memberikan suara Anda:`;
        p.options.slice(0, 2).forEach(opt => {
          buttons.push({
            name: 'quick_reply',
            id: `vote_${p.id}_${opt.index}`,
            displayText: `🗳️ ${opt.text}`
          });
        });
        buttons.push({ name: 'quick_reply', displayText: '🏠 Menu Utama', id: 'menu_utama' });
      } else {
        bodyText += `\n> Terima kasih, Anda telah berpartisipasi pada musyawarah ini.`;
        buttons.push({ name: 'quick_reply', displayText: '📊 Refresh', id: 'menu_polling' });
        buttons.push({ name: 'quick_reply', displayText: '🏠 Menu Utama', id: 'menu_utama' });
      }

      await sendButton(
        sock,
        jid,
        'Musyawarah Warga',
        bodyText.trim(),
        'Pilih opsi suara di bawah',
        buttons
      );
    }
  },

  /**
   * Cast a vote: direct button click or text command "vote [poll_id] [option_number]"
   */
  async handleVote(sock, jid, text) {
    const user = await authHandler.ensureAuthenticated(sock, jid);
    if (!user) return;

    let pollId = '';
    let optionNumber = 1;

    if (text.startsWith('vote_')) {
      const parts = text.split('_');
      pollId = parts[1];
      optionNumber = parseInt(parts[2], 10);
    } else {
      const parts = text.trim().split(/\s+/);
      if (parts.length >= 3) {
        pollId = parts[1].trim();
        optionNumber = parseInt(parts[2].trim(), 10);
      }
    }

    if (!pollId || isNaN(optionNumber)) {
      await sendButton(
        sock,
        jid,
        'Pilihan Tidak Valid',
        'Silakan pilih tombol opsi yang tersedia pada musyawarah.',
        'SIWIL Polling',
        [{ name: 'quick_reply', displayText: '🗳️ Buka Polling', id: 'menu_polling' }]
      );
      return;
    }

    try {
      await sock.sendPresenceUpdate('composing', jid);
    } catch (_) {}

    const phone = normalizePhoneNumber(jid);
    const result = await siwilApi.votePoll(phone, pollId, optionNumber);

    if (!result.success) {
      await sendButton(
        sock,
        jid,
        'Gagal Memberikan Suara',
        `❌ *Gagal Memberikan Suara*\n\n${result.error}`,
        'SIWIL Polling',
        [
          { name: 'quick_reply', displayText: '🗳️ Buka Polling', id: 'menu_polling' },
          { name: 'quick_reply', displayText: '🏠 Menu Utama', id: 'menu_utama' }
        ]
      );
      return;
    }

    const successMsg = `✅ *Suara Anda Berhasil Dicatat*\n\n` +
      `• *Musyawarah:* *${result.data?.poll_title || 'Musyawarah Warga'}*\n` +
      `• *Pilihan Anda:* *${result.data?.selected_option || optionNumber}*\n\n` +
      `> Terima kasih telah berpartisipasi aktif dalam musyawarah lingkungan bersama SIWIL.`;

    await sendButton(
      sock,
      jid,
      'Suara Masuk',
      successMsg,
      'SIWIL Polling',
      [
        { name: 'quick_reply', displayText: '📊 Cek Hasil Polling', id: 'menu_polling' },
        { name: 'quick_reply', displayText: '🏠 Menu Utama', id: 'menu_utama' }
      ]
    );
  }
};
