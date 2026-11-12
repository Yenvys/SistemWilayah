# PRODUCT REQUIREMENTS DOCUMENT (PRD)
## SIWIL — Sistem Informasi Wilayah & Pemerintahan Terpadu

---

### Document Overview
| Attribute | Specification |
| :--- | :--- |
| **Product Name** | **SIWIL** (*Sistem Informasi Wilayah Terpadu*) |
| **Document Type** | **Product Requirements Document (PRD)** |
| **Version** | **v2.5.0 (Final - Production / As-Built)** |
| **Product Owner / Lead Engineer** | **Muhammad Al Farel** (NIM: 41257124) |
| **Class / Cohort** | **TI 1B 2025** |
| **Institution** | **Program Studi S1 Teknik Informatika — STMIK IKMI Cirebon** |
| **Course & Code** | **Rekayasa Perangkat Lunak (TIKB2503021)** |
| **Course Supervisor / Lecturer** | **Yudhistira Arie Wijaya, M.Kom., CDS.** |
| **Topic / Theme** | **Smart Governance & Public Service AI** (*All-in-One Civil Administration*) |
| **Reference Standard** | **IEEE Std 830 / ISO/IEC/IEEE 29148 & ISO/IEC 25010** |
| **Status** | **Approved / Production-Ready** |

---

## 1. Product Overview & Strategic Vision

### 1.1 Problem Statement
Pelayanan administrasi kependudukan di tingkat rukun kemasyarakatan (RT/RW) hingga kantor kelurahan/desa di Indonesia umumnya masih menghadapi kendala fundamental:
1. **Rantai Birokrasi Bertingkat yang Manual & Lambat:** Warga harus secara fisik mendatangi kediaman Ketua RT untuk meminta surat pengantar basah, lalu ke rumah Ketua RW untuk cap pengesahan, dan mengantre di kantor desa/kelurahan pada jam dinas. Proses ini membutuhkan 2 hingga 5 hari kerja.
2. **Keterbatasan Jam Pelayanan Pengurus:** Pengurus RT dan RW adalah tokoh masyarakat sukarela yang memiliki pekerjaan utama di luar perannya, sehingga warga sering kali kesulitan bertemu langsung.
3. **Penyimpanan Terfragmentasi & Kerentanan Privasi (UU PDP):** Buku register kependudukan manual rawan rusak, hilang, dan rentan terhadap kebocoran data pribadi (NIK, Nomor KK, data keluarga), bertentangan dengan amanat **Undang-Undang No. 27 Tahun 2022 tentang Pelindungan Data Pribadi (UU PDP)**.
4. **Minimnya Transparansi APBDes & Saluran Aspirasi:** Informasi alokasi realisasi APBDes dan progres fisik proyek pembangunan desa jarang tersosialisasikan secara terbuka kepada warga. Partisipasi warga dalam musyawarah lingkungan sangat minim (15% - 25%).

### 1.2 Product Vision & Value Proposition
**SIWIL** dibangun sebagai platform *Smart Neighborhood & Village Governance Ecosystem* yang mendigitalisasi seluruh rantai pelayanan sipil secara *end-to-end* tanpa memutus tata krama hierarki struktural (RT $ightarrow$ RW $ightarrow$ Desa).

**Key Value Propositions:**
- **Zero Physical Paperwork:** Menghilangkan formulir manual dan fotokopi berkas dengan formulir digital, verifikasi berjenjang web, dan Tanda Tangan Elektronik (TTE) berbasis QR Code Validator berkekuatan hukum.
- **Validasi Kependudukan Zero Fake Account:** Registrasi mandiri warga memvalidasi silang kecocokan NIK dan No. KK terhadap basis data kependudukan desa, mengeliminasi akun palsu.
- **Asisten Cerdas SIWIL AI:** Didukung Large Language Model Google Gemini dengan 12 *backend function calling tools* untuk menjawab pertanyaan kependudukan, status berkas, dan anggaran secara aman sesuai koridor UU PDP.
- **Omnichannel Service Delivery:** Layanan dapat diakses fleksibel melalui portal web responsif, WhatsApp Messenger Bot (microservice port 3000), dan Telegram Bot resmi.

### 1.3 User Personas
1. **Warga (Citizen):** Penduduk resmi terdaftar yang membutuhkan pelayanan surat mandiri, pelaporan aduan fasilitas, tombol darurat SOS, partisipasi musyawarah, dan konsultasi asisten AI.
2. **Ketua RT (Neighborhood Leader):** Verifikator surat tingkat pertama, verifikator registrasi warga baru, pengelola kependudukan lokal RT, dan penanggap pertama sinyal darurat SOS.
3. **Ketua RW (Sub-district Coordinator):** Verifikator surat tingkat kedua, koordinator kebijakan wilayah antar-RT, dan fasilitator musyawarah tingkat RW.
4. **Pemerintah Desa (Village Administration):** Verifikator akhir dan penerbit surat resmi ber-TTE/stempel desa, pengelola anggaran APBDes dan monitoring proyek pembangunan fisik.
5. **Super Administrator:** Pengelola infrastruktur teknologi, konfigurasi integrasi AI/Bot/Mail, dan pengelola multi-desa.
6. **Publik / Tamu:** Masyarakat luas yang mengakses portal keterbukaan informasi APBDes, statistik kependudukan, dan pembangunan desa tanpa perlu login.

### 1.4 Success Metrics (Key Performance Indicators)
| Metric | Manual Baseline | SIWIL Target |
| :--- | :--- | :--- |
| **Waktu Rata-rata Terbit Surat** | 2 – 5 Hari Kerja | $< 4$ Jam (Instan jika disetujui) |
| **Penggunaan Kertas Fisik** | Formulir & Fotokopi berulang | 100% Paperless (PDF & QR TTE) |
| **Partisipasi Musyawarah / Polling** | 15% – 25% kehadiran fisik | $> 70\%$ hak pilih warga aktif |
| **Kecepatan Respon Pengaduan** | 3 – 7 Hari kerja | $< 24$ Jam untuk status penanganan awal |
| **Akurasi Validasi Akun Warga** | Rentan duplikasi data | 100% Validasi Silang NIK & KK |
| **Ketersediaan Layanan Informasi** | Jam kerja kantor desa | 24 Jam 7 Hari seminggu (Web + AI Bot) |

---

## 2. User Roles & Access Control Architecture (RBAC)

Sistem menerapkan arsitektur kontrol akses berbasis peran (*Role-Based Access Control*) menggunakan **Spatie Laravel Permission** dipadukan dengan kueri terisolasi (*Eloquent Scoped Queries*):

```
       ┌────────────────────────────────────────────────────────┐
       │                 SUPER ADMINISTRATOR                    │
       │       (Full System, Multi-Desa, Config, Audit)         │
       └───────────────────────────┬────────────────────────────┘
                                   │
       ┌───────────────────────────▼────────────────────────────┐
       │                       ADMIN DESA                       │
       │     (Penerbit Surat Resmi, APBDes, Teritorial Desa)    │
       └───────────────────────────┬────────────────────────────┘
                                   │
       ┌───────────────────────────▼────────────────────────────┐
       │                        KETUA RW                        │
       │        (Verifikator Tingkat 2, Lingkup RW Binaan)      │
       └───────────────────────────┬────────────────────────────┘
                                   │
       ┌───────────────────────────▼────────────────────────────┐
       │                        KETUA RT                        │
       │        (Verifikator Tingkat 1, Lingkup RT Binaan)      │
       └───────────────────────────┬────────────────────────────┘
                                   │
       ┌───────────────────────────▼────────────────────────────┐
       │                         WARGA                          │
       │      (Self-Service: Surat, Aduan, Polling, SOS, KK)    │
       └────────────────────────────────────────────────────────┘
```

### 2.1 Role Authority Matrix
| Modul / Fitur | Tamu Publik | Warga | Ketua RT | Ketua RW | Admin Desa | Super Admin |
| :--- | :---: | :---: | :---: | :---: | :---: | :---: |
| **Portal Publik (APBDes & Stat)** | Read | Read | Read | Read | Read | Read |
| **Dashboard Khusus** | ❌ | View Warga | View RT/RW | View RT/RW | View Desa | View Global |
| **Master Teritorial (RW/RT)** | ❌ | ❌ | Read (RT-nya) | Read (RW-nya) | Full CRUD | Full CRUD |
| **Master Penduduk (Residents)**| ❌ | ❌ (Profil) | CRUD (RT-nya) | Read (RW-nya) | Full CRUD | Full CRUD |
| **Data Kartu Keluarga (KK)** | ❌ | Read (KK-nya)| CRUD (RT-nya) | Read (RW-nya) | Full CRUD | Full CRUD |
| **Pengajuan Surat (Create)** | ❌ | Warga Sendiri | Untuk Warga | Untuk Warga | Untuk Warga | Untuk Warga |
| **Approval Surat (Tahap RT)** | ❌ | ❌ | Approve/Tolak | ❌ | Bypass | Bypass |
| **Approval Surat (Tahap RW)** | ❌ | ❌ | ❌ | Approve/Tolak | Bypass | Bypass |
| **Approval Surat (Tahap Desa)** | ❌ | ❌ | ❌ | ❌ | Approve & Terbit | Approve & Terbit |
| **Cetak PDF & QR Barcode TTE** | ❌ | Download Milik| Download RT | Download RW | Download Desa | Download Semua |
| **Pengaduan Foto & GPS** | ❌ | Create & Read | Respon RT | Respon RW | Respon Desa | Full Akses |
| **Tombol Darurat SOS** | ❌ | Trigger SOS | Alert & Respon| Alert & Respon| Alert & Respon | Alert & Respon |
| **Voting Musyawarah / Polling** | ❌ | Vote 1x | Vote 1x | Vote 1x | Vote 1x | Vote 1x |
| **Papan Pengumuman Desa** | Read | Read | Read | Read | Full CRUD | Full CRUD |
| **Manajemen APBDes & Proyek** | Read | Read | Read | Read | Full CRUD | Full CRUD |
| **Pusat Konfigurasi (AI, Bot)**| ❌ | ❌ | ❌ | ❌ | ❌ | Full CRUD & Test |
| **Audit Logs Sistem** | ❌ | ❌ | Read (RT Log) | Read (RW Log) | Read (Desa Log)| Full Audit Log |
| **Floating AI Assistant Widget** | ❌ | Scope Warga | Scope RT | Scope RW | Scope Desa | Scope Global |

---

## 3. System Architecture & Tech Stack

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
│   │   MySQL / SQLite (UUID Keys)    │  │  QR Code Verification │  │   Gemini / Multi-Model   │  │
│   └─────────────────────────────────┘  └───────────────────────┘  └──────────────────────────┘  │
└─────────────────────────────────────────────────────────────────────────────────────────────────┘
```

### 3.1 Technology Specifications
- **Backend Framework:** Laravel 13 (PHP 8.4+) with strict type declarations, PSR-12, and constructor property promotion.
- **Frontend Stack:** Tailwind CSS v4, Vite 8.2, Blade Components, Google Font Outfit, Feather/Heroicons SVG.
- **Primary Keys:** UUID v4 (`HasUuids`) on all domain tables to prevent ID enumeration (IDOR).
- **PDF & Digital Signature Engine:** `barryvdh/laravel-dompdf` with built-in QR Code hash verification.
- **AI Engine:** Google Gemini Generative API (`gemini-2.5-flash` / `gemini-1.5-flash`) with dynamic 12-tool function calling orchestration.
- **WhatsApp Gateway:** Node.js HTTP Microservice (Port 3000) supporting text, interactive menus, OTP tokens, and PDF streaming.
- **Telegram Gateway:** Telegram Bot API (`https://api.telegram.org/bot<TOKEN>`) with secure deep linking.

---

## 4. Product Features & Functional Requirements

### 4.1 Feature 1: Public Transparency Portal
**Description:** Halaman terbuka tanpa autentikasi (`/`, `/portal/*`) untuk keterbukaan tata kelola desa.

#### US-01: Akses Transparansi Realisasi APBDes
- **User Story:** As a citizen or visitor, I want to view the village annual budget (APBDes) transparently, so that I can hold the local government accountable.
- **Acceptance Criteria (Gherkin):**
  ```gherkin
  Scenario: Pengunjung publik membuka halaman APBDes
    Given Pengguna membuka halaman "/portal/apbdes"
    When Pengguna memilih tahun anggaran tertentu
    Then Sistem menampilkan breakdown 3 pilar: Pendapatan, Belanja, dan Pembiayaan
    And Sistem menghitung persentase serapan dana riil terhadap pagu anggaran
  ```

#### US-02: Monitoring Proyek Pembangunan Desa
- **User Story:** As a citizen, I want to monitor ongoing and completed physical infrastructure projects with progress bars and photos, so that I can track village development.
- **Acceptance Criteria (Gherkin):**
  ```gherkin
  Scenario: Pengunjung melihat progres proyek pembangunan
    Given Pengguna membuka halaman "/portal/pembangunan"
    When Halaman selesai dimuat
    Then Sistem menampilkan kartu proyek lengkap dengan foto lapangan, sumber dana, dan progress bar (0% - 100%)
  ```

---

### 4.2 Feature 2: Identity & Access Management (IAM)
**Description:** Modul registrasi mandiri, multi-identifier login, dan pemulihan akun berkeamanan tinggi.

#### US-03: Registrasi Warga Zero Fake Account
- **User Story:** As a new resident, I want to register using my NIK and KK number, so that my account is verified against official village records.
- **Acceptance Criteria (Gherkin):**
  ```gherkin
  Scenario: Registrasi warga dengan NIK dan KK valid
    Given Calon warga berada di halaman "/register"
    When Menginput NIK 16 digit dan Nomor KK 16 digit yang cocok di data kependudukan desa
    Then Sistem membuat akun baru dengan status "pending" menunggu persetujuan RT/Desa
    And Pengguna belum dapat login hingga diverifikasi
  ```
  ```gherkin
  Scenario: Penolakan registrasi NIK/KK fiktif
    Given Calon warga memasukkan NIK atau KK yang tidak ada di basis data desa
    When Menekan tombol "Daftar Akun"
    Then Sistem menolak proses dan memunculkan pesan validasi bahwa NIK tidak terdaftar
  ```

#### US-04: Pemulihan Password via WhatsApp OTP
- **User Story:** As a user who forgot my password, I want to receive a 6-digit OTP on my registered WhatsApp, so that I can safely recover my account.
- **Acceptance Criteria (Gherkin):**
  ```gherkin
  Scenario: Permintaan dan verifikasi kode OTP WhatsApp
    Given Pengguna memasukkan NIK atau nomor WhatsApp terdaftar di "/forgot-password"
    When Pengguna menekan tombol "Kirim Kode OTP"
    Then Sistem men-generate OTP 6 digit bervaliditas 5 menit
    And Mengirimkan pesan OTP secara instan via WhatsApp Gateway
    When Kode OTP diverifikasi cocok, pengguna diarahkan ke form reset kata sandi baru
  ```

---

### 4.3 Feature 3: Territorial & Population Management
**Description:** Pengelolaan master wilayah bertingkat (Desa $ightarrow$ RW $ightarrow$ RT) dan data kependudukan.

#### US-05: Pengelolaan Data Warga & Kartu Keluarga
- **User Story:** As a RT Leader, I want to record and update family cards and resident biodata within my neighborhood, so that demographic data stays current.
- **Acceptance Criteria (Gherkin):**
  ```gherkin
  Scenario: Ketua RT menambahkan data warga baru
    Given Ketua RT membuka menu "/dashboard/residents/create"
    When Mengisi NIK, nama lengkap, tanggal lahir, dan relasi KK
    Then Data warga tersimpan dengan relasi otomatis ke wilayah RT binaannya
  ```

#### US-06: Tinjauan Mandiri "Keluarga Saya"
- **User Story:** As a citizen, I want to review my family members registered in my Family Card (KK), so that I can verify family details privately.
- **Acceptance Criteria (Gherkin):**
  ```gherkin
  Scenario: Warga membuka menu Data Keluarga Saya
    Given Warga mengakses "/dashboard/my-family"
    When Sistem membaca No. KK dari profil pengguna
    Then Sistem menampilkan data kepala keluarga dan seluruh anggota keluarga terkait
    And Sistem mengisolasi data agar warga tidak dapat melihat KK warga lain
  ```

---

### 4.4 Feature 4: Digital Civil Services & 3-Tier Approval Workflow
**Description:** Pengajuan surat mandiri dan alur persetujuan nirkertas bertingkat (RT $ightarrow$ RW $ightarrow$ Desa) ber-TTE.

#### US-07: Pengajuan Surat Digital Mandiri
- **User Story:** As a citizen, I want to apply for administrative certificates (SKTM, Domisili, SKU, etc.) online with file attachments, so that I do not need physical paper forms.
- **Acceptance Criteria (Gherkin):**
  ```gherkin
  Scenario: Warga mengajukan surat keterangan
    Given Warga membuka menu "/dashboard/documents/create"
    When Memilih jenis surat, mengisi keperluan, dan mengunggah dokumen syarat
    Then Sistem men-generate nomor registrasi unik (DOC-YYYYMMDD-XXXX)
    And Menetapkan status awal permohonan menjadi "DIAJUKAN_RT"
    And Mengirimkan notifikasi instan ke Ketua RT setempat
  ```

#### US-08: Persetujuan Berjenjang 3-Tier & Penerbitan TTE QR Code
- **User Story:** As RT, RW, and Village leaders, we want to review applications in hierarchy and issue official PDF documents with verifiable digital QR codes.
- **Acceptance Criteria (Gherkin):**
  ```gherkin
  Scenario: Alur persetujuan surat berjenjang hingga terbit
    Given Permohonan surat berstatus "DIAJUKAN_RT"
    When Ketua RT menyetujui, status naik menjadi "DIAJUKAN_RW"
    When Ketua RW menyetujui, status naik menjadi "DIAJUKAN_DESA"
    When Admin Desa menyetujui, status berubah menjadi "DISETUJUI / SELESAI"
    And Sistem menyematkan stempel desa dan TTE digital
    And Sistem mengompilasi berkas PDF resmi lengkap dengan QR Code verifikasi
    And Warga menerima notifikasi otomatis bahwa surat siap diunduh
  ```

---

### 4.5 Feature 5: Citizen Complaints & Geotagging
**Description:** Saluran aspirasi warga dengan foto bukti fisik dan penanda koordinat GPS otomatis.

#### US-09: Pelaporan Kerusakan Sarana dengan Geotagging
- **User Story:** As a citizen, I want to report public facility issues with GPS coordinates and photos, so that field officers can locate the problem accurately.
- **Acceptance Criteria (Gherkin):**
  ```gherkin
  Scenario: Warga mengirim aduan dengan foto dan GPS
    Given Warga membuka "/dashboard/complaints/create"
    When Mengisi judul, deskripsi, upload foto, dan peramban membaca GPS
    Then Sistem menyimpan latitude dan longitude secara presisi
    And Aduan otomatis terdisposisi ke dashboard Ketua RT/RW terkait berstatus "PENDING"
  ```

---

### 4.6 Feature 6: Emergency SOS Panic Button
**Description:** Tombol darurat cepat dengan geolokasi instan untuk penanganan krisis lingkungan.

#### US-10: Pemicu Tombol Bahaya Darurat SOS
- **User Story:** As a resident in emergency (Medical, Fire, Crime, Disaster), I want to trigger the SOS button to alert all neighborhood leaders immediately with my location.
- **Acceptance Criteria (Gherkin):**
  ```gherkin
  Scenario: Warga menekan tombol SOS darurat
    Given Warga menekan tombol merah "EMERGENCY SOS" di dashboard
    When Memilih kategori darurat ("MEDIS" / "KEBAKARAN" / "KRIMINAL" / "BENCANA")
    Then Sistem menangkap koordinat GPS terkini dan menyimpan record status "ACTIVE"
    And Banner merah kedip darurat seketika muncul di dashboard RT, RW, dan Desa
  ```

---

### 4.7 Feature 7: E-Democracy & Musyawarah Polling
**Description:** Jajak pendapat digital berintegritas satu warga satu suara (One Citizen One Vote).

#### US-11: Musyawarah Warga Terarah
- **User Story:** As an eligible citizen, I want to vote on neighborhood polls and see aggregated results, so that democratic decisions are transparent.
- **Acceptance Criteria (Gherkin):**
  ```gherkin
  Scenario: Warga memberikan suara pada polling
    Given Terdapat polling musyawarah aktif di wilayah RT/RW warga
    When Warga memilih salah satu opsi dan menekan kirim suara
    Then Suara tercatat di tabel "poll_votes" dengan composite key unik (poll_id, user_id)
    And Sistem memblokir warga agar tidak dapat memilih lebih dari satu kali
  ```

---

### 4.8 Feature 8: Omnichannel Bot Integration (WhatsApp & Telegram)
**Description:** Layanan administrasi mandiri melalui aplikasi pesan instan sehari-hari.

#### US-12: Layanan Mandiri via Bot WhatsApp & Telegram
- **User Story:** As a citizen without desktop PC, I want to check document status and download PDFs directly from WhatsApp or Telegram, so that services are accessible anywhere.
- **Acceptance Criteria (Gherkin):**
  ```gherkin
  Scenario: Warga mengunduh PDF surat resmi via WhatsApp
    Given Nomor WhatsApp warga telah tertaut dengan akun SIWIL
    When Warga mengirimkan pesan menu surat dan memilih nomor surat yang telah disetujui
    Then Bot WhatsApp mengirimkan berkas PDF resmi ber-barcode langsung ke ruang obrolan
  ```

---

## 5. AI Assistant Specifications (SIWIL AI Engine)

```
┌─────────────────────────────────────────────────────────────────────────────────────────┐
│                                   SIWIL AI ARCHITECTURE                                 │
│                                                                                         │
│  [User Question] ──► [System Prompt + User Context + UU PDP Guardrail]                 │
│                                     │                                                   │
│                                     ▼                                                   │
│                     ┌───────────────────────────────┐                                   │
│                     │      Google Gemini LLM        │                                   │
│                     │  (Primary: Gemini 2.5 Flash)  │                                   │
│                     └───────────────┬───────────────┘                                   │
│                                     │                                                   │
│                  ┌──────────────────┴──────────────────┐                                │
│                  ▼                                     ▼                                │
│         [Direct Text Answer]               [Function Calling Request]                   │
│                                                        │                                │
│                                                        ▼                                │
│                                            ┌───────────────────────┐                    │
│                                            │     AiToolService     │                    │
│                                            │ (12 Backend RBAC Tools│                    │
│                                            └───────────┬───────────┘                    │
│                                                        │                                │
│                                                        ▼                                │
│                                            [Eloquent Scoped Query]                      │
│                                                        │                                │
│                                                        ▼                                │
│                                            [Safe Filtered JSON]                         │
│                                                        │                                │
│                                                        ▼                                │
│  [Final Answer to User] ◄────────────────── [Gemini Synthesizer]                        │
└─────────────────────────────────────────────────────────────────────────────────────────┘
```

### 5.1 Architecture & Multi-Model Engine
- **Primary LLM:** Google Gemini API (`gemini-2.5-flash` / `gemini-1.5-flash`).
- **Dynamic Context Injection:** Setiap permintaan chat menyuntikkan konteks pengguna (Nama, Peran, ID Wilayah RT/RW/Desa) secara otomatis ke dalam *system instruction*.
- **Multi-Key Rotation:** Mendukung rotasi otomatis array kunci API cadangan di database (`system_configurations.ai_api_keys`) apabila kunci utama terkena *rate limit* HTTP 429.
- **Smart Local Fallback:** Apabila seluruh kuota eksternal habis atau jaringan terputus, controller AI mengaktifkan *Local Regex Pattern Matching* untuk menjawab prosedur surat dan nomor darurat lokal tanpa memicu error HTTP 500.

### 5.2 Catalog of 12 Backend Function Calling Tools
AI tidak diperbolehkan mengarang bebas (*halusinasi*). AI dibekali 12 tools backend deterministik pada class `App\Services\AiToolService`:

| No | Function Tool Name | Deskripsi Fungsional Tool | Batasan Otorisasi & Scope Data |
| :-: | :--- | :--- | :--- |
| 1 | `fetch_dashboard_data` | Mengambil ringkasan statistik ringkas dashboard (total surat, aduan aktif). | Disesuaikan dengan role akun yang sedang login. |
| 2 | `fetch_residents_and_areas` | Mengambil data kependudukan dan struktur hierarki teritorial wilayah. | **Khusus Pengurus (RT/RW/Desa/SA)**. Ditolak keras untuk Warga. |
| 3 | `fetch_announcements` | Mengambil daftar pengumuman dan surat edaran desa yang aktif. | Dapat diakses seluruh peran pengguna. |
| 4 | `fetch_document_requests` | Mengambil riwayat permohonan surat administrasi beserta statusnya. | Warga hanya menerima datanya sendiri; Pengurus sesuai wilayah. |
| 5 | `fetch_complaints` | Mengambil data pengaduan masyarakat dan progres penanganannya. | Warga menerima aduan miliknya; Pengurus sesuai wilayah tugas. |
| 6 | `fetch_polls` | Mengambil daftar polling aktif, pilihan opsi, dan status vote pengguna. | Sesuai level jangkauan wilayah polling (RT / RW / Desa). |
| 7 | `fetch_family_data` | Mengambil rincian data Kartu Keluarga dan anggota keluarga pemohon. | **Wajib sama No. KK-nya**. Warga dilarang melihat KK orang lain. |
| 8 | `fetch_development_and_budgets` | Mengambil data proyek fisik pembangunan desa dan alokasi APBDes. | Khusus pengurus dan portal transparansi publik. |
| 9 | `fetch_audit_logs` | Mengambil jejak audit aktivitas manipulasi data sensitif sistem. | Khusus Super Administrator dan Pimpinan Wilayah. |
| 10 | `fetch_users_directory` | Mengambil data akun pengguna sistem dan status verifikasi akun. | Khusus Super Administrator dan Admin Desa. |
| 11 | `fetch_ai_knowledge` | Mengambil basis data pengetahuan resmi (SOP pelayanan, sejarah desa). | Dapat diakses seluruh peran sebagai referensi fakta resmi. |
| 12 | `fetch_current_user_profile` | Mengambil biodata dan profil lengkap dari akun yang sedang login. | Mengembalikan data privat milik pengguna terotentikasi. |

### 5.3 Privacy Guardrails & Penegakan Regulasi UU PDP
Sesuai amanat **Undang-Undang RI No. 27 Tahun 2022 tentang Pelindungan Data Pribadi (UU PDP)**, sistem menerapkan proteksi privasi berlapis:
- **Instruction Guardrail:** AI secara tegas menolak membagikan NIK, nomor KK, nomor WhatsApp, atau alamat privat warga lain kepada akun berstatus warga biasa.
- **Backend Deterministic Scoping:** Di tingkat kode backend (`AiToolService.php`), jika akun berstatus `WARGA` meminta data kependudukan umum, kueri database secara deterministik mengembalikan array kosong dan memblokir data sensitif.

---

## 6. Non-Functional Requirements (NFR)

Kebutuhan non-fungsional disusun mengacu pada standar kualitas perangkat lunak internasional **ISO/IEC 25010**:

| Dimension (ISO 25010) | Requirement Target | Implementation Specification |
| :--- | :--- | :--- |
| **Functional Suitability** | 100% kelengkapan fungsional modul | Seluruh fitur persuratan 3-tier, kependudukan, aduan GPS, APBDes, dan 12 AI tools lolos uji fungsional tanpa galat. |
| **Performance Efficiency** | Page load < 1.5 detik, AI response < 3.0 detik | Penggunaan Eager Loading (`with(...)`) untuk mengeliminasi N+1 query; paginasi data 10-25 baris; kompilasi Vite 8 minified assets. |
| **Compatibility** | Cross-browser & Mobile-first | Berfungsi sempurna di Chrome, Firefox, Safari, Edge; responsif dari layar smartphone 360px hingga desktop 1920px. |
| **Reliability & Resilience** | Uptime 99.5%, fail-safe third party | Blok `try-catch non-blocking` pada pengiriman WA/Telegram; rotasi multi-API key AI; smart local regex fallback saat offline. |
| **Security & Privacy** | Kepatuhan UU PDP No. 27/2022 | Hashing password Bcrypt (cost 12); UUID v4 primary keys; Eloquent Scoped Query `accessibleBy()`; proteksi OWASP (CSRF, XSS, SQLi). |
| **Maintainability** | Clean Architecture & Testability | Kepatuhan PSR-12 via Laravel Pint; pemisahan logika ke Service Classes; cakupan 105 Feature & Unit Tests terotomatisasi. |
| **Portability** | Multi-environment deployment | Konfigurasi terisolasi via `.env` dan `system_configurations`; kompatibel di Local (Laragon/XAMPP), VPS Linux, dan Cloud PaaS. |

---

## 7. Product Release Phases & Roadmap

### Phase 1: Current Release (v2.5 - Production Ready / As-Built)
- [x] Portal Transparansi Publik (APBDes, Demografi Kependudukan, Pembangunan Fisik).
- [x] Registrasi Warga Validasi Silang NIK & No. KK (Zero Fake Account).
- [x] Password Reset via WhatsApp OTP 6 Digit.
- [x] Pelayanan Persuratan 3-Tier (RT $ightarrow$ RW $ightarrow$ Desa) ber-TTE QR Code Validator & DomPDF.
- [x] Pengaduan Warga Berbasis Geotagging GPS & Foto Bukti.
- [x] Tombol Darurat Cepat (Emergency SOS Panic Button).
- [x] Musyawarah & Polling Demokrasi Digital (One Citizen One Vote).
- [x] Asisten Cerdas SIWIL AI (Google Gemini LLM, 12 Backend Tools, Guardrail UU PDP).
- [x] Integrasi Omnichannel Gateway Bot WhatsApp & Telegram.
- [x] Penjaminan Mutu 105 Automated Tests Passed & CI/CD Pipeline GitHub Actions Hijau.

### Phase 2: Next Minor Release (v2.6)
- [ ] Integrasi Payment Gateway (QRIS / Midtrans) untuk pembayaran iuran kas bulanan RT dan sampah secara digital.
- [ ] Pencatatan buku kas RT digital terotomasi dengan notifikasi bukti bayar WhatsApp.

### Phase 3: Major Release (v3.0)
- [ ] Progressive Web App (PWA) dengan push notifications instan dan mode offline-first.
- [ ] Integrasi REST API Nasional Dukcapil untuk verifikasi data e-KTP dan Kartu Identitas Anak (KIA) secara otomatis.
- [ ] Modul Keamanan Lingkungan & Ronda Malam berbasis pemindaian QR Code pos kamling.

---
*Dokumen Product Requirements Document (PRD) ini disusun secara komprehensif oleh Muhammad Al Farel (NIM: 41257124) sebagai acuan spesifikasi fungsional dan teknis aplikasi SIWIL.*
