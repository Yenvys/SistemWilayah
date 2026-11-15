import { logger } from './logger.js';

/**
 * Format & Send clean, modern text layout for WhatsApp
 * Menggantikan button interaktif yang sering diblokir/tidak muncul di WhatsApp Web & HP terbaru
 * dengan format teks berangka/kata kunci yang rapi, elegan, dan 100% kompatibel.
 */
export const sendButton = async (
  sock,
  jid,
  title,
  body,
  footer,
  buttons = []
) => {
  try {
    // Dukungan overload jika parameter berupa object payload
    if (title && typeof title === 'object' && !body) {
      buttons = title.buttons || [];
      footer = title.footer || '';
      body = title.body || '';
      title = title.title || '';
    }

    let text = '';

    // 1. Header Judul
    const cleanTitle = (title || '').replace(/^\*+|\*+$/g, '').trim();
    if (cleanTitle) {
      text += `*${cleanTitle}*\n`;
      text += `━━━━━━━━━━━━━━━━━━━━━\n\n`;
    }

    // 2. Isi Pesan (Body)
    if (body) {
      text += `${body.trim()}\n\n`;
    }

    // 3. Daftar Opsi / Menu
    if (buttons && buttons.length > 0) {
      const items = [];

      for (const btn of buttons) {
        const bName = btn.name || btn.type || 'quick_reply';
        const bText = btn.displayText || btn.text || btn.title || 'Pilih';

        if (bName === 'single_select' && Array.isArray(btn.sections)) {
          for (const sec of btn.sections) {
            if (sec.title) {
              items.push(`\n📌 *${sec.title.toUpperCase()}*`);
            }
            if (Array.isArray(sec.rows)) {
              for (const r of sec.rows) {
                const desc = r.description ? `\n   _${r.description}_` : '';
                items.push(`• *${r.title}*${desc}\n  👉 Ketik: *${r.id}*`);
              }
            }
          }
        } else if (bName === 'quick_reply') {
          items.push(`• *${bText}*\n  👉 Ketik: *${btn.id}*`);
        } else if (bName === 'cta_url') {
          items.push(`• *${bText}*\n  🌐 ${btn.url}`);
        } else if (bName === 'cta_copy') {
          items.push(`• *${bText}*\n  📋 Salin: \`${btn.id}\``);
        } else {
          items.push(`• *${bText}* (Ketik: *${btn.id || bText}*)`);
        }
      }

      if (items.length > 0) {
        text += `━━━━━━━━━━━━━━━━━━━━━\n`;
        text += items.filter(Boolean).join('\n') + `\n`;
        text += `━━━━━━━━━━━━━━━━━━━━━\n\n`;
      }
    }

    // 4. Footer & Catatan
    if (footer) {
      text += `_${footer.trim()}_\n`;
    }

    const res = await sock.sendMessage(jid, { text: text.trim() });
    logger.success(`Pesan berhasil dikirim ke ${jid}`);
    return res?.key?.id;
  } catch (err) {
    logger.error('[SEND MESSAGE ERROR]', err);
    try {
      return await sock.sendMessage(jid, {
        text: `*${title || 'SIWIL'}*\n\n${body || ''}\n\n_${footer || ''}_`.trim()
      });
    } catch (fallbackErr) {
      logger.error('[FALLBACK SEND ERROR]', fallbackErr);
    }
  }
};

export const sendInteractiveMessage = sendButton;
