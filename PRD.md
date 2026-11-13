# SOFTWARE REQUIREMENTS SPECIFICATION (SRS) & PRODUCT REQUIREMENT DOCUMENT (PRD)
## SIWIL AI: Sistem Informasi Wilayah Terpadu Berbasis Kecerdasan Buatan
### Standar Dokumentasi: IEEE Std 830-1998 / ISO/IEC/IEEE 29148:2018

---

| Informasi Dokumen | Rincian Spesifikasi |
| :--- | :--- |
| **Nama Proyek** | **SIWIL AI** (*Sistem Informasi Wilayah Terpadu*) |
| **Nomor Dokumen** | SRS-SIWIL-2026-V1.0 |
| **Mata Kuliah** | Rekayasa Perangkat Lunak (TIKB2503021) — Semester 3 |
| **Institusi** | Program Studi S1 Teknik Informatika, STMIK IKMI Cirebon |
| **Model Pengembangan** | Agile / Software Development Life Cycle (SDLC) Berbasis Standar OBE |
| **Status Dokumen** | *Baseline Approved & Production-Ready Specification* |
| **Versi Dokumen** | 1.1 (IEEE 830 & ISO 29148 Standard Edition) |

---

## DAFTAR ISI

1. [PENDAHULUAN (INTRODUCTION)](#1-pendahuluan-introduction)
   - 1.1 [Tujuan Pembuatan Dokumen](#11-tujuan-pembuatan-dokumen)
   - 1.2 [Ruang Lingkup Produk (Product Scope)](#12-ruang-lingkup-produk-product-scope)
   - 1.3 [Definisi, Akronim, dan Singkatan](#13-definisi-akronim-dan-singkatan)
   - 1.4 [Referensi Normatif](#14-referensi-normatif)
   - 1.5 [Gambaran Umum Dokumen](#15-gambaran-umum-dokumen)
2. [DESKRIPSI KESELURUHAN (OVERALL DESCRIPTION)](#2-deskripsi-keseluruhan-overall-description)
   - 2.1 [Perspektif Produk](#21-perspektif-produk)
   - 2.2 [Karakteristik & Profil Aktor (User Personas)](#22-karakteristik--profil-aktor-user-personas)
   - 2.3 [Batasan Perancangan & Implementasi (Design Constraints)](#23-batasan-perancangan--implementasi-design-constraints)
   - 2.4 [Asumsi dan Ketergantungan (Assumptions & Dependencies)](#24-asumsi-dan-ketergantungan-assumptions--dependencies)
3. [SPESIFIKASI KEBUTUHAN FUNGSIONAL (USER STORIES & ACCEPTANCE CRITERIA)](#3-spesifikasi-kebutuhan-fungsional-user-stories--acceptance-criteria)
   - 3.1 [FR-01: Autentikasi & Pre-Enrollment NIK Terverifikasi](#31-fr-01-autentikasi--pre-enrollment-nik-terverifikasi)
   - 3.2 [FR-02: Pemulihan Akun via OTP WhatsApp](#32-fr-02-pemulihan-akun-via-otp-whatsapp)
   - 3.3 [FR-03: Hierarki Wilayah & Administrasi Kependudukan](#33-fr-03-hierarki-wilayah--administrasi-kependudukan)
   - 3.4 [FR-04: Layanan Persuratan Berjenjang & Digital Signature](#34-fr-04-layanan-persuratan-berjenjang--digital-signature)
   - 3.5 [FR-05: Penerbitan Vector PDF & Verifikasi QR Code](#35-fr-05-penerbitan-vector-pdf--verifikasi-qr-code)
   - 3.6 [FR-06: Pengaduan Warga Berbasis Spasial (GIS)](#36-fr-06-pengaduan-warga-berbasis-spasial-gis)
   - 3.7 [FR-07: Tombol Darurat Satu Ketukan (SOS Panic Button)](#37-fr-07-tombol-darurat-satu-ketukan-sos-panic-button)
   - 3.8 [FR-08: Musyawarah Digital & Polling Warga (E-Voting Scoped)](#38-fr-08-musyawarah-digital--polling-warga-e-voting-scoped)
   - 3.9 [FR-09: Transparansi Anggaran Desa (APBDes Digital)](#39-fr-09-transparansi-anggaran-desa-apbdes-digital)
   - 3.10 [FR-10: Monitoring Proyek Pembangunan Fisik Berbasis GIS](#310-fr-10-monitoring-proyek-pembangunan-fisik-berbasis-gis)
   - 3.11 [FR-11: Portal Informasi Publik & Pengumuman Warga](#311-fr-11-portal-informasi-publik--pengumuman-warga)
   - 3.12 [FR-12: Layanan Omnichannel Bot (WhatsApp & Telegram)](#312-fr-12-layanan-omnichannel-bot-whatsapp--telegram)
4. [SPESIFIKASI FITUR KECERDASAN BUATAN UTAMA (AI SPECIFICATION)](#4-spesifikasi-fitur-kecerdasan-buatan-utama-ai-specification)
   - 4.1 [Arsitektur Agen Cerdas SIWIL AI](#41-arsitektur-agen-cerdas-siwil-ai)
   - 4.2 [Dynamic System Prompt & Session Context Injection](#42-dynamic-system-prompt--session-context-injection)
   - 4.3 [Deterministic UU PDP Guardrails (Anti-Scraping Data Pribadi)](#43-deterministic-uu-pdp-guardrails-anti-scraping-data-pribadi)
   - 4.4 [12 Native Tool Function Declarations](#44-12-native-tool-function-declarations)
   - 4.5 [Mekanisme Multi-Key API Rotation & Smart Fallback](#45-mekanisme-multi-key-api-rotation--smart-fallback)
5. [SPESIFIKASI KEBUTUHAN NON-FUNGSIONAL (NFR)](#5-spesifikasi-kebutuhan-non-fungsional-nfr)
   - 5.1 [Performance & Response Latency](#51-performance--response-latency)
   - 5.2 [Security & Cryptography](#52-security--cryptography)
   - 5.3 [Reliability & High Availability](#53-reliability--high-availability)
   - 5.4 [Usability & Mobile-First Accessibility](#54-usability--mobile-first-accessibility)
   - 5.5 [Maintainability & Clean Architecture](#55-maintainability--clean-architecture)
6. [MATRIKS KETERTELUSURAN KEBUTUHAN (TRACEABILITY MATRIX)](#6-matriks-ketertelusuran-kebutuhan-traceability-matrix)

---

## 1. PENDAHULUAN (INTRODUCTION)

### 1.1 Tujuan Pembuatan Dokumen
Dokumen *Software Requirements Specification* (SRS) dan *Product Requirement Document* (PRD) ini disusun sebagai pedoman formal berstandar **IEEE 830-1998** dan **ISO/IEC/IEEE 29148:2018** untuk merancang, membangun, menguji, dan memvalidasi aplikasi web utuh **SIWIL AI (Sistem Informasi Wilayah Terpadu)**. Dokumen ini menjadi acuan mutlak bagi perancangan diagram UML, konstruksi basis kode, integrasi agen kecerdasan buatan, verifikasi pengujian otomatis, serta pengoperasian sistem.

### 1.2 Ruang Lingkup Produk (Product Scope)
SIWIL AI adalah perangkat lunak tata kelola pemerintahan mikro (*Smart Governance & Civic Micro-Services Platform*) yang mengotomasi seluruh rantai proses administrasi kependudukan dari tingkat Rukun Tetangga (RT), Rukun Warga (RW), hingga Kantor Desa/Kelurahan.

Ruang lingkup sistem mencakup:
1. **Administrasi Kependudukan Berjenjang:** Pendataan penduduk, Kartu Keluarga (KK), dan relasi kewilayahan hierarkis.
2. **Layanan Persuratan Digital:** Pengajuan mandiri, verifikasi berjenjang (RT $\to$ RW $\to$ Desa), pembubuhan tanda tangan/stempel dinas digital otomatis, dan penerbitan vector PDF resmi ber-QR Code.
3. **Partisipasi Warga & Respons Cepat:** Pelaporan aduan terkoordinat GPS, tombol darurat (*SOS Panic Button*), dan jajak pendapat digital (*E-Voting*).
4. **Transparansi Tata Kelola:** Publikasi terbuka APBDes dan progres pembangunan fisik berbasis Sistem Informasi Geografis (GIS).
5. **Aksesibilitas Multikanal (Omnichannel):** Layanan mandiri via WhatsApp Bot dan Telegram Bot.
6. **Kecerdasan Artifisial (SIWIL AI):** Agen interaktif berbasis *Large Language Model* (Google Gemini) yang dilengkapi 12 *native function calling tools* dan filter keamanan privasi sesuai UU No. 27 Tahun 2022 tentang Perlindungan Data Pribadi (UU PDP).

### 1.3 Definisi, Akronim, dan Singkatan
* **SRS:** *Software Requirements Specification* (Dokumen Spesifikasi Kebutuhan Perangkat Lunak).
* **PRD:** *Product Requirement Document* (Dokumen Kebutuhan Produk).
* **RBAC:** *Role-Based Access Control* (Pembatasan hak akses berbasis peran).
* **NIK:** Nomor Induk Kependudukan (16 digit angka identitas penduduk Indonesia).
* **KK:** Kartu Keluarga (16 digit angka identitas keluarga Indonesia).
* **UU PDP:** Undang-Undang No. 27 Tahun 2022 tentang Perlindungan Data Pribadi.
* **LLM:** *Large Language Model* (Model bahasa besar kecerdasan artifisial, contoh: Google Gemini).
* **RAG:** *Retrieval-Augmented Generation* (Teknik augmentasi konteks AI dari basis data pengetahuan lokal).
* **OTP:** *One-Time Password* (Kode sandi sekali pakai untuk otentikasi).
* **GIS:** *Geographic Information System* (Sistem informasi berbasis pemetaan geografis).
* **SKTM / SKU / SKW:** Surat Keterangan Tidak Mampu / Usaha / Waris.

### 1.4 Referensi Normatif
1. **IEEE Std 830-1998:** *IEEE Recommended Practice for Software Requirements Specifications*.
2. **ISO/IEC/IEEE 29148:2018:** *Systems and software engineering — Life cycle processes — Requirements engineering*.
3. **UU Republik Indonesia No. 27 Tahun 2022:** *Tentang Perlindungan Data Pribadi (UU PDP)*.
4. **Panduan Tugas Proyek Akhir Mandiri & Rubrik Penilaian OBE:** *Mata Kuliah Rekayasa Perangkat Lunak (TIKB2503021), STMIK IKMI Cirebon, 2026*.

---

## 2. DESKRIPSI KESELURUHAN (OVERALL DESCRIPTION)

### 2.1 Perspektif Produk
SIWIL AI berposisi sebagai aplikasi web terdistribusi yang berdiri mandiri (*self-contained ecosystem*) namun terhubung dengan beberapa layanan eksternal melalui antarmuka REST API:

```
[ Warga / Pengurus / Publik ]
             │
             ├── Web Browser (Desktop / Mobile) ──┐
             ├── WhatsApp Instant Messaging ──────┼──► [ Reverse Proxy / Nginx ] ──► [ Laravel Core Engine ]
             └── Telegram Messenger ──────────────┘                  │                         │
                                                                     │                         ├── MySQL Relational DB
                                                                     │                         ├── Redis / Local Cache
                                                                     │                         └── Local Public Disk Storage
                                                                     ▼
                                                   [ External Intelligence & APIs ]
                                                             │
                                                             ├── Google Gemini LLM API (Tool Calling)
                                                             ├── OpenAI API Fallback
                                                             ├── QR Code Verification Server
                                                             └── SMTP Mail Gateway
```

### 2.2 Karakteristik & Profil Aktor (User Personas)

Sistem Informasi Wilayah Terpadu (SIWIL) memodelkan **10 Aktor Sistem** yang terbagi menjadi dua klasifikasi arsitektur utama: **6 Aktor Primer (Pengguna Manusia)** dengan hak akses berbasis *Role-Based Access Control* (RBAC), serta **4 Aktor Sekunder (Sistem Pendukung & Gateway Otomasi)** yang bertindak sebagai mesin inferensi kecerdasan buatan dan kanal komunikasi omnichannel terintegrasi.

| ID Aktor | Nama Peran | Kategori Aktor | Deskripsi Karakteristik & Kebutuhan Inti |
| :--- | :--- | :--- | :--- |
| **ACT-01** | **Warga (Citizen)** | Primer (Manusia) | Masyarakat umum domisili RT/RW yang menginginkan kemudahan pengurusan surat nirkertas tanpa antre fisik, pelaporan aduan lingkungan cepat, pemantauan kegiatan musyawarah, dan akses tombol darurat SOS. Mengakses via web mobile, bot WhatsApp, dan Telegram. |
| **ACT-02** | **Pengurus RT (Ketua RT)** | Primer (Manusia) | Relawan pengurus RT yang memiliki profesi utama di luar. Memerlukan meja kerja digital dan sistem notifikasi instan saat ada warga mendaftar, berkas surat yang perlu diverifikasi awal, atau situasi darurat di lingkungannya. |
| **ACT-03** | **Pengurus RW (Ketua RW)** | Primer (Manusia) | Koordinator lingkungan tingkat RW yang memvalidasi persetujuan surat berjenjang antar-RT, mengesahkan izin kegiatan lingkungan, dan memantau agregat demografi serta disparitas antar-RT. |
| **ACT-04** | **Admin Desa / Kelurahan** | Primer (Manusia) | Aparatur sipil kantor desa yang bertanggung jawab penuh terhadap legalitas surat dinas desa (Tanda Tangan Elektronik), penatausahaan APBDes, inventarisasi proyek fisik pembangunan, dan sosialisasi warta resmi desa. |
| **ACT-05** | **Super Admin** | Primer (Manusia) | Administrator teknis yang bertugas mengelola siklus hidup akun pengguna, konfigurasi runtime (kredensial API Key AI, WhatsApp gateway, SMTP), pemeliharaan master wilayah, dan audit logging sistem. |
| **ACT-06** | **Pengunjung Publik** | Primer (Manusia) | Masyarakat luas yang ingin melihat transparansi penyelenggaraan desa (profil wilayah, realisasi APBDes, status pembangunan fisik, dan statistik terbuka tanpa perlu login). |
| **ACT-07** | **SIWIL AI Agent** | Sekunder (Mesin/AI) | Entitas kecerdasan buatan otonom berbasis LLM Google Gemini yang berperan sebagai Customer Service virtual 24/7 untuk menjawab pertanyaan administrasi dan mengeksekusi operasi data melalui function calling berbatas izin (*read-only tools* & guardrails UU PDP). |
| **ACT-08** | **WhatsApp Bot Gateway** | Sekunder (Gateway Eksternal) | Layanan bot percakapan instan WhatsApp multi-device (Baileys / Cloud API) yang bertindak sebagai antarmuka omnichannel warga untuk mengecek status surat, menerima notifikasi darurat SOS, dan mempermudah warga senior yang tidak terbiasa dengan antarmuka web. |
| **ACT-09** | **Telegram Bot Gateway** | Sekunder (Gateway Eksternal) | Kanal bot resmi Telegram (`@cewilBot` / API Gateway) untuk broadcasting pengumuman resmi desa, penerusan peringatan dini bencana/keamanan lingkungan, dan alternatif kanal interaksi dua arah dengan sistem. |
| **ACT-10** | **SMTP Mail System** | Sekunder (Mail Engine) | Layanan mail transfer agent (SMTP) untuk pengiriman surat elektronik resmi, pengiriman tautan verifikasi akun/reset kata sandi, lampiran dokumen PDF ber-TTE ke instansi eksternal, dan notifikasi audit keamanan sistem. |

### 2.3 Batasan Perancangan & Implementasi (Design Constraints)
1. **Bahasa Pemrograman & Framework:** PHP 8.3+ menggunakan framework Laravel 12/13 LTS dengan struktur arsitektur MVC dan *Service Pattern*.
2. **Tipe Data Primary Key:** Wajib menggunakan format UUID v4 (`HasUuids`) di seluruh entitas basis data untuk memitigasi risiko *Insecure Direct Object Reference* (IDOR).
3. **Regulasi Perlindungan Privasi:** Implementasi wajib mematuhi amanat UU No. 27 Tahun 2022 tentang Perlindungan Data Pribadi (UU PDP). NIK, alamat lengkap, dan nomor kontak dilindungi oleh pembatasan akses data (*scopeAccessibleBy*).
4. **Kemandirian Modul AI:** Integrasi AI tidak boleh mengakibatkan ketergantungan fatal (*single point of failure*). Jika API Key AI pihak ketiga mengalami limitasi, aplikasi harus tetap berjalan normal dengan *rule-based smart fallback*.

---

## 3. SPESIFIKASI KEBUTUHAN FUNGSIONAL (USER STORIES & ACCEPTANCE CRITERIA)

### 3.1 FR-01: Autentikasi & Pre-Enrollment NIK Terverifikasi
* **ID Kebutuhan:** `REQ-F-001`
* **User Story:**
  > *Sebagai warga baru, saya ingin melakukan registrasi akun menggunakan NIK dan No. KK saya, sehingga akun saya terverifikasi secara resmi dengan data kependudukan RT.*
* **Kriteria Penerimaan (Acceptance Criteria):**
  * **Skenario 1.1: Registrasi Berhasil (Data Valid & Terdaftar di Database RT)**
    * **Given** Warga memasukkan 16 digit NIK dan No. KK yang telah diinput sebelumnya oleh Ketua RT.
    * **When** Warga mengisi password (min. 6 karakter), nomor HP/WhatsApp, dan menekan tombol "Daftar".
    * **Then** Sistem membuat rekaman user baru dengan role `WARGA` dan status `pending`, lalu menampilkan pesan sukses bahwa akun sedang menunggu verifikasi RT/Admin.
  * **Skenario 1.2: Registrasi Ditolak (NIK Belum Terdaftar)**
    * **Given** Warga memasukkan NIK yang belum tercatat pada basis data kependudukan RT.
    * **When** Warga menekan tombol "Daftar".
    * **Then** Sistem menolak pendaftaran dan memberikan pesan galat: *"NIK tidak ditemukan di data kependudukan. Pastikan RT/RW Anda sudah mendaftarkan NIK Anda."*
  * **Skenario 1.3: Login Akun Menunggu Verifikasi**
    * **Given** Warga memiliki akun berstatus `pending`.
    * **When** Warga mencoba login menggunakan NIK dan password yang benar.
    * **Then** Sistem menolak sesi login dan menampilkan pesan: *"Akun Anda masih menunggu verifikasi oleh RT/Admin."*

### 3.2 FR-02: Pemulihan Akun via OTP WhatsApp
* **ID Kebutuhan:** `REQ-F-002`
* **User Story:**
  > *Sebagai pengguna yang lupa kata sandi, saya ingin menerima kode OTP verifikasi melalui pesan WhatsApp resmi sistem, sehingga saya dapat mereset kata sandi dengan aman tanpa email konvensional.*
* **Kriteria Penerimaan (Acceptance Criteria):**
  * **Skenario 2.1: Permintaan OTP Berhasil**
    * **Given** Pengguna memasukkan NIK, email, atau nomor HP yang terdaftar aktif.
    * **When** Pengguna mengklik tombol "Kirim Kode OTP".
    * **Then** Sistem menggenerasi 6 digit angka acak kriptografis, menyimpannya di Cache terenkripsi selama 10 menit, menyetel *cooldown* 60 detik, dan mengirimkan pesan WhatsApp ke nomor pengguna.
  * **Skenario 2.2: Pembatasan Frekuensi Permintaan (*Rate Limiting*)**
    * **Given** Pengguna telah meminta kode OTP kurang dari 60 detik yang lalu.
    * **When** Pengguna menekan tombol "Kirim Ulang".
    * **Then** Sistem menolak pengiriman dan menampilkan sisa waktu tunggu dalam detik.
  * **Skenario 2.3: Verifikasi OTP Valid & Pembaruan Sandi**
    * **Given** Pengguna memasukkan kode 6-digit yang cocok dengan rekaman Cache.
    * **When** Pengguna memasukkan kata sandi baru dan menekan tombol "Simpan Kata Sandi".
    * **Then** Kata sandi di-hash menggunakan algoritma Bcrypt, OTP di-flush dari Cache, dan pengguna diarahkan ke halaman login dengan pesan sukses.

### 3.3 FR-03: Hierarki Wilayah & Administrasi Kependudukan
* **ID Kebutuhan:** `REQ-F-003`
* **User Story:**
  > *Sebagai pengurus RT/RW/Desa, saya ingin mengelola data struktur wilayah dan kependudukan sesuai yurisdiksi saya, sehingga pencatatan mutasi penduduk (lahir, pindah, wafat) tersinkronisasi rapi.*
* **Kriteria Penerimaan (Acceptance Criteria):**
  * **Skenario 3.1: Hak Akses Data Sesuai Scope Wilayah (RBAC)**
    * **Given** Pengurus RT 01 login ke dalam sistem.
    * **When** Pengurus membuka menu data kependudukan.
    * **Then** Sistem hanya menampilkan daftar warga dan Kartu Keluarga yang berdomisili di RT 01. Warga RT 02 atau RW lain tidak terlihat (*strict data scoping*).
  * **Skenario 3.2: Penambahan Anggota Keluarga Baru**
    * **Given** Pengurus RT memilih data Kartu Keluarga tertentu.
    * **When** Pengurus menginput NIK, nama, tanggal lahir, jenis kelamin, hubungan keluarga, dan foto KTP fisik.
    * **Then** Sistem menyimpan rekaman penduduk baru berelasi dengan `family_id` yang sesuai dan mencatat aktivitas penambahan ke dalam `audit_logs`.

### 3.4 FR-04: Layanan Persuratan Berjenjang & Digital Signature
* **ID Kebutuhan:** `REQ-F-004`
* **User Story:**
  > *Sebagai warga, saya ingin mengajukan surat administrasi (SKTM/SKU/Pengantar) secara online, dan sebagai pengurus saya ingin menyetujui serta membubuhkan tanda tangan digital secara bertingkat.*
* **Kriteria Penerimaan (Acceptance Criteria):**
  * **Skenario 4.1: Pengajuan Surat Tingkat RT**
    * **Given** Warga memilih jenis surat `IZIN_RENOVASI_RUMAH`.
    * **When** Warga mengunggah berkas lampiran dan mengirim pengajuan.
    * **Then** Dokumen dibuat dengan status `DIAJUKAN_RT`, dan sistem mendeteksi level approval final adalah `RT`.
  * **Skenario 4.2: Alur Persetujuan Berjenjang Surat Desa (Contoh: SKTM)**
    * **Given** Dokumen berstatus `DIAJUKAN_RT` dengan jenis `SURAT_KETERANGAN_TIDAK_MAMPU`.
    * **When** Ketua RT menekan "Setujui (Teruskan ke RW)".
    * **Then** Sistem mencatat stempel persetujuan RT (`rt_approved_by_id`), menyematkan tanda tangan RT, mengubah status menjadi `DIAJUKAN_RW`, serta mengirim notifikasi otomatis ke WhatsApp/Telegram Ketua RW.
    * **When** Ketua RW menyetujui, status berubah menjadi `DIAJUKAN_DESA` dan diteruskan ke Kantor Desa.
    * **When** Admin Desa/Kades menekan "Setujui & Terbitkan", status berubah menjadi `DISETUJUI / SELESAI`.

### 3.5 FR-05: Penerbitan Vector PDF & Verifikasi QR Code
* **ID Kebutuhan:** `REQ-F-005`
* **User Story:**
  > *Sebagai pemohon yang suratnya disetujui, saya ingin mengunduh berkas surat dalam format PDF resmi berkop desa lengkap dengan stempel dinas, tanda tangan digital, dan QR Code verifikasi.*
* **Kriteria Penerimaan (Acceptance Criteria):**
  * **Skenario 5.1: Ekspor Dokumen Resmi DomPDF**
    * **Given** Pengajuan surat telah berstatus `DISETUJUI` atau `SELESAI`.
    * **When** Pengguna atau pengurus menekan tombol "Cetak PDF".
    * **Then** Sistem merender template Blade yang sesuai (`formats/desa.blade.php` atau `formats/rt_only.blade.php`), menyematkan nomor registrasi otomatis, stempel transparan, dan gambar TTD resmi.
  * **Skenario 5.2: Pemindaian QR Code Keabsahan**
    * **Given** Dokumen fisik dicetak oleh warga.
    * **When** Pihak ketiga (bank/instansi lain) memindai QR Code pada pojok bawah surat.
    * **Then** QR Code mengarah ke data verifikasi terenkripsi: `VERIFIED_SIWIL_AI|NO:<request_number>|TGL:<date>|STATUS:DISETUJUI` yang membuktikan keaslian dokumen tanpa rekayasa.

### 3.6 FR-06: Pengaduan Warga Berbasis Spasial (GIS)
* **ID Kebutuhan:** `REQ-F-006`
* **User Story:**
  > *Sebagai warga, saya ingin menyampaikan laporan keluhan kerusakan fasilitas lingkungan lengkap dengan foto dan titik lokasi peta GPS, sehingga pengurus dapat meninjau ke lokasi secara tepat.*
* **Kriteria Penerimaan (Acceptance Criteria):**
  * **Skenario 6.1: Pelaporan Aduan dengan Geotagging**
    * **Given** Warga berada di lokasi lampu jalan rusak.
    * **When** Warga membuka form aduan, mengizinkan akses GPS peramban (mendapatkan `location_lat` dan `location_lng`), mengunggah foto bukti, dan mengirimkan aduan.
    * **Then** Sistem menyimpan aduan dengan status `PENDING`, mengaitkannya ke wilayah RT/RW pelapor, dan menampilkannya sebagai pin koordinat di Peta Spasial Dashboard Desa.
  * **Skenario 6.2: Pembaruan Status Penanganan**
    * **Given** Pengurus RT/Desa meninjau aduan.
    * **When** Pengurus mengubah status menjadi `PROCESSING` lalu `RESOLVED`.
    * **Then** Sistem memperbarui status aduan dan mengirimkan pemberitahuan progres kepada warga pelapor.

### 3.7 FR-07: Tombol Darurat Satu Ketukan (SOS Panic Button)
* **ID Kebutuhan:** `REQ-F-007`
* **User Story:**
  > *Sebagai warga yang menghadapi bahaya (medis darurat, kebakaran, kriminalitas), saya ingin menekan tombol SOS merah di dashboard/bot, sehingga pengurus wilayah terdekat segera mendapat sinyal bahaya berkoordinat.*
* **Kriteria Penerimaan (Acceptance Criteria):**
  * **Skenario 7.1: Pemicuan Alarm Darurat Aktif**
    * **Given** Warga membuka aplikasi dalam kondisi krisis.
    * **When** Warga menekan tombol "Kirim Panggilan Darurat" dan memilih tipe insiden (`Medis`, `Kebakaran`, `Kriminal`).
    * **Then** Sistem mencatat rekaman `emergencies` berstatus `ACTIVE` lengkap dengan koordinat GPS real-time.
  * **Skenario 7.2: Penyiaran Alarm Audio-Visual**
    * **Given** Terdapat sinyal SOS darurat berstatus `ACTIVE`.
    * **When** Pengurus RT, RW, atau Admin Desa membuka dashboard.
    * **Then** Sistem memunculkan banner peringatan darurat berwarna merah mencolok disertai koordinat lokasi dan identitas pelapor.
  * **Skenario 7.3: Penyelesaian Insiden Darurat**
    * **Given** Penanganan lapangan telah diselesaikan oleh satgas/pengurus.
    * **When** Pengurus menekan tombol "Selesaikan Darurat".
    * **Then** Status darurat berubah menjadi `RESOLVED` dan banner peringatan otomatis hilang.

### 3.8 FR-08: Musyawarah Digital & Polling Warga (E-Voting Scoped)
* **ID Kebutuhan:** `REQ-F-008`
* **User Story:**
  > *Sebagai pengurus, saya ingin mengadakan musyawarah lingkungan berbasis e-voting berbatas wilayah, dan sebagai warga saya ingin memberikan suara secara rahasia dan sah.*
* **Kriteria Penerimaan (Acceptance Criteria):**
  * **Skenario 8.1: Pembuatan Polling Berskala RT**
    * **Given** Ketua RT 02 membuat polling jadwal kerja bakti.
    * **When** Polling disimpan dengan `rt_id` RT 02 dan batas kedaluwarsa waktu.
    * **Then** Polling hanya muncul pada daftar musyawarah warga RT 02. Warga RT 01 tidak dapat melihat maupun memilih.
  * **Skenario 8.2: Pencegahan Pemilihan Ganda (One Citizen One Vote)**
    * **Given** Warga telah memberikan 1 suara pada opsi pilihan polling.
    * **When** Warga mencoba mengklik dan memilih opsi lain kembali.
    * **Then** Sistem menolak dan menampilkan status bahwa hak suara pengguna telah tercatat.
  * **Skenario 8.3: Otomasi Penentuan Pemenang**
    * **Given** Masa aktif polling telah mencapai `expires_at`.
    * **When** Polling ditutup (`CLOSED`).
    * **Then** Sistem menghitung persentase suara terbesar secara matematis, menetapkan `winning_option`, dan menyiarkan hasil rekapitulasi ke WhatsApp warga.

### 3.9 FR-09: Transparansi Anggaran Desa (APBDes Digital)
* **ID Kebutuhan:** `REQ-F-009`
* **User Story:**
  > *Sebagai masyarakat publik atau warga, saya ingin melihat rincian APBDes per tahun anggaran beserta perbandingan realisasinya, sehingga tata kelola keuangan desa transparan dan akuntabel.*
* **Kriteria Penerimaan (Acceptance Criteria):**
  * **Skenario 9.1: Inspeksi APBDes Terbuka (Tanpa Login)**
    * **Given** Pengunjung mengakses rute `/portal/apbdes`.
    * **When** Pengunjung memilih filter tahun anggaran (misal: 2026).
    * **Then** Sistem menyajikan ringkasan 3 pilar: Total Pendapatan, Belanja, dan Pembiayaan lengkap dengan grafik visual perbandingan Anggaran vs Realisasi.

### 3.10 FR-10: Monitoring Proyek Pembangunan Fisik Berbasis GIS
* **ID Kebutuhan:** `REQ-F-010`
* **User Story:**
  > *Sebagai perangkat desa, saya ingin mencatat progres proyek pembangunan desa, dan sebagai warga saya ingin memantau persebaran proyek fisik tersebut di peta geografis.*
* **Kriteria Penerimaan (Acceptance Criteria):**
  * **Skenario 10.1: Pemetaan Proyek Infrastruktur**
    * **Given** Admin Desa menginput proyek baru (Judul, Pagu Anggaran, Sumber Dana, Foto, Titik Koordinat GPS, dan % Progres).
    * **When** Warga atau publik membuka menu Pembangunan (`/portal/pembangunan`).
    * **Then** Sistem menampilkan kartu proyek beserta titik pin interaktif di peta spasial yang menunjukkan lokasi riil pekerjaan infrastruktur.

### 3.11 FR-11: Portal Informasi Publik & Pengumuman Warga
* **ID Kebutuhan:** `REQ-F-011`
* **User Story:**
  > *Sebagai aparatur desa, saya ingin mempublikasikan pengumuman warta desa yang dapat dibaca di portal web dan disalurkan ke kanal pesan warga.*
* **Kriteria Penerimaan (Acceptance Criteria):**
  * **Skenario 11.1: Publikasi Warta Resmi**
    * **Given** Admin Desa menyusun pengumuman dengan judul, isi warta, dan mengaktifkan status publikasi.
    * **When** Pengumuman disimpan.
    * **Then** Pengumuman tampil di beranda utama portal publik, dashboard warga, serta dapat ditarik informasinya oleh AI saat ditanyakan oleh warga.

### 3.12 FR-12: Layanan Omnichannel Bot (WhatsApp & Telegram)
* **ID Kebutuhan:** `REQ-F-012`
* **User Story:**
  > *Sebagai warga yang lebih sering memakai WhatsApp atau Telegram, saya ingin mengajukan surat, melapor aduan, dan mengunduh berkas PDF surat langsung dari ruang percakapan obrolan.*
* **Kriteria Penerimaan (Acceptance Criteria):**
  * **Skenario 12.1: Identifikasi Otomatis Pengguna WhatsApp**
    * **Given** Warga mengirim pesan obrolan ke nomor bot WhatsApp resmi.
    * **When** Endpoint webhook `/api/whatsapp/check-user` menerima nomor telepon pengirim.
    * **Then** Sistem mengenali profil warga tanpa perlu login manual berulang, menyajikan menu interaktif layanan surat, aduan, dan polling.
  * **Skenario 12.2: Pengiriman Berkas PDF Surat via Bot**
    * **Given** Pengajuan surat warga telah disetujui penuh oleh Kepala Desa.
    * **When** Sistem menyelesaikan proses pembuatan berkas PDF.
    * **Then** Microservice bot WhatsApp dan Telegram secara otomatis mengirimkan pesan konfirmasi beserta lampiran dokumen PDF resmi ke nomor ponsel pemohon.

---

## 4. SPESIFIKASI FITUR KECERDASAN BUATAN UTAMA (AI SPECIFICATION)

### 4.1 Arsitektur Agen Cerdas SIWIL AI
Fitur AI pada SIWIL dirancang menggunakan pola **Autonomous Tool-Calling Agent** yang menggabungkan kemampuan pemahaman bahasa alami dari model fondasi LLM (Google Gemini) dengan kendali deterministik atas basis data relasional sistem:

```
[ Pesan Pengguna (Teks Input) ]
             │
             ▼
[ 1. Deterministic Security Guardrails (PHP Layer) ]
  ├── Cek Apakah Warga Meminta NIK / Data Pribadi Orang Lain? ──► [ YA: Langsung Tolak (UU PDP) ]
  └── TIDAK (Lolos Filter Keamanan)
             │
             ▼
[ 2. Dynamic Context & Prompt Assembler ]
  ├── Injeksi Identitas Sesi: Nama, NIK, Peran (WARGA/RT/RW/DESA), Alamat Resmi
  ├── Injeksi Dokumen RAG (ai_knowledge: SOP Surat, Peraturan Wilayah)
  └── Deklarasi Schema Tools (12 Native Function Calling Declarations)
             │
             ▼
[ 3. LLM Inference Engine (Google Gemini 2.5 Flash) ]
  ├── Menghasilkan Jawaban Teks Langsung, ATAU
  └── Memutuskan Memanggil Tool Database (Function Calling)
             │
             ▼
[ 4. Backend Tool Executor (RBAC Bounded) ]
  ├── Menjalankan Handler PHP Sesuai Scope Identitas Auth Pengguna
  └── Mengembalikan Data JSON Terverifikasi ke Model LLM
             │
             ▼
[ 5. Final Grounded Response Generator ]
  └── Jawaban Ramah, Akurat, Tanpa Halusinasi + Opsi Tombol Aksi Cepat
```

### 4.2 Dynamic System Prompt & Session Context Injection
Setiap interaksi obrolan menginjeksikan *system prompt* dinamis yang dikonstruksi secara programatik oleh `AiToolService::buildDynamicSystemPrompt(User $user)`:
- **Identitas Terotentikasi:** Menyematkan Nama, Username/NIK, Peran resmi, dan Nomor RT/RW pengguna aktif.
- **Batasan Kewenangan Hukum:** Menguraikan instruksi tegas bahwa pengguna hanya boleh disajikan data yang sesuai dengan hak akses perannya.
- **Pedoman Anti-Halusinasi:** AI dilarang keras mengarang data kependudukan atau status surat; AI wajib memanggil fungsi *tool calling* jika data faktual tersedia di database.

### 4.3 Deterministic UU PDP Guardrails (Anti-Scraping Data Pribadi)
Sebelum pesan diteruskan ke model AI, layer kontroler mengevaluasi masukan dengan aturan deterministik:
- Jika pengguna ber-role `WARGA` mengajukan kueri yang mengandung indikasi pencarian informasi warga lain (contoh pola regex: nomor telepon orang lain, NIK tetangga, alamat warga lain di luar KK):
  * **Respons Instan:** *"Permintaan ditolak. Demi kepatuhan Undang-Undang Perlindungan Data Pribadi (UU PDP No. 27/2022), Anda hanya berhak mengakses data pribadi dan data keluarga dalam Kartu Keluarga Anda sendiri."*
  * Request tidak dikirim ke API Gemini, menghemat kuota token dan menutup celah manipulasi *jailbreak prompt*.

### 4.4 12 Native Tool Function Declarations

| Nama Function Tool | Deskripsi Fungsionalitas | Parameter Masukan | Batasan Akses Role (RBAC) |
| :--- | :--- | :--- | :--- |
| `fetch_dashboard_data` | Mengambil ringkasan metrik statistik dashboard yang difilter per wilayah. | `category`: `all`, `residents`, `documents`, `complaints`, `announcements`. | Seluruh Role (Data tersaring otomatis sesuai scope) |
| `fetch_residents_and_areas` | Mengambil data kependudukan dan struktur RT/RW. | `search`, `rt_number`, `rw_number`, `limit`. | Khusus `RT`, `RW`, `ADMIN_DESA`, `SUPER_ADMIN` (Warga diblokir total) |
| `fetch_announcements` | Mengambil warta pengumuman resmi desa yang sedang aktif. | `search`, `limit`. | Seluruh Role |
| `fetch_document_requests` | Memeriksa permohonan surat administrasi warga (SKTM, SKU, Domisili). | `status`, `type`, `limit`. | Warga (surat milik sendiri), Pengurus (surat yang perlu diverifikasi) |
| `fetch_complaints` | Memeriksa riwayat pengaduan dan status penyelesaian masalah warga. | `status`, `limit`. | Warga (aduan sendiri), Pengurus (aduan wilayah binaan) |
| `fetch_polls` | Mengambil daftar musyawarah dan polling yang sedang aktif beserta opsi voting. | `status`, `limit`. | Seluruh Role (Sesuai yurisdiksi polling) |
| `fetch_family_data` | Menampilkan susunan anggota keluarga resmi dalam Kartu Keluarga (KK). | `kk_number` (opsional). | Warga (otomatis mengunci KK miliknya), Pengurus (binaan) |
| `fetch_development_and_budgets` | Memeriksa rincian anggaran APBDes dan progres proyek pembangunan fisik. | `type`, `fiscal_year`, `limit`. | Pengurus RT, RW, Desa, Super Admin |
| `fetch_audit_logs` | Menginspeksi riwayat audit jejak aktivitas sistem. | `action`, `limit`. | Khusus Pengurus dan Super Admin (Warga diblokir) |
| `fetch_users_directory` | Mengakses daftar akun pengguna terdaftar di sistem. | `role_filter`, `search`, `limit`. | Khusus Admin Desa & Super Admin |
| `fetch_ai_knowledge` | Mengambil basis pengetahuan resmi SOP persuratan dan aturan desa. | `topic`. | Seluruh Role |
| `fetch_current_user_profile` | Menampilkan biodata profil lengkap pengguna yang sedang login. | *(tanpa parameter)* | Seluruh Role (Mengembalikan data sesi login) |

### 4.5 Mekanisme Multi-Key API Rotation & Smart Fallback
- **Rotasi Kunci Mandiri:** Sistem mendukung penyimpanan beberapa API Key Google Gemini dalam tabel `system_configurations`. Jika satu kunci terkena *rate limit* (HTTP 429), sistem otomatis memindahkan panggilan ke kunci cadangan berikutnya.
- **Smart Fallback Tanpa AI:** Jika seluruh koneksi AI pihak ketiga terputus, sistem secara elegan beralih ke *rule-based keyword matcher* yang menyajikan tombol navigasi cepat (Kembali ke Beranda, Cek Surat, Buat Pengaduan) sehingga pengguna tidak mengalami layar *error blank*.

---

## 5. SPESIFIKASI KEBUTUHAN NON-FUNGSIONAL (NFR)

### 5.1 Performance & Response Latency
* **NFR-P1 (Kecepatan Muat Web):** Halaman antarmuka web harus berhasil dirender dalam waktu kurang dari $1.5\text{ detik}$ pada koneksi 4G standar.
* **NFR-P2 (Respon Bot Pesan Instan):** Respons otomatis bot WhatsApp dan Telegram untuk kueri berbasis menu tidak boleh melebihi $2\text{ detik}$.
* **NFR-P3 (Waktu Eksekusi AI Tool):** Keseluruhan siklus obrolan AI (Prompting $\to$ Function Calling $\to$ Database Execution $\to$ Final Text) ditargetkan tuntas dalam waktu $\le 4\text{ detik}$.
* **NFR-P4 (Ukuran Berkas PDF):** Berkas dokumen surat yang digenerasi oleh DomPDF harus memiliki ukuran efisien ($< 200\text{ KB}$) agar ramah kuota data seluler.

### 5.2 Security & Cryptography
* **NFR-S1 (Otentikasi & Hashing):** Seluruh kata sandi pengguna wajib dienkripsi searah menggunakan algoritma Bcrypt dengan *work factor* standar Laravel. Tidak ada teks sandi mentah yang disimpan di basis data.
* **NFR-S2 (Insecure Direct Object Reference Mitigation):** Seluruh entitas tabel menggunakan UUID v4 acak 36-karakter sebagai primary key untuk menggagalkan upaya manipulasi enumerasi ID pada URL.
* **NFR-S3 (Proteksi Form & Akses API):** Semua form web wajib menerapkan token proteksi *Cross-Site Request Forgery* (CSRF). Komunikasi API bot dilindungi oleh autentikasi bearer token/LID validation.
* **NFR-S4 (Penyembunyian Kredensial Sensitif):** Kredensial sistem (API Keys, Token Telegram, Password SMTP) wajib disamarkan (*masked*) pada antarmuka admin.

### 5.3 Reliability & High Availability
* **NFR-R1 (Ketersediaan Sistem):** Ketersediaan layanan web ditargetkan mencapai $99.5\%$ *uptime* pada jam operasional pelayanan warga.
* **NFR-R2 (Integritas Data Transaksional):** Mutasi data kependudukan dan persuratan berjenjang harus dibungkus dalam mekanisme `DB::transaction` untuk mencegah anomali inkonsistensi data jika terjadi kegagalan jaringan di tengah proses.
* **NFR-R3 (Pencatatan Audit Trail):** Setiap operasi manipulasi data esensial wajib dicatat ke tabel `audit_logs` meliputi ID pengguna, aksi, IP address, serta nilai data sebelum dan sesudah perubahan.

### 5.4 Usability & Mobile-First Accessibility
* **NFR-U1 (Desain Responsif):** Seluruh antarmuka portal publik dan dashboard warga harus tampil proporsional pada resolusi layar mulai dari 360px (smartphone) hingga 1920px (desktop).
* **NFR-U2 (Target Sentuh Ramah Ponsel):** Komponen tombol utama, navigasi menu, dan pemicu SOS memiliki area sentuh minimal $44 \times 44\text{ piksel}$ sesuai panduan aksesibilitas WCAG 2.1 AA.
* **NFR-U3 (Kemudahan Alur Surat):** Pengajuan surat mandiri oleh warga harus dapat diselesaikan dalam maksimal 3 langkah interaksi formulir.

### 5.5 Maintainability & Clean Architecture
* **NFR-M1 (Separation of Concerns):** Pemisahan tegas antara routing (`routes/web.php` dan `routes/api.php`), penanganan HTTP (`Controllers`), aturan bisnis (`Services`), dan lapisan persistensi (`Models`).
* **NFR-M2 (Standar Kualitas Kode):** Penulisan kode PHP mematuhi PSR-12 dan diverifikasi otomatis menggunakan Laravel Pint (`vendor/bin/pint`).

---

## 6. MATRIKS KETERTELUSURAN KEBUTUHAN (TRACEABILITY MATRIX)

| ID Kebutuhan | Nama Fungsionalitas | Aktor Terkait | Use Case ID | Implementasi Arsitektur | Validasi Pengujian |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **REQ-F-001** | Registrasi & Login NIK | Warga, RT, Super Admin | UC-01, UC-02 | `AuthController`, `RegisterWargaRequest` | Unit & Feature Test |
| **REQ-F-002** | Reset Password OTP | Seluruh Pengguna | UC-03 | `ForgotPasswordController`, `WhatsAppService` | Mock Service Test |
| **REQ-F-003** | Manajemen Wilayah & Data Warga | RT, RW, Desa, Super Admin | UC-04, UC-05 | `AreaController`, `ResidentController` | Scope Query Test |
| **REQ-F-004** | Persuratan Berjenjang & TTD | Warga, RT, RW, Desa | UC-06 | `DocumentRequestController`, `DocumentRequestService` | Workflow Transition Test |
| **REQ-F-005** | Unduh PDF & Verifikasi QR | Warga, Publik, Pengurus | UC-07 | `DomPDF Facade`, `formats/desa.blade.php` | PDF Export Test |
| **REQ-F-006** | Pengaduan Warga Spasial | Warga, RT, RW, Desa | UC-08 | `ComplaintController`, Leaflet/GIS Module | Coordinate Parsing Test |
| **REQ-F-007** | Panggilan Darurat (SOS) | Warga, RT, Desa | UC-09 | `EmergencyController`, Dashboard Banner | Event Broadcasting Test |
| **REQ-F-008** | Musyawarah & E-Voting | Warga, RT, RW, Desa | UC-10 | `PollController`, `PollVote Model` | Anti-Duplicate Vote Test |
| **REQ-F-009** | Transparansi APBDes | Pengunjung Publik, Desa | UC-11 | `PublicController`, `BudgetController` | Public Route Test |
| **REQ-F-010** | Monitoring Proyek Pembangunan | Pengunjung Publik, Desa | UC-12 | `DevelopmentController`, Peta Spasial | Geolocation Rendering Test |
| **REQ-F-011** | Publikasi Informasi Publik | Admin Desa, Publik | UC-13 | `AnnouncementController`, `PublicController` | Publication Status Test |
| **REQ-F-012** | Layanan Bot WA & Telegram | Warga, Pengurus | UC-14, UC-15 | `WhatsAppController`, `TelegramService` | Webhook Simulator Test |
| **REQ-F-013** | Asisten SIWIL AI Function Calling | Seluruh Pengguna Terdaftar | UC-16 | `AiController`, `AiToolService`, Gemini API | Tool Calling Scope Test |

---

## 7. PENGESAHAN DOKUMEN (SIGN-OFF)

Dokumen Spesifikasi Kebutuhan Perangkat Lunak (SRS/PRD) ini telah disesuaikan secara komprehensif dengan rubrik evaluasi Proyek Akhir Mandiri Rekayasa Perangkat Lunak berbasis Kurikulum Outcome-Based Education (OBE).

*Ditetapkan di: Cirebon*  
*Tanggal: 12 November 2026*  
*Pengembang Mandiri (Full-Stack Software Engineer): Mahasiswa S1 Teknik Informatika*
