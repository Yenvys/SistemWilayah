import fs from 'node:fs';
import path from 'node:path';

/**
 * Message Store & Persistence Engine (berdasarkan referensi Cel/src/helper.js)
 * Menyimpan seluruh pesan masuk (notify) & keluar (append),
 * sehingga saat WhatsApp meminta retry receipt, bot langsung merespons
 * dan mencegah "Waiting for this message".
 */

const dataDir = path.join(process.cwd(), 'data');
if (!fs.existsSync(dataDir)) {
  fs.mkdirSync(dataDir, { recursive: true });
}

export const storeFilePath = path.join(dataDir, 'store.json');

export const store = {
  messages: new Map(),
  isDirty: false,
  limit: 2500,

  bind(ev) {
    ev.on('messages.upsert', ({ messages, type }) => {
      if (type !== 'notify' && type !== 'append') return;
      let added = false;
      for (const msg of messages) {
        if (!msg.key?.id || !msg.message) continue;
        this.messages.set(msg.key.id, msg.message);
        added = true;
      }
      if (added) {
        this.isDirty = true;
        this._cleanOldMessages();
      }
    });

    ev.on('messages.update', (updates) => {
      let updated = false;
      for (const update of updates) {
        if (!update.key?.id || !update.message) continue;
        if (this.messages.has(update.key.id)) {
          this.messages.set(update.key.id, {
            ...this.messages.get(update.key.id),
            ...update.message,
          });
          updated = true;
        }
      }
      if (updated) {
        this.isDirty = true;
      }
    });
  },

  _cleanOldMessages() {
    if (this.messages.size > this.limit) {
      const keysToDelete = Array.from(this.messages.keys()).slice(
        0,
        this.messages.size - this.limit
      );
      for (const key of keysToDelete) {
        this.messages.delete(key);
      }
    }
  },

  async writeToFile(filePath = storeFilePath) {
    if (!this.isDirty) return;
    this.isDirty = false;
    try {
      const obj = Object.fromEntries(this.messages);
      await fs.promises.writeFile(filePath, JSON.stringify(obj));
    } catch (e) {
      this.isDirty = true;
      throw e;
    }
  },

  writeToFileSync(filePath = storeFilePath) {
    if (!this.isDirty) return;
    const obj = Object.fromEntries(this.messages);
    fs.writeFileSync(filePath, JSON.stringify(obj));
    this.isDirty = false;
  },

  readFromFile(filePath = storeFilePath) {
    try {
      if (!fs.existsSync(filePath)) return;
      const raw = JSON.parse(fs.readFileSync(filePath, 'utf-8'));
      for (const [id, msg] of Object.entries(raw)) {
        this.messages.set(id, msg);
      }
      this._cleanOldMessages();
    } catch (e) {
      // Ignore read errors on startup
    }
  },
};

export const getMessageFromStoreById = (id) => {
  if (!id) return null;
  return store.messages.get(id) || null;
};

export const cacheMessage = (id, message) => {
  if (!id || !message) return;
  store.messages.set(id, message);
  store.isDirty = true;
  store._cleanOldMessages();
};
