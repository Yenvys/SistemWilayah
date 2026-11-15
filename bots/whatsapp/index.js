import {
  makeWASocket,
  DisconnectReason,
  useMultiFileAuthState,
  makeCacheableSignalKeyStore,
  fetchLatestBaileysVersion,
  Browsers,
  isJidBroadcast
} from '@whiskeysockets/baileys';
import pino from 'pino';
import qrcode from 'qrcode-terminal';
import { config } from './src/config.js';
import { logger } from './src/utils/logger.js';
import { createExpressServer } from './src/server/expressServer.js';
import { handleIncomingMessage } from './src/handlers/messageHandler.js';
import { store, storeFilePath, getMessageFromStoreById } from './src/utils/store.js';

// Meredam log mentah internal libsignal yang membanjiri terminal saat terjadi desync/Bad MAC
const originalConsoleError = console.error;
const originalConsoleInfo = console.info;

console.error = (...args) => {
  const msg = args.map((a) => (typeof a === 'string' ? a : a?.message || '')).join(' ');
  if (msg.includes('Bad MAC') || msg.includes('Failed to decrypt message')) {
    return;
  }
  originalConsoleError(...args);
};

console.info = (...args) => {
  const msg = args.map((a) => (typeof a === 'string' ? a : a?.message || '')).join(' ');
  if (msg.includes('Closing session:')) {
    return;
  }
  originalConsoleInfo(...args);
};

// Tangani uncaught exception agar proses bot tidak terhenti karena desync libsignal
process.on('uncaughtException', (err) => {
  if (err?.message?.includes('Bad MAC') || err?.message?.includes('No matching sessions')) {
    return;
  }
  logger.error('Uncaught Exception:', err);
});

let sock = null;

// Memuat riwayat cache pesan lama dari data/store.json saat startup (persis Cel)
try {
  store.readFromFile(storeFilePath);
  logger.info(`Memuat database cache pesan dari data/store.json (Total: ${store.messages.size} pesan)`);
} catch (e) {
  logger.warn('Gagal membaca store lama, memulai store baru.');
}

// Initialize and start Express HTTP server for Laravel webhook calls
const app = createExpressServer(() => sock);

app.listen(config.port, () => {
  logger.success(`Express API Server berjalan di http://127.0.0.1:${config.port}`);
  logger.info(`Endpoint notifikasi siap menerima request dari Laravel SIWIL:`);
  logger.info(`- POST http://127.0.0.1:${config.port}/send-message`);
  logger.info(`- POST http://127.0.0.1:${config.port}/send-document`);
  logger.info(`- GET  http://127.0.0.1:${config.port}/status`);
});

/**
 * Connect to WhatsApp using Baileys (Konfigurasi & engine identik dengan Cel/src/client.js)
 */
async function connectToWhatsApp() {
  const { state, saveCreds } = await useMultiFileAuthState(config.authFolder);
  const baileysLogger = pino({ level: 'silent' });
  let version;

  try {
    const fetched = await fetchLatestBaileysVersion();
    version = fetched.version;
  } catch (_) {
    version = undefined;
  }

  logger.info('Menghubungkan ke WhatsApp...');

  sock = makeWASocket({
    ...(version ? { version } : {}),
    auth: {
      creds: state.creds,
      keys: makeCacheableSignalKeyStore(state.keys, baileysLogger)
    },
    logger: baileysLogger,
    printQRInTerminal: false,
    generateHighQualityLinkPreview: true,
    browser: Browsers.ubuntu("Chrome"),
    markOnlineOnConnect: true,
    keepAliveIntervalMs: 10000,
    connectTimeoutMs: 60000,
    retryRequestDelayMs: 250,
    maxMsgRetryCount: 15,
    syncFullHistory: false,
    shouldIgnoreJid: (jid) => isJidBroadcast(jid),
    getMessage: async (key) => {
      const msg = await getMessageFromStoreById(key.id);
      return msg || { conversation: "hello" };
    }
  });

  // Ikat event socket ke store agar otomatis mencatat seluruh traffic pesan masuk (notify) & keluar (append)
  store.bind(sock.ev);

  // Save session credentials whenever updated
  sock.ev.on('creds.update', saveCreds);

  // Monitor connection updates
  sock.ev.on('connection.update', async (update) => {
    const { connection, lastDisconnect, qr } = update;

    if (qr) {
      console.log('\n==================================================');
      console.log('  SCAN QR CODE DI BAWAH INI DENGAN WHATSAPP ANDA:');
      console.log('==================================================\n');
      qrcode.generate(qr, { small: true });
      console.log('\nPetunjuk: Buka WhatsApp > Perangkat Tertaut > Tautkan Perangkat\n');
    }

    if (connection === 'close') {
      const statusCode = lastDisconnect?.error?.output?.statusCode;
      const shouldReconnect = statusCode !== DisconnectReason.loggedOut;

      logger.warn(`Koneksi WhatsApp terputus. Status Code: ${statusCode}. Reconnecting: ${shouldReconnect}`);

      if (shouldReconnect) {
        setTimeout(connectToWhatsApp, 3000);
      } else {
        logger.error('WhatsApp keluar (Logged out). Silakan hapus folder auth_info_baileys dan scan ulang QR code.');
      }
    } else if (connection === 'open') {
      logger.success(`🎉 WhatsApp Bot SIWIL Berhasil Terhubung!`);
      const botNumber = sock.user?.id?.split(':')[0] || sock.user?.id;
      logger.info(`Nomor Bot: +${botNumber} (${sock.user?.name || config.botName})`);
      logger.info(`SIWIL API Target: ${config.siwilApiUrl}`);
    }
  });

  // Handle incoming messages
  sock.ev.on('messages.upsert', async ({ messages, type }) => {
    if (type !== 'notify') return;

    for (const msg of messages) {
      await handleIncomingMessage(sock, msg);
    }
  });
}

// Auto-save store ke disk setiap 5 menit
setInterval(async () => {
  try {
    await store.writeToFile(storeFilePath);
  } catch (_) {}
}, 300000);

// Auto-save store saat process terminated
process.on('SIGINT', () => {
  try { store.writeToFileSync(storeFilePath); } catch (_) {}
  process.exit(0);
});

process.on('SIGTERM', () => {
  try { store.writeToFileSync(storeFilePath); } catch (_) {}
  process.exit(0);
});

// Start connection
connectToWhatsApp().catch((err) => {
  logger.error('Fatal error starting WhatsApp bot:', err);
});
