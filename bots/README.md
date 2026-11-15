# SIWIL Omnichannel Bots (WhatsApp & Telegram)

Direktori ini memuat dua microservice bot resmi untuk sistem **SIWIL (Sistem Informasi Wilayah Terpadu)**:

---

## 1. Bot WhatsApp (`bots/whatsapp`)
* **Teknologi:** Node.js, `@whiskeysockets/baileys` (Multi-Device WhatsApp Web Socket), Express.js (port 3000).
* **Fungsi Utama:**
  - Registrasi & autentikasi otomatis via webhook `/api/whatsapp/check-user`
  - Permohonan surat mandiri, tracking berkas, dan unduh dokumen resmi ber-TTE format vector PDF
  - Pengaduan keluhan warga berbasis teks & koordinat
  - Penerimaan sinyal darurat SOS instan
  - Pengiriman kode OTP pemulihan kata sandi 6 digit (valid 10 menit)
* **Cara Menjalankan:**
  ```bash
  cd bots/whatsapp
  npm install
  cp .env.example .env
  node index.js
  ```

---

## 2. Bot Telegram (`bots/telegram`)
* **Teknologi:** TypeScript, `tsx`, Telegraf framework.
* **Fungsi Utama:**
  - Perintah interaktif: `/layanan`, `/login`, `/menu`, `/ping`, `/info`
  - Siaran pengumuman resmi desa ke kanal warga
  - Monitoring informasi wilayah
* **Cara Menjalankan:**
  ```bash
  cd bots/telegram
  npm install
  cp .env.example .env
  npm start
  ```
