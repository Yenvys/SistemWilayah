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
| **Nama Pengembang** | **Muhammad Al Farel** |
| **NIM** | **41257124** |
| **Kelas** | **TI 1B 2025** |
| **Kelompok** | **Mandiri / Perorangan (100% Individual Ownership & Full-App Build)** |
| **Tema / Topik Kelompok** | **Smart Governance & E-Government Wilayah Desa, RW, dan RT Terpadu Berbasis AI & Omnichannel Bot** |
| **Mata Kuliah / Kode** | **Rekayasa Perangkat Lunak (TIKB2503021) — 4 SKS (2 Teori + 2 Praktik)** |
| **Dosen Pengampu** | **Yudhistira Arie Wijaya, M.Kom., CDS.** |
| **Program Studi / Institusi**| **S1 Teknik Informatika — STMIK IKMI Cirebon** |
| **Tahun Akademik** | **2025 / 2026 (Semester 3)** |
| **Repositori GitHub** | [`https://github.com/Yenvys/SistemWilayah.git`](https://github.com/Yenvys/SistemWilayah.git) |

> [!NOTE]
> Proyek SIWIL dikembangkan sebagai pemenuhan tugas proyek akhir mata kuliah **Rekayasa Perangkat Lunak (RPL)** berbasis kurikulum **Outcome-Based Education (OBE)** yang mencakup 4 tahapan utuh Software Development Life Cycle (SDLC):
> 1. Inisiasi PRD/SRS Mandiri (Standar IEEE 830 / ISO 29148)
> 2. Perancangan SDD, 4 Diagram UML & Prototipe UI/UX (Standar IEEE 1016)
> 3. Konstruksi Kode (Clean Code & SOLID), Integrasi AI Real-Time, Automated Testing & CI/CD Pipeline
> 4. Pelaporan Final Terintegrasi & Video Presentasi Live Demo Utuh

---

## 🎥 Tautan Video Presentasi & Live Demo

Tautan rekaman video presentasi dan demonstrasi sistem aplikasi SIWIL (menampilkan rekaman layar penuh dan webcam wajah mahasiswa secara langsung):

[![Tonton Video Presentasi & Live Demo SIWIL](https://img.shields.io/badge/YouTube-Tonton_Video_Presentasi_%26_Live_Demo-red?style=for-the-badge&logo=youtube)](https://youtu.be/0H_HhHe1bR4?si=PfxTpZPErlz5iVDC)

> 🔗 **Tautan Langsung YouTube:**  
> 👉 **[https://youtu.be/0H_HhHe1bR4?si=PfxTpZPErlz5iVDC](https://youtu.be/0H_HhHe1bR4?si=PfxTpZPErlz5iVDC)**

### Rincian Konten Video Presentasi & Live Demo:
- **Elevator Pitch & Masalah Nyata:** Latar belakang rantai birokrasi berjenjang RT/RW/Desa, perlindungan data pribadi (UU PDP No. 27/2022), dan transparansi anggaran APBDes.
- **Arsitektur Sistem & Pemodelan UML:** Penjelasan komprehensif 4 Diagram UML Utama (Use Case Diagram RBAC, Activity Diagram alur persuratan 3-tier, Class Diagram berorientasi objek mematuhi 5 prinsip SOLID, Sequence Diagram pertukaran pesan inter-komponen) dan ERD 20 entitas relasional.
- **Live Demo Sistem Utuh (Real-Time):**
  - Demonstrasi portal transparansi publik (Beranda Desa, APBDes interaktif, Proyek Pembangunan).
  - Alur persuratan digital 3-tier: Pengajuan oleh Warga $\rightarrow$ Verifikasi & TTE Ketua RT $\rightarrow$ Verifikasi Ketua RW $\rightarrow$ Legalisasi Kepala Desa ber-QR Code TTE $\rightarrow$ Cetak PDF DomPDF resmi.
  - Pengaduan warga berbasis GPS geotagging & bukti foto serta tombol darurat (SOS button).
  - Musyawarah demokrasi digital (*polling* terarah tingkat RT/RW/Desa).
- **Pengujian Live Fitur AI (SIWIL AI):**
  - Interaksi natural language query via Floating Chatbot Widget.
  - Eksekusi 12 backend function calling tools secara deterministik membaca data riil wilayah.
  - Verifikasi ketat *Guardrail UU PDP* (AI menolak membocorkan NIK, nomor HP, dan data privat warga lain kepada akun biasa).
- **Kanal Omnichannel:** Demonstrasi layanan mandiri persuratan dan notifikasi real-time via WhatsApp Bot dan Telegram Bot.
- **Verifikasi CI/CD & Automated Testing:** Pembuktian status hijau (*passing*) 105 automated unit & feature tests (540 assertions) dan pipeline otomatisasi GitHub Actions (`.github/workflows/ci.yml`).

---

## 📑 Berkas & Dokumen Luaran Proyek (Tahap 1 - 4)

Semua berkas luaran resmi proyek rekayasa perangkat lunak ini telah disusun secara lengkap dan terstandarisasi:

| Tahapan Proyek | Berkas Luaran Utama (Markdown / PDF / Word) | Berkas Diagram & Asset | Status Luaran |
| :--- | :--- | :--- | :---: |
| **Tahap 1: Inisiasi & PRD / SRS** | 📄 [`PRD.md`](PRD.md)<br>📄 [`PRD.pdf`](PRD.pdf) *(Standar IEEE 830 / ISO 29148)* | User Stories Gherkin, Spesifikasi 12 AI Tools, Matriks NFR ISO 25010 | ✅ Lengkap |
| **Tahap 2: SDD & Perancangan UML** | 📐 [`SDD.md`](SDD.md)<br>📐 [`SDD.pdf`](SDD.pdf) *(Standar IEEE 1016-2009)* | 📊 [`DIAGRAM.md`](DIAGRAM.md)<br>4 Diagram UML, ERD 20 Entitas, UI/UX | ✅ Lengkap |
| **Tahap 3: Konstruksi, AI & CI/CD** | ⚙️ [GitHub Actions Workflow](https://github.com/Yenvys/SistemWilayah/actions)<br>🧪 105 Automated Tests (540 Assertions Passed) | [`.github/workflows/ci.yml`](.github/workflows/ci.yml)<br>Kode Bersih (PSR-12, Laravel Pint Passed) | ✅ 100% Green |
| **Tahap 4: Pelaporan Final & Berkas Akhir** | 📊 [`LAPORAN_FINAL.md`](LAPORAN_FINAL.md)<br>📊 [`LAPORAN_FINAL.pdf`](LAPORAN_FINAL.pdf)<br>📝 [`DOKUMEN_LENGKAP_PROYEK_RPL_MUHAMMAD_AL_FAREL_41257124.docx`](DOKUMEN_LENGKAP_PROYEK_RPL_MUHAMMAD_AL_FAREL_41257124.docx) | 🖼️ [`DIAGRAM.md`](DIAGRAM.md) *(Kompilasi Gambar Diagram Arsitektur Lengkap)* | ✅ Lengkap |

---

## 📐 Kompilasi Diagram Arsitektur & Pemodelan UML

Kompilasi lengkap seluruh gambar diagram arsitektur, pemodelan UML, ERD, dan antarmuka dapat dilihat pada berkas khusus:
👉 **[DIAGRAM.md](DIAGRAM.md)**

| No | Jenis Diagram | Berkas Gambar | Penjelasan Singkat |
| :---: | :--- | :--- | :--- |
| 1 | **Use Case Diagram** | [`usecase_diagram.drawio.png`](usecase_diagram.drawio.png) | Interaksi 6 aktor (Publik, Warga, RT, RW, Desa, Super Admin) berbasis RBAC. |
| 2 | **Activity Diagram** | [`activity_diagram.drawio.png`](activity_diagram.drawio.png) | Alur bisnis persuratan nirkertas 3-tier dari pengajuan warga hingga penerbitan dokumen resmi ber-TTE. |
| 3 | **Class Diagram (SOLID)** | [`class_diagram.drawio.png`](class_diagram.drawio.png) | Struktur model, service layer, dan controller yang mematuhi 5 prinsip desain SOLID. |
| 4 | **Sequence Diagram** | [`sequence_diagram.drawio.png`](sequence_diagram.drawio.png) | Urutan pertukaran pesan transaksi persuratan dan eksekusi function calling Google Gemini AI. |
| 5 | **Entity Relationship Diagram (ERD)** | [`diagram.drawio.png`](diagram.drawio.png) | Struktur 20 tabel basis data relasional ternormalisasi dengan UUID primary keys. |
| 6 | **Prototipe UI/UX Dashboard** | [`dashboard_warga.jpg`](dashboard_warga.jpg)<br>[`dashboard_rt.jpg`](dashboard_rt.jpg)<br>[`dashboard_rw.jpg`](dashboard_rw.jpg)<br>[`dashboard_desa.jpg`](dashboard_desa.jpg) | Desain antarmuka mobile-first responsif untuk Warga, RT, RW, dan Kantor Desa. |

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
git clone https://github.com/Yenvys/SistemWilayah.git
cd SistemWilayah
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

# Konfigurasi Database (MySQL / SQLite):
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=siwil
DB_USERNAME=root
DB_PASSWORD=

# Kunci API AI (Google Gemini):
GEMINI_API_KEY=your_gemini_api_key_here
GEMINI_MODEL=gemini-2.5-flash
AI_PROVIDER=gemini

# Integrasi WhatsApp Bot Microservice (Port 3000):
WHATSAPP_BOT_URL=http://127.0.0.1:3000
WHATSAPP_BOT_NUMBER=628981295171

# Integrasi Telegram Bot:
TELEGRAM_BOT_TOKEN=your_telegram_bot_token_here
TELEGRAM_BOT_USERNAME=your_bot_username
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
Jalankan server aplikasi di terminal:

```bash
# Terminal 1 - Backend Server:
php artisan serve

# Terminal 2 - Frontend Development Server (Opsional):
npm run dev

# Terminal 3 - WhatsApp Bot Microservice (Opsional):
cd ../bot && npm start

# Terminal 4 - Telegram Bot cevlt (Opsional):
cd ../cevlt && npm start
```
*Aplikasi web akan aktif di:* `http://127.0.0.1:8000` (atau `http://localhost/siwil/public` jika via Laragon Apache).

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
