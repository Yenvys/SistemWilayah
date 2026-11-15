import express from 'express';
import fs from 'fs';
import path from 'path';
import { formatToJid } from '../utils/formatter.js';
import { sendInteractiveMessage } from '../utils/buttonHelper.js';
import { cacheMessage } from '../utils/store.js';
import { logger } from '../utils/logger.js';

const refStorePath = path.resolve('data/referenceStore.json');
let referenceMap = {};
try {
  if (fs.existsSync(refStorePath)) {
    referenceMap = JSON.parse(fs.readFileSync(refStorePath, 'utf8'));
  }
} catch (_) {}

function saveReferenceMap() {
  try {
    fs.writeFileSync(refStorePath, JSON.stringify(referenceMap, null, 2), 'utf8');
  } catch (_) {}
}

export function createExpressServer(getSock) {
  const app = express();

  // Allow up to 50MB for PDF base64 payloads
  app.use(express.json({ limit: '50mb' }));
  app.use(express.urlencoded({ extended: true, limit: '50mb' }));

  // Handle malformed JSON gracefully
  app.use((err, req, res, next) => {
    if (err instanceof SyntaxError && err.status === 400 && 'body' in err) {
      logger.warn(`Malformed JSON received: ${err.message}`);
      return res.status(400).json({ error: 'Format JSON request tidak valid.' });
    }
    next(err);
  });

  /**
   * Health / Status Endpoint
   */
  app.get('/status', (req, res) => {
    const sock = getSock();
    const isConnected = Boolean(sock && sock.user);

    res.json({
      status: 'ok',
      connected: isConnected,
      user: sock?.user || null,
      uptime: process.uptime()
    });
  });

  app.get('/health', (req, res) => {
    res.json({ status: 'UP', timestamp: new Date().toISOString() });
  });

  /**
   * Send text message with optional in-place edit and interactive buttons
   */
  app.post('/send-message', async (req, res) => {
    try {
      const { number, message, title, footer, buttons, referenceId, edit } = req.body;
      if (!number || !message) {
        return res.status(400).json({ error: 'Parameter "number" dan "message" wajib diisi.' });
      }

      const sock = getSock();
      if (!sock || !sock.user) {
        logger.warn('Gagal kirim pesan: WhatsApp Bot belum terhubung / belum scan QR.');
        return res.status(503).json({ error: 'WhatsApp Bot belum terhubung / belum scan QR.' });
      }

      const jid = formatToJid(number);
      if (!jid) {
        return res.status(400).json({ error: 'Format nomor telepon tidak valid.' });
      }

      let edited = false;
      let sentId = null;

      // 1. Coba edit pesan sebelumnya jika diminta dan referenceId ditemukan
      if (edit && referenceId && referenceMap[referenceId]) {
        const prev = referenceMap[referenceId];
        try {
          const editPayload = {
            text: message,
            edit: {
              remoteJid: jid,
              id: prev.messageId,
              fromMe: true
            }
          };

          const editResult = await sock.sendMessage(jid, editPayload);
          if (editResult?.key?.id) {
            sentId = prev.messageId;
            edited = true;
            logger.info(`Pesan notifikasi [${referenceId}] berhasil diedit in-place di WhatsApp.`);
          }
        } catch (editErr) {
          logger.warn(`Gagal edit pesan [${referenceId}] (${editErr.message}), beralih mengirim pesan baru.`);
        }
      }

      // 2. Jika tidak diedit (atau edit gagal), kirim sebagai pesan baru
      if (!edited) {
        if (buttons && Array.isArray(buttons) && buttons.length > 0) {
          sentId = await sendInteractiveMessage(sock, jid, {
            title: title || 'SIWIL Layanan Warga',
            body: message,
            footer: footer || '🏛️ SIWIL - Sistem Informasi Wilayah',
            buttons
          });
        } else {
          const sent = await sock.sendMessage(jid, { text: message });
          sentId = sent?.key?.id;
          if (sentId && sent.message) {
            cacheMessage(sentId, sent.message);
          }
        }

        if (referenceId && sentId) {
          referenceMap[referenceId] = {
            messageId: sentId,
            jid,
            timestamp: Date.now()
          };
          saveReferenceMap();
        }
      }

      logger.success(`Notifikasi ${edited ? 'berhasil diedit' : 'terkirim'} ke ${number}`);
      return res.json({ success: true, messageId: sentId, edited });
    } catch (err) {
      logger.error('Error in /send-message:', err.message);
      return res.status(500).json({ error: err.message });
    }
  });

  /**
   * Send document (PDF) directly via WhatsApp (called by Laravel WhatsAppService)
   */
  app.post('/send-document', async (req, res) => {
    try {
      const { number, base64, filename, caption, mimetype } = req.body;
      if (!number || !base64) {
        return res.status(400).json({ error: 'Parameter "number" dan "base64" wajib diisi.' });
      }

      const sock = getSock();
      if (!sock || !sock.user) {
        logger.warn('Gagal kirim dokumen: WhatsApp Bot belum terhubung / belum scan QR.');
        return res.status(503).json({ error: 'WhatsApp Bot belum terhubung / belum scan QR.' });
      }

      const jid = formatToJid(number);
      if (!jid) {
        return res.status(400).json({ error: 'Format nomor telepon tidak valid.' });
      }

      const fileBuffer = Buffer.from(base64, 'base64');
      const docName = filename || 'Dokumen_SIWIL.pdf';
      const docMime = mimetype || 'application/pdf';

      const sent = await sock.sendMessage(jid, {
        document: fileBuffer,
        fileName: docName,
        mimetype: docMime,
        caption: caption || undefined
      });

      if (sent?.key?.id && sent.message) {
        cacheMessage(sent.key.id, sent.message);
      }

      logger.success(`Dokumen PDF ${docName} berhasil dikirim ke ${number}`);
      return res.json({ success: true, message: 'Dokumen berhasil dikirim.' });
    } catch (err) {
      logger.error('Error in /send-document:', err.message);
      return res.status(500).json({ error: err.message });
    }
  });

  return app;
}
