import { getContentType } from "@whiskeysockets/baileys";

/**
 * Normalize phone number / JID to 628xxx format
 */
export function normalizePhoneNumber(raw) {
  if (!raw) return '';
  let clean = raw.split('@')[0].split(':')[0].replace(/[^0-9]/g, '');

  if (clean.startsWith('0')) {
    clean = '62' + clean.slice(1);
  } else if (clean.startsWith('8')) {
    clean = '62' + clean;
  }
  return clean;
}

/**
 * Format phone number / LID to WhatsApp JID (628xxx@s.whatsapp.net or xxx@lid)
 */
export function formatToJid(number) {
  if (!number) return null;
  if (typeof number === 'string' && number.includes('@')) {
    return number;
  }
  const clean = String(number).split('@')[0].split(':')[0].replace(/[^0-9]/g, '');
  if (!clean) return null;

  const norm = normalizePhoneNumber(clean);
  return norm ? `${norm}@s.whatsapp.net` : null;
}


/**
 * Format document status into friendly badge & emoji
 */
export function formatDocumentStatus(status) {
  switch (status) {
    case 'DIAJUKAN_RT':
      return '📝 Menunggu Verifikasi RT';
    case 'DIAJUKAN_RW':
      return '⏳ Menunggu Verifikasi RW';
    case 'DIAJUKAN_DESA':
      return '⏳ Menunggu Kantor Desa';
    case 'DISETUJUI':
    case 'SELESAI':
      return '✅ Disetujui (Siap Dicetak)';
    case 'DITOLAK':
      return '❌ Ditolak';
    default:
      return `ℹ️ ${status}`;
  }
}

/**
 * Format complaint status into friendly badge & emoji
 */
export function formatComplaintStatus(status) {
  switch (status) {
    case 'PENDING':
      return '⏳ Menunggu Ditindaklanjuti';
    case 'PROCESSING':
      return '🔄 Sedang Diproses Petugas';
    case 'RESOLVED':
      return '✅ Selesai Ditangani';
    case 'REJECTED':
      return '❌ Ditolak';
    default:
      return `ℹ️ ${status}`;
  }
}

/**
 * Extract text or button ID from any incoming WhatsApp message
 * (Menggunakan logika unwrapping dari referensi Cel/src/serialize.js)
 */
export function extractMessageContent(m) {
  if (!m || !m.message) return '';

  let message = m.message;
  let type = getContentType(message);

  // Unwrap ViewOnce or Ephemeral
  if (['viewOnceMessageV2', 'viewOnceMessage', 'ephemeralMessage'].includes(type)) {
    const unwrap = message[type];
    message = unwrap?.message || unwrap;
    type = getContentType(message);
  }

  const msgContent = message?.[type] || {};

  // 1. Interactive Response (Native Flow buttons / Quick Reply / CTA)
  if (type === 'interactiveResponseMessage') {
    try {
      const nativeReply = msgContent?.nativeFlowResponseMessage;
      const paramsRaw = nativeReply?.paramsJson || nativeReply?.responseJson || '';
      const params = paramsRaw ? JSON.parse(paramsRaw) : {};
      if (params?.id) return params.id;
      if (nativeReply?.name) return nativeReply.name;
    } catch (_) {}

    if (msgContent?.body?.text) {
      return msgContent.body.text.trim();
    }
  }

  // 2. Single Select List Response
  if (type === 'listResponseMessage') {
    return msgContent?.singleSelectReply?.selectedRowId || '';
  }

  // 3. Template Button Reply
  if (type === 'templateButtonReplyMessage') {
    return msgContent?.selectedId || '';
  }

  // 4. Buttons Response
  if (type === 'buttonsResponseMessage') {
    return msgContent?.selectedButtonId || '';
  }

  // 5. Standard Text or Captions
  return (
    type === 'conversation'
      ? message.conversation
      : type === 'extendedTextMessage'
        ? msgContent?.text
        : ['imageMessage', 'videoMessage'].includes(type)
          ? msgContent?.caption
          : ''
  )?.trim() || '';
}
