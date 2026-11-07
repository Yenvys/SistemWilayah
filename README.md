# SIWIL (Sistem Informasi Wilayah & Pemerintahan Terpadu)

<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="300" alt="Laravel Logo">
</p>

<p align="center">
  <strong>Platform Digitalisasi Administrasi Wilayah, Pelayanan Publik Mandiri, Transparansi Desa, dan Layanan Cerdas Berbasis AI & Omnichannel Bot</strong>
</p>

<p align="center">
  <a href="#-informasi-tim--pengembang">Informasi Tim</a> •
  <a href="#-tautan-video-progres--demo">Video Demo</a> •
  <a href="#-deskripsi-aplikasi">Deskripsi Aplikasi</a> •
  <a href="#-fitur-cerdas-ai-siwil-ai">Fitur AI</a> •
  <a href="#-fitur-utama-sistem">Fitur Utama</a> •
  <a href="#-petunjuk-instalasi--setup-instruction">Panduan Instalasi</a> •
  <a href="#-akun-pengguna-bawaan-seeder">Akun Default</a>
</p>

---

## 📌 Informasi Tim & Pengembang

| Atribut | Keterangan |
| :--- | :--- |
| **Nama Pengembang** | **Alfarel** *(dan Anggota Kelompok)* |
| **NIM** | *[Masukkan NIM Ketua / Anggota di sini]* |
| **Kelompok** | **Kelompok SIWIL** |
| **Tema / Topik Kelompok** | **Sistem Informasi Wilayah & Pemerintahan Cerdas (*Smart Governance* / *E-Government* Desa, RW, dan RT Terpadu Berbasis AI & Omnichannel Bot)** |
| **Mata Kuliah / Institusi** | *[Nama Mata Kuliah / Program Studi / Universitas]* |
| **Tahun Akademik** | 2026 |

> [!NOTE]
> Proyek ini dikembangkan untuk mendigitalkan birokrasi pemerintahan desa dan rukun warga/tetangga secara *end-to-end* dengan penegakan tata kelola privasi data (UU PDP), otomasi persuratan, dan asisten cerdas AI.

---

## 🎥 Tautan Video Progres & Demo

Berikut adalah tautan rekaman video dokumentasi perkembangan dan demonstrasi aplikasi:

| Tahapan Video | Tautan Video | Deskripsi Singkat Konten |
| :--- | :--- | :--- |
| **Video Progres 1** | [![Tonton Video Progres 1](https://img.shields.io/badge/YouTube-Video_Progres_1-red?style=for-the-badge&logo=youtube)](https://youtu.be/PROGRES_1_LINK_HERE) | Presentasi konsep dasar, perancangan basis data kependudukan, use case diagram, dan prototipe antarmuka awal. |
| **Video Progres 2** | [![Tonton Video Progres 2](https://img.shields.io/badge/YouTube-Video_Progres_2-red?style=for-the-badge&logo=youtube)](https://youtu.be/PROGRES_2_LINK_HERE) | Demonstrasi alur kerja pengajuan surat 3-tier (RT $\rightarrow$ RW $\rightarrow$ Desa), pengaduan foto & GPS, serta integrasi floating chatbot AI awal. |
| **Video Final Demo** | [![Tonton Video Final Demo](https://img.shields.io/badge/YouTube-Video_Final_Demo-green?style=for-the-badge&logo=youtube)](https://youtu.be/FINAL_DEMO_LINK_HERE) | Demonstrasi menyeluruh (*end-to-end*): Portal publik, reset password via WhatsApp OTP, 12 Function Calling AI Tools, Bot WhatsApp, Bot Telegram, dan TTE digital QR Code. |

*(Silakan perbarui URL tautan di atas sesuai dengan link YouTube / Google Drive presentasi kelompok Anda).*

---

## 📖 Deskripsi Aplikasi

**SIWIL** adalah aplikasi web modern berbasis cloud yang dirancang untuk mendigitalisasi dan mempermudah tata kelola administrasi tingkat wilayah desa, Rukun Warga (RW), dan Rukun Tetangga (RT). 

Aplikasi ini menjadi jembatan informasi, administrasi, transparansi fiskal, dan pelaporan dua arah antara warga dengan aparatur kewilayahan (Ketua RT, Ketua RW, dan Perangkat Desa/Kelurahan).

### Masalah yang Diselesaikan:
1. **Memangkas Birokrasi Fisik:** Warga tidak perlu lagi datang secara fisik berkali-kali untuk meminta surat pengantar manual dari RT, lalu ke RW, lalu mengantre di kantor desa.
2. **Pelayanan Fleksibel 24/7:** Layanan surat dan aduan dapat diakses mandiri melalui web, bot WhatsApp, maupun bot Telegram kapan saja.
3. **Penyimpanan Terpadu & Aman:** Mencegah tercecernya buku register manual dan mengamankan data pribadi warga sesuai mandat **Undang-Undang Perlindungan Data Pribadi (UU PDP)**.
4. **Transparansi Tata Kelola:** Membuka data APBDes dan progres proyek pembangunan desa kepada publik melalui portal keterbukaan informasi.

---

## 🤖 Fitur Cerdas AI (SIWIL AI)

Project SIWIL dilengkapi dengan modul kecerdasan buatan (*Artificial Intelligence*) canggih yang terintegrasi langsung ke sistem operasional (*backend*):

1. **Floating Chatbot Widget Interaktif:**
   - Tersedia di seluruh halaman dashboard dengan desain modern (*glassmorphism*, dark/light theme adaptif).
   - Memahami bahasa natural Indonesia untuk menjawab pertanyaan kependudukan, SOP layanan, dan status berkas.
2. **Multi-Model & Multi-Provider LLM Orchestration:**
   - Mendukung **Google Gemini (Gemini 2.5 & 3.5 Flash)**, OpenAI (GPT-4o), Groq Llama, serta model lokal (*Ollama*) via *custom base URL*.
   - Dilengkapi *API Key Rotation*: jika kuota kunci utama habis (*rate limit*), sistem otomatis berganti ke kunci cadangan.
   - Dilengkapi *Smart Fallback Pattern Matching* jika koneksi API luar tidak tersedia.
3. **12 Dynamic AI Function Calling Tools:**
   AI dibekali kemampuan membaca data riil sistem secara aman melalui *function calling*:
   - `fetch_dashboard_data`: Menampilkan ringkasan metrik statistik wilayah.
   - `fetch_residents_and_areas`: Mengambil data kependudukan (khusus peran pengurus).
   - `fetch_announcements`: Mengambil edaran dan pengumuman resmi desa.
   - `fetch_document_requests`: Memeriksa riwayat dan status permohonan surat.
   - `fetch_complaints`: Mengambil daftar pengaduan warga sesuai wilayah tugas.
   - `fetch_polls`: Menampilkan musyawarah aktif dan status hak pilih pengguna.
   - `fetch_family_data`: Membaca rincian anggota Kartu Keluarga (KK) milik pengguna.
   - `fetch_development_and_budgets`: Memeriksa proyek fisik dan alokasi APBDes.
   - `fetch_audit_logs`: Mengaudit aktivitas sistem (khusus pengurus).
   - `fetch_users_directory`: Direktori akun pengguna (khusus Super Admin / Desa).
   - `fetch_ai_knowledge`: Mengambil basis data FAQ, profil desa, dan SOP layanan.
   - `fetch_current_user_profile`: Mengambil biodata akun yang sedang terotentikasi.
4. **Kepatuhan Privasi & Guardrail UU PDP:**
   - Sistem secara dinamis menginjeksi peran (*role*) dan wilayah pengguna ke *system prompt*.
   - **UU PDP Enforcement:** AI secara tegas menolak membagikan NIK, nomor telepon, alamat, atau data privat warga lain kepada akun warga biasa.
5. **Pusat Basis Pengetahuan (`AiKnowledge`):**
   - Admin dapat menambah dan memperbarui basis pengetahuan (FAQ, profil desa, peraturan wilayah) yang dijadikan sumber rujukan utama jawaban AI.

---

## 🌟 Fitur Utama Sistem

### 1. Portal Transparansi Publik (Tanpa Login)
- **Beranda Desa:** Profil wilayah, kepala desa, statistik kependudukan ringkas, dan daftar pengumuman.
- **Transparansi APBDes:** Rincian grafik pos Pendapatan, Belanja, dan Pembiayaan beserta perbandingan pagu vs realisasi serapan dana per tahun.
- **Statistik Demografi:** Visualisasi rasio gender, jenjang pendidikan, 10 profesi terbanyak, dan piramida generasi umur.
- **Peta & Progres Pembangunan:** Galeri dokumentasi proyek fisik, persentase kemajuan, dan sumber dana (DD/ADD/PADes).

### 2. Autentikasi & Akun Pengguna
- **Registrasi Warga Berbasis Validasi Silang:** Mencegah akun fiktif; NIK dan Nomor KK pendaftar wajib sudah tercatat di basis data kependudukan desa.
- **Verifikasi Bertingkat:** Akun baru berstatus `pending` dan harus disetujui oleh Ketua RT atau Admin Desa sebelum dapat login.
- **Multi-Identifier Login:** Fleksibel login menggunakan NIK, Email, atau Nomor Handphone/WhatsApp.
- **Reset Password via WhatsApp OTP:** Pengiriman kode OTP 6 digit otomatis ke nomor WhatsApp pemohon dengan proteksi *throttle* dan masa berlaku 5 menit.
- **Tanda Tangan & Stempel Digital:** Pejabat RT, RW, dan Desa dapat mengunggah tanda tangan dan stempel resmi transparan untuk dicetak otomatis pada dokumen PDF.

### 3. Layanan Administrasi & Persuratan Digital
- Katalog surat lengkap: Surat Domisili, SKTM, SKU, Pengantar Nikah (KUA), Izin Keramaian, Surat Kehilangan, Izin Renovasi Rumah, dsb.
- **Multi-Tier Approval Workflow:** Alur persetujuan berjenjang dari RT $\rightarrow$ RW $\rightarrow$ Desa dengan opsi *fast-track* kewenangan desa.
- **Audit Trail Surat:** Riwayat status transparan (`DIAJUKAN_RT`, `DIAJUKAN_RW`, `DIAJUKAN_DESA`, `DISETUJUI`, `DITOLAK`).
- **Cetak Surat PDF & QR Code TTE:** Dokumen berformat standar resmi dengan QR Code validator yang membuktikan keaslian dokumen saat dipindai.

### 4. Partisipasi & Pengaduan Warga
- **Lapor Pengaduan:** Formulir laporan dengan unggahan foto bukti dan koordinat GPS presisi (*geotagging*).
- **Siklus Status Aduan:** Pelacakan penanganan (`PENDING` $\rightarrow$ `PROCESSING` $\rightarrow$ `RESOLVED` / `REJECTED`).
- **Tombol Darurat (SOS Button):** Pemicu cepat sinyal darurat (Medis, Kebakaran, Kriminal, Bencana Alam) disertai koordinat GPS seketika ke dashboard seluruh pengurus wilayah.

### 5. Musyawarah & Polling Digital (E-Democracy)
- Pembuatan jajak pendapat berjangka waktu (*expires at*) dengan cakupan level wilayah (Tingkat Desa, RW, atau RT).
- *Targeted Notification:* Notifikasi pemungutan suara hanya dikirimkan ke warga yang relevan dengan teritorial polling.
- *One Citizen One Vote:* Menjamin 1 akun warga hanya memiliki 1 hak suara.
- Penentuan pemenang otomatis (*winning option*) saat polling ditutup.

### 6. Pembangunan Desa, APBDes & Analisis Spasial (GIS Hub)
- Pengelolaan pagu anggaran dan realisasi APBDes tahunan.
- Monitoring proyek pembangunan fisik desa.
- **Peta Spasial Terpadu (GIS Interactive Map):** Peta interaktif Leaflet yang menampilkan titik proyek pembangunan, titik keluhan warga 30 hari terakhir, dan titik insiden darurat aktif.

### 7. Integrasi Omnichannel Bot (WhatsApp & Telegram)
- **WhatsApp Bot Microservice (`/api/whatsapp/*`):** Cek pengguna, login mandiri, pembuatan surat, cek status berkas, unduh PDF otomatis, lapor pengaduan foto, dan voting polling via chat WhatsApp.
- **Telegram Bot (`/api/telegram/*`):** Tautkan akun via *Deep Linking token*, pengajuan surat, tracking berkas, dan unduh PDF dokumen.
- **Tri-Channel Notification Hub:** Notifikasi otomatis serentak dikirimkan ke In-App, WhatsApp, Telegram, dan Email saat ada perubahan status surat atau aduan.

### 8. Pusat Konfigurasi Terpusat (System Configuration Hub)
- Panel kontrol Super Admin untuk mengatur kredensial AI, WhatsApp Gateway, Telegram Bot, dan SMTP Mailer langsung di antarmuka web.
- Penyamaran otomatis nilai kunci sensitif (*secret masking*).
- Tombol diagnostik pengujian koneksi langsung (*Test AI, Test WhatsApp, Test Telegram, Test SMTP*).

---

## 🛠️ Tech Stack & Dependensi

| Kategori | Teknologi / Pustaka |
| :--- | :--- |
| **Backend Framework** | [Laravel 11+ / 13.x](https://laravel.com) (PHP ^8.3) |
| **Frontend Styling** | [Tailwind CSS v4](https://tailwindcss.com) & Vite 8 |
| **Role & Permissions** | [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission) v8.3 |
| **PDF & Barcode Generator** | [Barryvdh Laravel DomPDF](https://github.com/barryvdh/laravel-dompdf) v3.1 |
| **Peta Spasial (GIS)** | [Leaflet JS](https://leafletjs.com/) & OpenStreetMap |
| **Kecerdasan Buatan (AI)** | Google Gemini API (v1beta) / OpenAI / Groq / Ollama |
| **Database** | MySQL / SQLite (UUID v4 Primary Keys) |
| **Testing Framework** | PHPUnit 12 (105 Feature & Unit Tests, 538 Assertions) |

---

## 🚀 Petunjuk Instalasi & Setup (Setup Instructions)

Ikuti langkah-langkah di bawah ini untuk menjalankan project SIWIL di komputer lokal (*development environment*):

### 1. Prasyarat Sistem
Pastikan perangkat Anda telah terpasang:
- **PHP** versi 8.3 atau 8.4+ (dengan ekstensi `pdo_sqlite` / `pdo_mysql`, `curl`, `mbstring`, `openssl`, `gd`).
- **Composer** (PHP Package Manager).
- **Node.js** (v18.x atau v20.x+) dan **NPM**.
- **Git**.

---

### 2. Langkah-Langkah Instalasi

#### Langkah 1: Kloning Repositori
Buka terminal dan unduh kode sumber proyek:
```bash
git clone <URL_REPOSITORY_ANDA>
cd siwil
```

#### Langkah 2: Pasang Dependensi Backend (Composer)
```bash
composer install
```

#### Langkah 3: Pasang Dependensi Frontend (Node.js)
```bash
npm install
```

#### Langkah 4: Konfigurasi File Lingkungan (.env)
Salin berkas contoh `.env.example` menjadi `.env`:
```bash
# Untuk Linux / macOS / Git Bash:
cp .env.example .env

# Untuk Windows PowerShell:
Copy-Item .env.example .env
```

Generate application key Laravel:
```bash
php artisan key:generate
```

Buka file `.env` dan sesuaikan pengaturan database serta integrasi:
```env
APP_NAME=SIWIL
APP_URL=http://localhost:8000

# Pilihan Database (SQLite sebagai default cepat):
DB_CONNECTION=sqlite

# Atau jika menggunakan MySQL:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=siwil_db
# DB_USERNAME=root
# DB_PASSWORD=

# Kunci API AI (Google Gemini):
GEMINI_API_KEY=KUNCI_API_GEMINI_ANDA
GEMINI_MODEL=gemini-2.5-flash
AI_PROVIDER=gemini

# Integrasi WhatsApp Bot Microservice (Opsional):
WHATSAPP_BOT_URL=http://127.0.0.1:3000
WHATSAPP_BOT_NUMBER=628xxxxxxxxxx

# Integrasi Telegram Bot (Opsional):
TELEGRAM_BOT_TOKEN=TOKEN_BOT_TELEGRAM_ANDA
TELEGRAM_BOT_USERNAME=NAMA_BOT_TELEGRAM_ANDA
```

Jika menggunakan SQLite, pastikan file database tersedia:
```bash
# Windows PowerShell:
if (!(Test-Path database\database.sqlite)) { New-Item -ItemType File -Path database\database.sqlite }

# Linux / macOS:
touch database/database.sqlite
```

#### Langkah 5: Jalankan Migrasi & Database Seeder
Jalankan migrasi tabel dan pengisian data master/pengguna uji coba bawaan:
```bash
php artisan migrate:fresh --seed
```

#### Langkah 6: Kompilasi Aset Frontend
```bash
npm run build
```

#### Langkah 7: Jalankan Aplikasi
Jalankan server aplikasi. Disarankan membuka 2 tab terminal terpisah:

**Terminal 1 (Backend Server):**
```bash
php artisan serve
```
*Aplikasi akan aktif di:* `http://127.0.0.1:8000`

**Terminal 2 (Frontend Hot-Reload / Vite Dev):**
```bash
npm run dev
```

---

## 👥 Akun Pengguna Bawaan (Seeder)

Setelah menjalankan `php artisan db:seed`, sistem telah menyediakan akun pengujian(dummy) untuk setiap level peran:

| Peran (Role) | Nama Pengguna / Pejabat | Username / Identitas | Password Default | Wilayah Yurisdiksi |
| :--- | :--- | :--- | :--- | :--- |
| **Super Admin** | Administrator Utama | `superadmin` *(atau email)* | `password123` | Sistem Global (Multi-Desa) |
| **Admin Desa** | Hj. Sri Mulyani (Kades) | `3201016666666666` | `password123` | Kantor Desa Sukamaju |
| **Ketua RW** | Surya Darma | `3201017777777777` | `password123` | RW 01, Desa Sukamaju |
| **Ketua RT 01** | Hendra Wijaya | `3201018888888888` | `password123` | RT 01 / RW 01 |
| **Ketua RT 02** | Dodi Prasetyo | `3201019999999999` | `password123` | RT 02 / RW 01 |
| **Warga (RT 01)** | Budi Santoso | `3201011111111111` | `password123` | RT 01 (KK: 3201010000000001) |
| **Warga (RT 01)** | Siti Aminah | `3201011111111112` | `password123` | RT 01 (KK: 3201010000000003) |
| **Warga (RT 02)** | Agus Rahman | `3201013333333333` | `password123` | RT 02 (KK: 3201010000000002) |
| **Warga (RT 02)** | Dewi Lestari | `3201013333333334` | `password123` | RT 02 (KK: 3201010000000006) |

> [!TIP]
> Anda dapat login menggunakan **NIK (16 digit)**, **Alamat Email**, atau **Nomor Telepon terdaftar**.

---

## 🧪 Pengujian Otomatis (Automated Testing)

Project SIWIL dilengkapi dengan 105 skenario pengujian unit dan fitur terotomatisasi (*PHPUnit*):
```bash
php artisan test --compact
```
Atau menjalankan *test suite* tertentu:
```bash
php artisan test --filter=AiChatTest
php artisan test --filter=AuthWorkflowTest
php artisan test --filter=PollWorkflowTest
```

---

## 📄 Dokumen Spesifikasi Produk (PRD)

Untuk mempelajari rincian arsitektur teknis mendalam, kamus data (ERD), spesifikasi endpoint API, dan alur kerja approval surat berjenjang secara lengkap, silakan merujuk ke dokumen:
👉 **[PRD.md](file:///c:/Users/alfar/Documents/laragon/www/siwil/PRD.md)**

---

<p align="center">
  Dibuat dengan dedikasi untuk memajukan tata kelola pelayanan masyarakat dan transparansi wilayah Indonesia.
</p>
