# PRODUCT REQUIREMENTS DOCUMENT (PRD)
## SIWIL — Sistem Informasi Wilayah & Pemerintahan Terpadu

---

### Informasi Dokumen
| Atribut | Detail |
| :--- | :--- |
| **Nama Produk** | **SIWIL** (Sistem Informasi Wilayah Terpadu) |
| **Status Dokumen** | **Disetujui / Sesuai Implementasi (As-Built)** |
| **Versi Dokumen** | **v2.5.0** |
| **Target Rilis** | Produksi & Operasional Wilayah |
| **Domain Aplikasi** | Smart Governance, Pelayanan Publik Wilayah, Administrasi Desa/Kelurahan, RT/RW |
| **Tech Stack Utama** | Laravel 11/13, PHP 8.3+, Tailwind CSS v4, Vite 8, MySQL/SQLite, Spatie Permission, Gemini AI, DomPDF |
| **Kanal Layanan** | Web Portal Publik, Dashboard Back-Office, WhatsApp Bot Microservice, Telegram Bot |

---

## 1. Executive Summary & Visi Produk

### 1.1 Latar Belakang Masalah
Pelayanan administrasi kependudukan di tingkat rukun tetangga (RT), rukun warga (RW), dan kelurahan/desa di Indonesia umumnya masih menghadapi kendala klasik:
1. **Birokrasi Bertingkat yang Manual & Lambat:** Warga harus mendatangi rumah Ketua RT untuk meminta surat pengantar fisik, lalu mendatangi Ketua RW untuk cap pengesahan, dan mengantre di kantor desa/kelurahan untuk menerbitkan surat keterangan resmi.
2. **Keterbatasan Jam Pelayanan Fisik:** Pengurus RT dan RW adalah tokoh masyarakat yang memiliki pekerjaan utama di luar perannya, sehingga warga sering kesulitan bertemu langsung.
3. **Penyimpanan Data Terfragmentasi:** Data kependudukan tersimpan di buku register manual yang rawan rusak, hilang, tidak sinkron antar tingkatan, dan rentan terhadap kebocoran data privasi.
4. **Minimnya Transparansi & Saluran Aspirasi:** Informasi realisasi APBDes, proyek pembangunan fisik, serta keluhan sarana publik jarang tersampaikan secara transparan kepada warga secara dua arah.

### 1.2 Visi & Nilai Strategis Produk
**SIWIL** dirancang sebagai *platform ekosistem tata kelola wilayah dan pemerintahan desa cerdas terpadu* (*all-in-one smart neighborhood & village governance platform*). 
SIWIL mendigitalisasi seluruh rantai pelayanan sipil secara *end-to-end* tanpa memutus tata krama hierarki struktural (RT $\rightarrow$ RW $\rightarrow$ Desa), mengintegrasikan asisten kecerdasan buatan (*SIWIL AI Engine*) berbasis LLM dengan function calling aman (UU PDP), serta menghadirkan keterhubungan *omnichannel* ke aplikasi percakapan sehari-hari warga (WhatsApp dan Telegram).

### 1.3 Target Pengguna
1. **Warga Masyarakat:** Seluruh penduduk yang terdaftar resmi pada Kartu Keluarga (KK) di wilayah terkait.
2. **Pengurus RT (Rukun Tetangga):** Verifikator tingkat pertama dan garda terdepan pendataan warga serta penanganan keluhan lingkungan.
3. **Pengurus RW (Rukun Warga):** Verifikator tingkat kedua, koordinator kebijakan wilayah antar-RT, dan fasilitator musyawarah.
4. **Pemerintah Desa / Kelurahan (Admin Desa & Kepala Desa):** Pengambil keputusan utama, penerbit surat resmi berkekuatan hukum, administrator data kependudukan, pengelola proyek fisik dan anggaran APBDes.
5. **Super Administrator:** Pengelola infrastruktur sistem, penyedia konfigurasi integrasi AI/Bot/Mail, dan pengelola entitas multi-desa.

---

## 2. Tujuan Produk & Key Performance Indicators (KPI)

### 2.1 Tujuan Produk (Product Goals)
- **Zero Physical Paperwork for Requests:** Memangkas 90% kebutuhan berkas fotokopi manual dengan formulir digital, verifikasi berjenjang di web/bot, dan tanda tangan digital (TTE/Barcode).
- **Omnichannel Service Delivery:** Memberikan kemudahan bagi warga yang tidak terbiasa membuka website komputer agar dapat mengajukan surat, melapor aduan, dan ikut polling langsung lewat WhatsApp atau Telegram.
- **Transparansi & Akuntabilitas Anggaran:** Membuka data APBDes dan progres proyek pembangunan desa kepada masyarakat umum melalui portal publik interaktif.
- **Kepatuhan Privasi & Regulasi:** Mengimplementasikan kontrol akses berbasis peran (RBAC) yang ketat sesuai amanat Undang-Undang Perlindungan Data Pribadi (UU PDP).
- **Layanan Cerdas 24/7:** Asisten cerdas SIWIL AI yang dapat menjawab prosedur, merekap metrik, dan mengeksekusi *read-only retrieval* data wilayah secara kontekstual dan aman.

### 2.2 Metrik Keberhasilan (KPI)
| Metrik | Baseline Manual | Target SIWIL |
| :--- | :--- | :--- |
| **Rata-rata Waktu Terbit Surat** | 2 – 5 Hari Kerja | $< 4$ Jam (untuk surat berjenjang), Instan jika disetujui |
| **Partisipasi Warga dalam Musyawarah/Polling** | $15\% - 25\%$ hadir tatap muka | $> 70\%$ partisipasi aktif pemegang hak pilih |
| **Kecepatan Respon Pengaduan Warga** | $3 - 7$ Hari | $< 24$ Jam untuk status penanganan awal |
| **Ketersediaan Layanan Informasi** | Jam kerja kantor desa | 24 Jam 7 Hari seminggu (Portal Web + AI Bot) |
| **Akurasi Validasi Akun Warga** | Sering terjadi data ganda | $100\%$ validasi silang NIK dan No. KK kependudukan |

---

## 3. Aktor Sistem & Arsitektur Hak Akses (RBAC)

SIWIL mengadopsi kontrol akses berlapis menggunakan **Spatie Laravel Permission** dipadukan dengan *custom query scoping* di level Eloquent Model.

```
       ┌────────────────────────────────────────────────────────┐
       │                 SUPER ADMINISTRATOR                    │
       │       (Full Access, Multi-Desa, System Config)         │
       └───────────────────────────┬────────────────────────────┘
                                   │
       ┌───────────────────────────▼────────────────────────────┐
       │                       ADMIN DESA                       │
       │     (Penerbit Surat Resmi, APBDes, Wilayah Desa)       │
       └───────────────────────────┬────────────────────────────┘
                                   │
       ┌───────────────────────────▼────────────────────────────┐
       │                        KETUA RW                        │
       │        (Verifikator Tingkat 2, Wilayah Lingkup RW)     │
       └───────────────────────────┬────────────────────────────┘
                                   │
       ┌───────────────────────────▼────────────────────────────┐
       │                        KETUA RT                        │
       │        (Verifikator Tingkat 1, Wilayah Lingkup RT)     │
       └───────────────────────────┬────────────────────────────┘
                                   │
       ┌───────────────────────────▼────────────────────────────┐
       │                         WARGA                          │
       │      (Self-Service: Surat, Aduan, Polling, SOS, KK)    │
       └────────────────────────────────────────────────────────┘
```

### 3.1 Rincian Hak Akses & Tanggung Jawab Aktor

| Aktor / Role | Guard / Role Key | Ruang Lingkup Otoritas (Scope) | Hak Akses Utama |
| :--- | :--- | :--- | :--- |
| **Super Admin** | `superadmin` / `SUPER_ADMIN` | Global (Seluruh Desa & Sistem) | CRUD Desa, konfigurasi AI & Bot API, konfigurasi SMTP, manajemen seluruh akun pengguna, akses audit logs, seluruh data kependudukan. |
| **Admin Desa** | `desa` / `ADMIN_DESA` | 1 Desa/Kelurahan Terkait | CRUD RW & RT di wilayahnya, verifikasi akhir penerbitan surat, pengesahan TTE/Stempel Desa, manajemen pembangunan & APBDes, CRUD Pengumuman, verifikasi akun warga pending. |
| **Ketua RW** | `rw` / `RW` | 1 Rukun Warga (Meliputi seluruh RT binaan) | Verifikasi berkas surat tingkat 2, pemantauan statistik warga di lingkup RW, disposisi aduan antar-RT, fasilitator musyawarah tingkat RW. |
| **Ketua RT** | `rt` / `RT` | 1 Rukun Tetangga | Verifikasi permohonan surat tingkat 1, pengelolaan master kependudukan & KK di RT-nya, verifikasi akun registrasi warga lokal, respons aduan & darurat SOS. |
| **Warga** | `warga` / `WARGA` | Terbatas pada NIK & No. KK Pribadi | Pengajuan surat mandiri, pemantauan tracking status surat, pengaduan foto & GPS, partisipasi polling/musyawarah, pemicu tombol darurat SOS, konsultasi AI Chatbot. |

### 3.2 Matriks Hak Akses Modul (RBAC Matrix)

| Modul Fitur | Warga | Ketua RT | Ketua RW | Admin Desa | Super Admin |
| :--- | :---: | :---: | :---: | :---: | :---: |
| **Portal Publik (Beranda, APBDes, Stat)** | Read | Read | Read | Read | Read |
| **Dashboard Khusus** | View (Warga) | View (RT/RW) | View (RT/RW) | View (Desa) | View (Global) |
| **Master Wilayah (Desa, RW, RT)** | ❌ | Read (RT-nya) | Read (RW-nya) | Full CRUD (RW/RT) | Full CRUD (Semua) |
| **Data Kependudukan (NIK/Penduduk)** | ❌ (Hanya Profil) | CRUD (RT-nya) | Read (RW-nya) | Full CRUD (Desa) | Full CRUD (Semua) |
| **Data Keluarga (Kartu Keluarga)** | Read (KK Sendiri) | CRUD (RT-nya) | Read (RW-nya) | Full CRUD (Desa) | Full CRUD (Semua) |
| **Pengajuan Surat (Create)** | Warga Sendiri | Untuk Warga | Untuk Warga | Untuk Warga | Untuk Warga |
| **Approval Surat (Tahap RT)** | ❌ | Approve/Tolak | ❌ | Bypass Approve | Bypass Approve |
| **Approval Surat (Tahap RW)** | ❌ | ❌ | Approve/Tolak | Bypass Approve | Bypass Approve |
| **Approval Surat (Tahap Desa)** | ❌ | ❌ | ❌ | Approve & Terbit | Approve & Terbit |
| **Cetak Surat PDF & Barcode TTE** | Download Milik | Download RT | Download RW | Download Desa | Download Semua |
| **Lapor Pengaduan (Foto + GPS)** | Create & Read | Respon RT | Respon RW | Respon Desa | Full Akses |
| **Tombol Darurat (SOS)** | Trigger SOS | Alert & Respon | Alert & Respon | Alert & Respon | Alert & Respon |
| **Musyawarah / Polling (Vote)** | Vote 1x | Vote 1x | Vote 1x | Vote 1x | Vote 1x |
| **Musyawarah / Polling (Create)** | ❌ | Create (RT) | Create (RW) | Create (Desa) | Create (Global) |
| **Pengumuman Desa** | Read | Read | Read | CRUD | CRUD |
| **Pembangunan & APBDes** | Portal Publik | Read | Read | Full CRUD | Full CRUD |
| **Spatial Analytics (GIS Map)** | ❌ | ❌ | ❌ | Read & Analisis | Read & Analisis |
| **Laporan & Audit Log** | ❌ | Read (RT Log) | Read (RW Log) | Read (Desa Log) | Full Audit Log |
| **Manajemen User & Verifikasi** | ❌ | Verifikasi RT | ❌ | Verifikasi Desa | Full CRUD User |
| **Pusat Konfigurasi (AI, Bot, Mail)**| ❌ | ❌ | ❌ | ❌ | Full CRUD & Test |
| **AI Assistant (Chatbot Floating)** | Ya (Scope Warga)| Ya (Scope RT) | Ya (Scope RW) | Ya (Scope Desa)| Ya (Scope SA) |

---

## 4. Arsitektur Teknologi & Infrastruktur Sistem

```
┌─────────────────────────────────────────────────────────────────────────────────────────────────┐
│                                       USER ACCESS LAYER                                         │
│   Web Browser (PC & Mobile)        WhatsApp Messenger                 Telegram Messenger        │
│   Tailwind CSS v4 + Blade UI       Node.js Bot Microservice (Port 3000)  Official Telegram Bot  │
└───────────────────┬───────────────────────────────┬──────────────────────────────┬──────────────┘
                    │                               │                              │
                    ▼                               ▼                              ▼
┌─────────────────────────────────────────────────────────────────────────────────────────────────┐
│                                    APPLICATION GATEWAY (LARAVEL)                                │
│   HTTP Web Middleware                     API Routing Prefix: /api/                             │
│   - auth, profile.complete                - /api/whatsapp/*  (Bot Webhook & Controller)         │
│   - Spatie RoleMiddleware                 - /api/telegram/*  (Deep link, Login, Services)       │
└───────────────────────────────────────────────────┬─────────────────────────────────────────────┘
                                                    │
                                                    ▼
┌─────────────────────────────────────────────────────────────────────────────────────────────────┐
│                                   CORE SERVICE & BUSINESS LOGIC                                 │
│   ┌─────────────────────┐   ┌──────────────────────┐   ┌─────────────────────────────────────┐  │
│   │   ResidentService   │   │DocumentRequestService│   │         EmailNotificationService    │  │
│   └─────────────────────┘   └──────────────────────┘   └─────────────────────────────────────┘  │
│   ┌─────────────────────┐   ┌──────────────────────┐   ┌─────────────────────────────────────┐  │
│   │   WhatsAppService   │   │   TelegramService    │   │           AiToolService             │  │
│   │   (Baileys/HTTP)    │   │   (Telegram Bot API) │   │     (12 Dynamic Function Calling)   │  │
│   └─────────────────────┘   └──────────────────────┘   └─────────────────────────────────────┘  │
└───────────────────────────────────────────────────┬─────────────────────────────────────────────┘
                                                    │
                                                    ▼
┌─────────────────────────────────────────────────────────────────────────────────────────────────┐
│                                  DATA ACCESS & EXTERNAL ENGINES                                 │
│   ┌─────────────────────────────────┐  ┌───────────────────────┐  ┌──────────────────────────┐  │
│   │   Eloquent ORM (20 Models)      │  │  Barryvdh DomPDF      │  │   LLM AI Provider Engine │  │
│   │   MySQL / SQLite (UUID Keys)    │  │  QR Code Verification │  │   Gemini / OpenAI / Groq │  │
│   └─────────────────────────────────┘  └───────────────────────┘  └──────────────────────────┘  │
└─────────────────────────────────────────────────────────────────────────────────────────────────┘
```

### 4.1 Spesifikasi Komponen Teknologi
- **Framework Utama:** Laravel 11 / 13 (PHP 8.3+) dengan PSR-12 standard, strict type declarations, dan constructor property promotion.
- **Frontend Stack:** Tailwind CSS v4, Vite 8, Blade Templating, Google Font Outfit, Feather/Heroicons SVG.
- **Identitas Primary Key:** Menggunakan **UUID v4** (`HasUuids`) di seluruh entitas bisnis demi mencegah enumerasi URL id dan keamanan multi-tenancy.
- **PDF Generation Engine:** `barryvdh/laravel-dompdf` dikombinasikan dengan QR Code generation service untuk tanda tangan elektronik (TTE).
- **Asisten AI:** Multi-provider LLM (Default: Google Gemini 2.5 Flash / 3.5 Flash) dengan dukungan fallback API key rotasi berjenjang.
- **Integrasi Bot WhatsApp:** Menggunakan Microservice Node.js HTTP REST (Port 3000) yang menyediakan *interactive buttons*, pengiriman file dokumen PDF terkompilasi, dan pesan balasan otomatis.
- **Integrasi Bot Telegram:** Integrasi langsung melalui `TelegramService` ke Telegram Bot API (`https://api.telegram.org/bot<TOKEN>`) dengan dukungan *Deep Linking Authentication* via cache token.

---

## 5. Rincian Spesifikasi Fitur Fungsional

### 5.1 Modul 1: Portal Transparansi Publik (Public Portal)
Portal publik dapat diakses tanpa autentikasi (`/`, `/portal/*`) untuk memenuhi prinsip transparansi tata kelola desa:
1. **Beranda Desa (`/`):**
   - Menampilkan profil desa, kepala desa, dan statistik agregat ringkas: Total Penduduk, Total KK, Total RW, Total RT.
   - Papan Pengumuman Publik terbaru (5 artikel teratas).
   - Indikator proyek pembangunan fisik yang sedang berlangsung (*Ongoing*) dan telah rampung (*Completed*).
2. **Transparansi APBDes (`/portal/apbdes`):**
   - Pemilihan tahun anggaran dinamis (berdasarkan data riil di database).
   - Rincian breakdown 3 klasifikasi fiskal desa: **Pendapatan**, **Belanja**, dan **Pembiayaan**.
   - Perbandingan Pagu Anggaran (*Budget Allocated*) vs Realisasi Fisik (*Realized Amount*) beserta persentase serapan dana.
3. **Statistik Kependudukan (`/portal/statistik`):**
   - Rasio gender (Laki-laki vs Perempuan).
   - Distribusi jenjang pendidikan (SD, SMP, SMA, D3, S1, S2, dsb).
   - Top 10 Profesi kependudukan terbanyak.
   - Analisis Piramida Generasi Usia: Balita (0-5 tahun), Anak-anak (6-12 tahun), Remaja (13-17 tahun), Dewasa (18-59 tahun), Lansia (60+ tahun).
4. **Monitoring Pembangunan (`/portal/pembangunan`):**
   - Daftar proyek fisik desa dilengkapi foto proyek, progress bar capaian (0-100%), sumber dana (DD, ADD, PADes), timeline pelaksanaan, dan nilai anggaran proyek.

---

### 5.2 Modul 2: Autentikasi, Registrasi & Manajemen Pengguna (IAM)
1. **Registrasi Warga Berbasis Validasi Silang (Zero Fake Accounts):**
   - Syarat registrasi mandiri: Warga memasukkan **NIK (16 digit)** dan **Nomor KK (16 digit)**.
   - Backend memverifikasi bahwa NIK dan No. KK tersebut telah terdaftar sebelumnya di database kependudukan resmi desa (`exists:residents,nik` dan `exists:families,kk_number`).
   - Akun yang baru mendaftar memiliki status default `pending`.
2. **Verifikasi Akun Bertingkat:**
   - Ketua RT atau Admin Desa memvalidasi akun baru di menu Manajemen User.
   - Akun disetujui (`status = active`) atau ditolak (`status = rejected`).
   - Warga tidak dapat login selama akun berstatus `pending` atau `rejected`.
3. **Multi-Identifier Login:**
   - Input username universal: Mendukung login menggunakan **NIK**, **Alamat Email**, atau **Nomor Handphone/WhatsApp**.
4. **Pemulihan Kata Sandi dengan WhatsApp OTP (Password Reset Engine):**
   - Warga memasukkan NIK atau nomor WhatsApp terdaftar.
   - Sistem men-generate **OTP 6 digit acak**, disimpan di cache dengan masa berlaku 5 menit.
   - OTP dikirim secara instan ke WhatsApp pemohon.
   - Form verifikasi OTP dilengkapi pembatasan *resend throttle* (60 detik) dan proteksi *brute force*.
   - Setelah OTP valid, pengguna diarahkan ke form reset kata sandi baru.
5. **Pemeriksaan Kelengkapan Profil Wajib (`profile.complete` Middleware):**
   - Pengguna dengan peran `WARGA` yang belum melengkapi data email dan nomor HP/WA secara otomatis dialihkan ke halaman pengaturan profil hingga data tersebut terisi.
6. **Tanda Tangan Elektronik & Stempel Resmi:**
   - Pengurus RT, RW, dan Admin Desa dapat mengunggah berkas gambar transparansi tanda tangan digital (`signature_path`) dan stempel wilayah (`stamp_path`) pada menu profil untuk disematkan otomatis ke berkas PDF surat resmi.

---

### 5.3 Modul 3: Manajemen Kependudukan & Struktur Wilayah (Master Data)
1. **Hirarki Wilayah Dinamis:**
   - Hubungan bertingkat 3 jenjang: `Desa` $\rightarrow$ `RW` (Rukun Warga) $\rightarrow$ `RT` (Rukun Tetangga).
   - Pengurus RT hanya memiliki akses ke RT binaannya; Ketua RW memiliki akses agregat ke seluruh RT dalam RW-nya; Admin Desa menguasai seluruh RW/RT di desanya; Super Admin dapat mengelola banyak Desa.
2. **Manajemen Kartu Keluarga (KK):**
   - Setiap KK memiliki nomor 16 digit unik, alamat domisili, dan relasi ke RT terkait.
   - Fitur "Data Keluarga Saya" (`/dashboard/my-family`) memungkinkan warga memeriksa seluruh anggota keluarga yang terdaftar dalam KK-nya beserta status hubungan keluarga.
3. **Master Data Penduduk (Residents):**
   - Biodata komprehensif: NIK, Nama Lengkap, Tempat/Tanggal Lahir, Jenis Kelamin, Agama, Status Perkawinan, Jenjang Pendidikan, Pekerjaan, Status Kependudukan (Aktif, Pindah, Meninggal), Penanda Kepala Keluarga, dan Foto e-KTP.
   - Filter pencarian canggih berbasis nama, NIK, nomor RT, nomor RW, dan status aktif.

---

### 5.4 Modul 4: Layanan Administrasi & Persuratan Digital (E-Services)
1. **Katalog Layanan Surat:**
   - Surat Keterangan Domisili
   - Surat Keterangan Tidak Mampu (SKTM)
   - Surat Keterangan Usaha (SKU)
   - Surat Pengantar Nikah (ke KUA)
   - Surat Izin Keramaian
   - Surat Keterangan Kehilangan / Legalitas
   - Surat Izin Renovasi Rumah
   - Surat Pendataan Tamu Menginap
   - Surat Pengantar Umum RT/RW
2. **Alur Kerja Persetujuan Berjenjang (Multi-Tier Approval Workflow):**
   - **Level Surat RT Saja (Final RT):** Izin Renovasi, Pendataan Tamu Menginap $\rightarrow$ Selesai begitu disetujui RT.
   - **Level Surat RW Saja (Final RW):** Izin Keramaian, Pengantar Kehilangan $\rightarrow$ Disetujui RT $\rightarrow$ Diteruskan ke RW $\rightarrow$ Selesai begitu disetujui RW.
   - **Level Surat Desa (Standard 3-Tier):** SKTM, Domisili, SKU, Pengantar Nikah $\rightarrow$ Warga mengajukan (`DIAJUKAN_RT`) $\rightarrow$ Disetujui RT (`DIAJUKAN_RW`) $\rightarrow$ Disetujui RW (`DIAJUKAN_DESA`) $\rightarrow$ Diterbitkan Desa (`DISETUJUI`/`SELESAI`).
   - Fast-Track: Admin Desa atau Super Admin memiliki wewenang diskresi untuk langsung menyetujui surat dalam keadaan mendesak.
3. **Audit Trail Persuratan (`DocumentRequestHistory`):**
   - Setiap aksi (diajukan, diteruskan, disetujui, ditolak) tercatat lengkap dengan *timestamp*, ID aktor pengambil keputusan, dan catatan/alasan verifikasi.
4. **Penerbitan Dokumen Digital & QR Code Validator:**
   - Format cetak PDF resmi berbasis DomPDF dengan layout surat dinamis (`formats/desa.blade.php`, `formats/domisili.blade.php`, dsb).
   - Tanda tangan digital dan stempel tertera secara otomatis.
   - QR Code verifikasi dokumen asli tertanam pada berkas: dapat dipindai oleh pihak ketiga (Bank, Instansi, Kepolisian) untuk membuktikan keabsahan surat secara *real-time*.

---

### 5.5 Modul 5: Pengaduan Warga & Penanganan Masalah (Citizen Complaints)
1. **Pengajuan Pengaduan:**
   - Formulir pelaporan dengan Judul, Deskripsi Masalah, Lokasi Keterangan, Unggah Foto Bukti Fisik, dan penanda koordinat GPS presisi (`location_lat`, `location_lng`).
2. **Filter Yurisdiksi Penanganan:**
   - Pengaduan diklasifikasikan ke wilayah RT dan RW terkait.
   - RT dapat merespons dan menyelesaikan aduan di wilayahnya; RW memantau aduan yang melintasi beberapa RT; Admin Desa dapat menangani aduan infrastruktur berskala besar.
3. **Siklus Status Aduan:**
   - `PENDING` (Baru Masuk) $\rightarrow$ `PROCESSING` (Sedang Ditangani/Ditinjau) $\rightarrow$ `RESOLVED` (Selesai Ditangani) atau `REJECTED` (Ditolak disertai alasan).
4. **Notifikasi Otomatis:**
   - Pelapor mendapatkan notifikasi *real-time* (In-App, WA, Telegram, Email) setiap kali status penanganan aduannya diperbarui petugas.

---

### 5.6 Modul 6: Sistem Tanggap Darurat (Emergency SOS Button)
1. **Aktivasi Tombol Darurat:**
   - Tombol cepat SOS dapat ditekan kapan saja oleh warga dari dashboard.
   - Klasifikasi insiden darurat: **Medis**, **Kebakaran**, **Kriminal**, atau **Bencana Alam**.
   - Otomatis mengambil koordinat GPS *real-time* perangkat pelapor saat tombol ditekan.
2. **Penanganan & Alerting:**
   - Status darurat `ACTIVE` memicu banner merah darurat di dashboard Ketua RT, RW, dan Kantor Desa secara bersamaan.
   - Pengurus wilayah dapat menghubungi pelapor atau langsung menuju titik lokasi koordinat.
   - Tombol "Tandai Selesai" (`RESOLVED`) digunakan pengurus setelah situasi darurat teratasi.

---

### 5.7 Modul 7: Musyawarah & Polling Digital (E-Democracy)
1. **Pembuatan Jajak Pendapat / Polling:**
   - Dapat dibuat oleh Admin Desa, Ketua RW, atau Ketua RT.
   - Dilengkapi batas waktu berakhir (`expires_at`), deskripsi musyawarah, dan multi-opsi pilihan.
   - Penentuan cakupan level wilayah: **Tingkat DESA**, **Tingkat RW**, atau **Tingkat RT**.
2. **Aturan Notifikasi Terarah (Targeted Scope Notification):**
   - Polling tingkat RT: Notifikasi undangan memilih **hanya** dikirimkan kepada warga yang tinggal di RT tersebut.
   - Polling tingkat RW atau Desa: Notifikasi dikirimkan universal ke seluruh warga di wilayah tersebut.
3. **Integritas Pemilihan (One Citizen One Vote):**
   - Setiap pengguna berhak memilih tepat 1 kali pada sebuah polling (`unique(poll_id, user_id)`).
   - Warga dapat memilih langsung melalui Web Dashboard atau membalas interaktif via Bot WhatsApp.
4. **Hasil Musyawarah Otomatis:**
   - Persentase suara terakumulasi langsung secara transparan.
   - Saat status ditutup (`CLOSED`), sistem secara otomatis menghitung dan menetapkan `winning_option` (opsi dengan suara terbanyak) serta mengirimkan hasil akhir musyawarah ke seluruh warga melalui WhatsApp dan Telegram.

---

### 5.8 Modul 8: Papan Pengumuman & Informasi Desa (Announcements)
1. **Publikasi Berita & Surat Edaran:**
   - Admin Desa dan Pengurus Wilayah dapat merilis pengumuman resmi dengan masa berlaku dan status publish.
2. **Omnichannel Broadcasting:**
   - Pengumuman yang baru diterbitkan dapat di-broadcast serentak ke:
     * In-App Notification seluruh warga.
     * Email Notifikasi menggunakan template HTML resmi.
     * Bot WhatsApp & Bot Telegram saluran warga.

---

### 5.9 Modul 9: Pembangunan Desa, APBDes & Analisis Spasial (GIS Hub)
1. **Monitoring Proyek Pembangunan:**
   - Input data proyek: Judul, Deskripsi, Pagu Anggaran, Sumber Dana (Dana Desa, Alokasi Dana Desa, PADes, Bantuan Keuangan), Tanggal Mulai & Selesai, Titik Koordinat Lokasi, serta Dokumentasi Foto.
   - Status pengerjaan: `PLANNED` (Direncanakan), `ONGOING` (Sedang Dikerjakan), `COMPLETED` (Selesai).
   - Input progres persentase fisik ($0\% - 100\%$).
2. **Tata Kelola Anggaran APBDes:**
   - Pencatatan pos anggaran per tahun fiskal berdasarkan 3 pilar: Pendapatan, Belanja, dan Pembiayaan.
   - Rekonsiliasi nominal anggaran yang direncanakan (*budget*) terhadap serapan riil (*realized*).
3. **Peta Spasial Terpadu (GIS Interactive Map):**
   - Memvisualisasikan titik lokasi fisik proyek pembangunan desa pada peta interaktif Leaflet.
   - *Overlay* sebaran titik pengaduan warga dalam 30 hari terakhir untuk mendeteksi kawasan rawan keluhan lingkungan.
   - *Overlay* titik insiden darurat (SOS) aktif secara *real-time*.

---

### 5.10 Modul 10: Kecerdasan Buatan Terintegrasi (SIWIL AI Assistant)
1. **Floating Chatbot Widget Interaktif:**
   - Widget asisten mengambang di pojok kanan bawah seluruh halaman dashboard dengan tema adaptif (terang/gelap).
   - Mendukung input pertanyaan teks natural Bahasa Indonesia serta tombol *shortcut* navigasi.
2. **Multi-Model Orchestration & Fallback Engine:**
   - Kompatibel dengan model Google Gemini (Gemini 2.5/3.5 Flash), OpenAI GPT-4o, Groq Llama, atau LLM lokal (Ollama via custom base URL).
   - Mendukung rotasi multi-API Key: jika kunci utama terkena *rate-limit* kuota, sistem otomatis beralih ke kunci cadangan.
   - Dilengkapi *Smart Fallback Pattern Matching* jika seluruh kuota API eksternal sedang tidak tersedia.
3. **Dynamic Prompt & Penegakan Privasi (UU PDP Guardrails):**
   - Sistem menginjeksi identitas terotentikasi pengguna secara dinamis ke system prompt.
   - **UU PDP Restriction:** Jika akun adalah warga biasa, AI secara tegas dilarang membocorkan nomor HP, NIK, alamat, atau biodata warga lain di luar Kartu Keluarganya sendiri.
4. **Katalog 12 Backend AI Tools (Function Calling):**
   1. `fetch_dashboard_data`: Mengambil ringkasan metrik statistik dashboard sesuai role.
   2. `fetch_residents_and_areas`: Mengambil data warga dan struktur wilayah (khusus pengurus).
   3. `fetch_announcements`: Mengambil daftar pengumuman resmi desa.
   4. `fetch_document_requests`: Mengambil daftar permohonan surat administrasi warga.
   5. `fetch_complaints`: Mengambil daftar keluhan/aspirasi warga sesuai yurisdiksi.
   6. `fetch_polls`: Mengambil daftar musyawarah aktif, pilihan opsi, dan status vote pengguna.
   7. `fetch_family_data`: Mengambil rincian data KK dan anggota keluarga pemohon.
   8. `fetch_development_and_budgets`: Mengambil data proyek fisik dan anggaran APBDes (khusus pengurus).
   9. `fetch_audit_logs`: Mengambil catatan audit log sistem (khusus pengurus).
   10. `fetch_users_directory`: Mengambil direktori akun pengguna (khusus Admin Desa/Super Admin).
   11. `fetch_ai_knowledge`: Mengambil basis pengetahuan FAQ, SOP wilayah, dan regulasi desa.
   12. `fetch_current_user_profile`: Mengambil biodata akun yang sedang login saat ini.
5. **Manajemen AI Knowledge Base (`AiKnowledge`):**
   - Super Admin dapat menambah dan menyunting topik pengetahuan resmi (SOP pelayanan, peraturan wilayah, sejarah desa) yang menjadi referensi utama jawaban AI.

---

### 5.11 Modul 11: Integrasi Omnichannel Bot (WhatsApp & Telegram Gateway)
1. **Layanan Mandiri via WhatsApp Bot (`/api/whatsapp/*`):**
   - **Cek Status Pengguna:** Otomatis mengenali pengguna berdasarkan nomor WhatsApp internasional (`628xxx`) atau WhatsApp LID.
   - **Login & Tautkan Akun:** Memasukkan kredensial login via pesan chat untuk menghubungkan WhatsApp dengan akun SIWIL.
   - **Layanan Persuratan:** Menampilkan daftar katalog surat, mengajukan surat baru, memeriksa status pengajuan, dan mengunduh berkas PDF surat resmi langsung di dalam chat WhatsApp.
   - **Layanan Pengaduan:** Mengirimkan laporan pengaduan masyarakat disertai foto bukti secara langsung.
   - **Musyawarah & Polling:** Melihat daftar polling aktif dan memberikan suara (voting) menggunakan nomor opsi atau tombol interaktif.
2. **Layanan Mandiri via Telegram Bot (`/api/telegram/*`):**
   - **Deep Link Authentication:** Web dashboard menyediakan tombol tautkan akun yang menghasilkan URL `https://t.me/<bot>?start=<token>` bervaliditas 15 menit melalui Redis/Cache.
   - **Login Mandiri:** Warga dapat mengetikkan login dan password langsung ke bot Telegram untuk menautkan akun.
   - **Pengajuan & Tracking Surat:** Layanan pembuatan dan pelacakan surat dengan format pesan HTML rapi.
   - **Download Berkas PDF:** Unduh dokumen surat resmi ber-barcode langsung sebagai berkas dokumen Telegram.
3. **Tri-Channel Notification Hub:**
   - Setiap mutasi status surat, respons aduan, pendaftaran polling baru, dan penutupan polling secara paralel memicu:
     1. Notifikasi In-App Database (`AppNotification`).
     2. Pesan WhatsApp via WhatsApp Gateway.
     3. Pesan Telegram via Telegram Bot API.
     4. Surat Elektronik (Email) via SMTP Mailable.

---

### 5.12 Modul 12: Pusat Konfigurasi Terpusat (System Configuration Hub)
1. **Pengaturan Dinamis Database (`system_configurations`):**
   - Konfigurasi kredensial tidak lagi terikat kaku pada file `.env` server, melainkan dapat diatur langsung oleh Super Admin melalui antarmuka web.
   - Konfigurasi AI: Pilihan Provider, Nama Model, Array API Keys cadangan, Custom Base URL.
   - Konfigurasi WhatsApp: Microservice Bot Webhook URL, Nomor Bot Pengirim, API Token.
   - Konfigurasi Telegram: Bot Token Resmi, Bot Username.
   - Konfigurasi Email: Mailer, Host, Port, Username, Password, Encryption, Sender Address & Name.
2. **Masking Kredensial Sensitif:**
   - Password SMTP, API Key AI, dan Bot Token disamarkan secara otomatis di frontend (`•••••••`) untuk mencegah insiden pengintipan layar (*shoulder surfing*).
3. **Pengujian Koneksi Langsung (Diagnostics Tool):**
   - Tombol **Test AI Connection**: Memverifikasi validitas kunci AI dan responsivitas LLM.
   - Tombol **Test WhatsApp Gateway**: Menguji komunikasi HTTP ke microservice bot port 3000.
   - Tombol **Test Telegram Bot**: Memanggil method `getMe` ke API Telegram.
   - Tombol **Test Email SMTP**: Mengirimkan email uji coba ke alamat pengguna yang sedang login.

---

### 5.13 Modul 13: Laporan, Analitik & Audit Log Aktivitas
1. **Tampilan Dashboard Berbasis Peran:**
   - **Warga (`dashboard.warga`):** Ringkasan surat mandiri, aduan pribadi, polling aktif, pengumuman desa, dan widget darurat SOS.
   - **Ketua RT/RW (`dashboard.rt_rw`):** Metrik kependudukan wilayah, berkas surat yang menanti verifikasi, aduan warga baru, daftar darurat aktif, dan partisipasi musyawarah.
   - **Admin Desa / Super Admin (`dashboard.index`):** Pohon struktur wilayah lengkap (Desa $\rightarrow$ RW $\rightarrow$ RT dengan jumlah KK dan jiwa), rekapitulasi pengguna, pengumuman desa, dan laporan darurat aktif.
2. **Audit Log Aktivitas (`audit_logs`):**
   - Mencatat seluruh operasi manipulasi data sensitif: Jenis Aksi (`CREATE`, `UPDATE`, `DELETE`, `LOGIN`), Nama Entitas, ID Entitas, ID Pengguna, serta payload detail perubahan data.
   - Dilengkapi filter pencarian teks, filter jenis aksi, dan filter entitas terdampak.

---

## 6. Spesifikasi Skema Database & Relasi (Data Dictionary)

Sistem menggunakan 20 model Eloquent dengan UUID Primary Key sebagai fondasi data:

```
┌──────────────┐       1:N       ┌──────────────┐       1:N       ┌──────────────┐
│   villages   ├────────────────►│     rws      ├────────────────►│     rts      │
└──────┬───────┘                 └──────────────┘                 └──────┬───────┘
       │                                                                 │ 1:N
       │ 1:N                                                             ▼
       │                         ┌──────────────┐       1:N       ┌──────────────┐
       ├────────────────────────►│  residents   │◄────────────────┤   families   │
       │                         └──────┬───────┘                 └──────────────┘
       │                                │ 1:1 (NIK = username)
       │ 1:N                            ▼
       │                         ┌──────────────┐
       ├────────────────────────►│    users     ├──────────────────────────┐
       │                         └──┬───┬───┬───┘                          │
       │                            │   │   │                              │
       ▼                            │   │   │                              ▼
┌──────────────┐                    │   │   │ 1:N                 ┌──────────────────┐
│   budgets    │                    │   │   └────────────────────►│   emergencies    │
└──────────────┘                    │   │                         └──────────────────┘
                                    │   │ 1:N                     ┌──────────────────┐
┌──────────────┐                    │   ├────────────────────────►│  announcements   │
│ developments │                    │   │                         └──────────────────┘
└──────────────┘                    │   │ 1:N                     ┌──────────────────┐
                                    │   └────────────────────────►│app_notifications │
┌──────────────────┐                ▼                             └──────────────────┘
│document_requests │◄───────────────┤ 1:N (resident / verifier)   ┌──────────────────┐
└────────┬─────────┘                ├────────────────────────────►│    complaints    │
         │ 1:N                      │                             └──────────────────┘
         ▼                          ▼ 1:N                         ┌──────────────────┐
┌──────────────────┐         ┌──────────────┐       1:N           │   poll_votes     │
│doc_req_histories │         │    polls     ├────────────────────►└────────▲─────────┘
└──────────────────┘         └──────┬───────┘                              │ 1:N
                                    │ 1:N                                  │
                                    ▼                                      │
                             ┌──────────────┐                              │
                             │ poll_options ├──────────────────────────────┘
                             └──────────────┘
```

### 6.1 Rincian Entitas Data Utama

| Nama Tabel | Deskripsi & Tujuan | Kolom Kunci / Atribut Penting |
| :--- | :--- | :--- |
| `villages` | Data master desa / kelurahan | `id` (UUID), `name`, `code` |
| `rws` | Data master Rukun Warga | `id` (UUID), `number`, `village_id` |
| `rts` | Data master Rukun Tetangga | `id` (UUID), `number`, `rw_id` |
| `families` | Data master Kartu Keluarga (KK) | `id` (UUID), `kk_number` (Unique), `address`, `rt_id` |
| `residents` | Data induk kependudukan warga | `id` (UUID), `nik` (Unique), `full_name`, `family_id`, `is_head_of_family`, `birth_place`, `birth_date`, `gender`, `religion`, `marital_status`, `education`, `occupation`, `status`, `rt_id`, `ktp_photo` |
| `users` | Akun autentikasi login pengguna | `id` (UUID), `username` (NIK/UID), `name`, `email`, `phone_number`, `whatsapp_number`, `whatsapp_lid`, `telegram_id`, `address`, `status`, `kk_number`, `password`, `role`, `village_id`, `rw_id`, `rt_id`, `signature_path`, `stamp_path` |
| `document_requests`| Transaksi pengajuan surat | `id` (UUID), `request_number`, `type`, `status`, `notes`, `resident_id`, `verified_by_id`, `rt_approved_by_id`, `rw_approved_by_id`, `desa_approved_by_id`, `attachment_path`, `completed_at` |
| `document_request_histories` | Log jejak verifikasi surat | `id` (UUID), `document_request_id`, `status`, `notes`, `actor_id` |
| `complaints` | Laporan keluhan warga | `id` (UUID), `user_id`, `title`, `description`, `location`, `location_lat`, `location_lng`, `photo_path`, `status`, `rt_id`, `rw_id`, `village_id` |
| `emergencies` | Rekaman panggilan darurat (SOS) | `id` (UUID), `user_id`, `type`, `location_lat`, `location_lng`, `status` |
| `polls` | Induk musyawarah / polling | `id` (UUID), `title`, `description`, `created_by`, `status`, `level`, `village_id`, `rw_id`, `rt_id`, `expires_at` |
| `poll_options` | Opsi pilihan dalam musyawarah | `id` (UUID), `poll_id`, `text` |
| `poll_votes` | Transaksi suara warga | `id` (UUID), `poll_id`, `poll_option_id`, `user_id` (Unique composite) |
| `announcements` | Papan pengumuman & edaran | `id` (UUID), `title`, `content`, `author_id`, `is_active`, `published_at` |
| `budgets` | Data pos anggaran APBDes | `id` (UUID), `village_id`, `year`, `type`, `category`, `description`, `amount_budget`, `amount_realized` |
| `developments` | Proyek fisik pembangunan | `id` (UUID), `village_id`, `title`, `description`, `budget_allocated`, `source_of_fund`, `status`, `progress_percentage`, `location_lat`, `location_lng`, `start_date`, `end_date`, `image_path` |
| `app_notifications` | Notifikasi in-app pengguna | `id` (UUID), `user_id`, `title`, `message`, `type`, `reference_id`, `url`, `is_read`, `read_at` |
| `audit_logs` | Jejak audit keamanan sistem | `id` (UUID), `user_id`, `action`, `entity_name`, `entity_id`, `details` |
| `ai_knowledge` | Basis data pengetahuan AI | `id` (UUID), `topic`, `content`, `last_updated_by` |
| `system_configurations`| Konfigurasi singleton dinamis | `id` (UUID), `ai_provider`, `ai_model`, `ai_api_keys`, `ai_base_url`, `whatsapp_bot_url`, `whatsapp_bot_number`, `whatsapp_api_token`, `telegram_bot_token`, `telegram_bot_username`, `mail_*`, `extra` |

---

## 7. Spesifikasi Rute & Antarmuka API

### 7.1 Rute Web Terbuka & Portal Publik
- `GET /` : Halaman Utama Portal Desa
- `GET /portal/apbdes` : Transparansi APBDes per tahun
- `GET /portal/statistik` : Visualisasi data demografi kependudukan
- `GET /portal/pembangunan` : Daftar & kemajuan proyek fisik wilayah

### 7.2 Rute Autentikasi & Reset Password
- `GET|POST /login` : Form & aksi login akun (NIK / Email / HP)
- `GET|POST /register` : Form & aksi registrasi warga baru
- `POST /logout` : Keluar dari sesi aplikasi
- `GET|POST /forgot-password` : Pengajuan kode OTP via WhatsApp
- `GET|POST /forgot-password/verify` : Form verifikasi kode OTP 6 digit
- `POST /forgot-password/resend` : Kirim ulang kode OTP
- `GET|POST /forgot-password/reset` : Form pergantian password baru

### 7.3 Rute Dashboard & Back-Office (Terlindungi `auth`, `profile.complete`)
- `GET /dashboard` : Dashboard dinamis sesuai peran pengguna
- `GET /dashboard/documents*` : Manajemen permohonan surat, status tracking, cetak PDF
- `GET /dashboard/complaints*` : Manajemen pengaduan warga
- `POST /dashboard/emergencies` : Pemicu tombol darurat SOS
- `PATCH /dashboard/emergencies/{id}/resolve` : Resolusi darurat
- `GET /dashboard/polls*` : Pembuatan, partisipasi vote, dan penutupan polling
- `GET /dashboard/announcements*` : Informasi & pengumuman desa
- `GET /dashboard/my-family` : Tinjauan Kartu Keluarga & relasi anggota (Warga)
- `GET /dashboard/settings` : Pengaturan profil, ubah password, tautkan Telegram/WhatsApp
- `POST /dashboard/ai/chat` : Endpoint percakapan cerdas SIWIL AI Assistant
- `GET /dashboard/areas` : Master wilayah RW & RT (`role:SUPER_ADMIN,ADMIN_DESA,RW,RT`)
- `GET /dashboard/residents*` : Manajemen data kependudukan (`role:SUPER_ADMIN,ADMIN_DESA,RW,RT`)
- `GET /dashboard/users*` : Manajemen akun pengguna & verifikasi pending
- `GET /dashboard/reports` : Laporan statistik & audit logs
- `GET /dashboard/developments*` : Manajemen proyek pembangunan & anggaran APBDes
- `GET /dashboard/configuration*` : Pusat konfigurasi sistem & pengujian integrasi (`role:SUPER_ADMIN`)

### 7.4 Rute API Integrasi WhatsApp Bot (`/api/whatsapp/`)
- `POST /api/whatsapp/check-user` : Verifikasi nomor pengirim WhatsApp ke database SIWIL
- `POST /api/whatsapp/login` : Login akun SIWIL via pesan WhatsApp
- `POST /api/whatsapp/logout` : Putuskan koneksi WhatsApp dari akun pengguna
- `GET  /api/whatsapp/services` : Ambil katalog layanan surat resmi yang tersedia
- `POST /api/whatsapp/documents` : Ajukan permohonan surat baru via bot WhatsApp
- `GET  /api/whatsapp/documents/{number}` : Ambil riwayat status surat warga
- `GET  /api/whatsapp/documents/{id}/pdf` : Unduh dokumen PDF resmi ber-barcode
- `POST /api/whatsapp/complaints` : Kirim pengaduan warga via bot WhatsApp
- `GET  /api/whatsapp/complaints/{number}` : Ambil riwayat status aduan warga
- `GET  /api/whatsapp/polls/{number}` : Ambil daftar musyawarah aktif untuk warga
- `POST /api/whatsapp/polls/vote` : Kirimkan pilihan suara (voting) musyawarah

### 7.5 Rute API Integrasi Telegram Bot (`/api/telegram/`)
- `POST /api/telegram/connect` : Validasi token deep link untuk menautkan Telegram ID
- `POST /api/telegram/login` : Login kredensial akun langsung di Telegram Bot
- `POST /api/telegram/logout` : Putuskan tautan akun dari Telegram Bot
- `GET  /api/telegram/user/{id}` : Periksa data akun berdasarkan Telegram ID
- `GET  /api/telegram/services` : Ambil katalog layanan persuratan
- `POST /api/telegram/documents` : Buat permohonan surat dari Telegram
- `GET  /api/telegram/documents/{id}` : Cek status permohonan surat
- `GET  /api/telegram/documents/{id}/pdf`: Unduh dokumen PDF dari Telegram
- `POST /api/telegram/complaints` : Kirim laporan pengaduan dari Telegram
- `GET  /api/telegram/complaints/{id}` : Ambil riwayat aduan pengguna di Telegram

---

## 8. Persyaratan Non-Fungsional (NFR)

### 8.1 Keamanan & Kepatuhan Regulasi (Security & Compliance)
- **Kepatuhan UU PDP (Undang-Undang Perlindungan Data Pribadi):** Data pribadi kependudukan warga (NIK, Nomor KK, Nomor Telepon, Alamat Lengkap) dilarang keras dipaparkan kepada publik atau warga lain. AI Assistant dibekali *deterministic role guardrail* untuk memblokir permohonan data warga lain.
- **Enkripsi Kredensial:** Seluruh kata sandi di-hash menggunakan algoritma Bcrypt (`BCRYPT_ROUNDS=12`). Token verifikasi dan OTP WhatsApp dienkripsi dan memiliki batas kadaluarsa 5–15 menit.
- **Proteksi Otorisasi Scoped Queries:** Setiap query Eloquent pada model `Resident`, `DocumentRequest`, dan `Poll` wajib menggunakan method scope `accessibleBy(User $user)` agar pengurus RT/RW tidak dapat melihat atau memanipulasi data di luar yurisdiksi teritorialnya.

### 8.2 Keandalan & Toleransi Kesalahan (Reliability & Resilience)
- **Multi-Key & Fallback AI:** Jika penyedia LLM mengalami limitasi kuota (HTTP 429), sistem otomatis melakukan perulangan ke daftar kunci cadangan pada `system_configurations.ai_api_keys`. Jika seluruh kunci habis, sistem kembali ke pencocokan aturan pintar internal (*smart keyword-based fallback*).
- **Asynchronous Safe Notifications:** Pengiriman notifikasi ke WhatsApp, Telegram, dan Email dibungkus dalam blok proteksi `try-catch` sehingga kegagalan koneksi pihak ketiga (misal server bot WhatsApp offline) tidak menggagalkan transaksi database utama aplikasi.

### 8.3 Kinerja & Efisiensi (Performance)
- **Paginasi Data:** Seluruh tabel daftar data kependudukan, surat, aduan, dan log audit menggunakan paginasi (10–25 baris per halaman) dengan eager loading relasi (`with(['resident', 'rt.rw'])`) untuk mencegah masalah query ganda (*N+1 problem*).
- **Aset Terkompilasi:** Menggunakan Tailwind CSS v4 dan Vite 8 dengan optimasi minifikasi file CSS/JS demi pemuatan halaman yang cepat di perangkat berkecepatan internet rendah.

### 8.4 Aksesibilitas & Responsivitas Antarmuka (UX & Accessibility)
- **Mobile-First Responsive Design:** Seluruh layout dashboard back-office maupun portal publik dirancang fleksibel menyesuaikan layar ponsel cerdas (Android/iOS) hingga monitor desktop.
- **Dark & Light Mode Support:** Tersedia tombol pengganti tema instan di bilah atas (*topnav*) yang menyimpan preferensi tema pengguna di `localStorage`.

---

## 9. Rangkuman Penjaminan Mutu & Pengujian (QA & Testing)

Project SIWIL telah dilengkapi dengan rangkaian pengujian otomatis (*Automated Feature & Unit Testing*) menggunakan **PHPUnit** dengan tingkat keberhasilan tinggi:

| Komponen Uji | File Test Suite | Fokus Verifikasi | Status |
| :--- | :--- | :--- | :---: |
| **Alur Autentikasi** | `AuthWorkflowTest.php` | Registrasi warga valid/invalid, status pending, login NIK/Email/HP, reset OTP WA | PASS |
| **Kecerdasan Buatan** | `AiChatTest.php` | Hak akses query AI, guardrail UU PDP, pembatasan data kependudukan warga | PASS |
| **AI Tool Function Calling** | `AiToolServiceTest.php` | Eksekusi 12 AI tools, validasi parameter, RBAC filter data kembalian | PASS |
| **Validasi Wilayah** | `AreaValidationTest.php` | Validasi hirarki Desa $\rightarrow$ RW $\rightarrow$ RT, pencegahan duplikasi nomor | PASS |
| **Pusat Pembangunan** | `DevelopmentHubTest.php` | CRUD proyek pembangunan, pembukuan APBDes, kalkulasi persentase serapan | PASS |
| **Sistem Notifikasi** | `NotificationWorkflowTest.php` | Pembuatan notifikasi internal, unread badge counter, mark as read | PASS |
| **Musyawarah & Polling**| `PollWorkflowTest.php` | Scope wilayah polling, targeted notification, one citizen one vote | PASS |
| **Profil Pengguna** | `SettingProfileTest.php` | Update biodata, upload signature & stamp, validasi nomor WA | PASS |
| **Pusat Konfigurasi** | `SystemConfigurationTest.php` | Penyimpanan singleton config, masking secret keys, test connection dispatchers | PASS |
| **Integrasi Telegram** | `TelegramAuthTest.php` | Validasi deep link token, direct login bot Telegram, disconnect session | PASS |
| **Notifikasi Telegram** | `TelegramComplaintNotificationTest.php` | Notifikasi status aduan ke chat Telegram pengguna | PASS |
| **Notifikasi Dokumen** | `TelegramDocumentNotificationTest.php` | Notifikasi persetujuan surat & pengiriman link PDF via Telegram | PASS |

*Total Hasil Pengujian Keseluruhan: 105 Feature & Unit Tests, 538 Assertions.*

---

## 10. Roadmap & Rencana Pengembangan Masa Depan

1. **Fase 1 (Next Minor Release - v2.6):**
   - Integrasi PPOB / Payment Gateway (Midtrans / Xendit / QRIS) untuk pembayaran iuran kas bulanan RT dan sampah secara digital langsung di aplikasi.
   - Perekaman riwayat pembayaran kas RT terintegrasi ke buku kas RT digital.
2. **Fase 2 (Major Release - v3.0):**
   - Aplikasi Mobile Native / PWA (Progressive Web App) dengan dukungan notifikasi *push* (FCM) dan mode *offline-first*.
   - Integrasi REST API Nasional Dukcapil untuk verifikasi data e-KTP dan Kartu Identitas Anak (KIA) secara otomatis.
   - Modul Keamanan Wilayah & Ronda Malam: Penjadwalan ronda RT dan absensi ronda digital berbasis pemindaian QR Code di pos kamling.

---
*Dokumen PRD ini disusun berdasarkan analisis mendalam arsitektur kode sumber, basis data, aturan bisnis, dan spesifikasi fungsional implementasi SIWIL.*

