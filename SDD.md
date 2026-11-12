# SYSTEM DESIGN DOCUMENT (SDD)
## SIWIL AI: Sistem Informasi Wilayah Terpadu Berbasis Kecerdasan Buatan
### Pemodelan Arsitektur Perangkat Lunak, UML Berorientasi Objek (SOLID), ERD & Perancangan UI/UX

---

| Informasi Dokumen | Rincian Desain Sistem |
| :--- | :--- |
| **Nama Proyek** | **SIWIL AI** (*Sistem Informasi Wilayah Terpadu*) |
| **Nomor Dokumen** | SDD-SIWIL-2026-V1.0 |
| **Mata Kuliah** | Rekayasa Perangkat Lunak (TIKB2503021) — Semester 3 |
| **Institusi** | Program Studi S1 Teknik Informatika, STMIK IKMI Cirebon |
| **Tahapan SDLC** | Tahap 2: Perancangan UML & Arsitektur Sistem Mandiri (SDD) |
| **Status Dokumen** | *Approved Architecture & Design Baseline* |
| **Versi Dokumen** | 1.0 (Comprehensive UML, SOLID, ERD & UI/UX Edition) |

---

## DAFTAR ISI

1. [PENDAHULUAN & PRINSIP ARSITEKTUR](#1-pendahuluan--prinsip-arsitektur)
   - 1.1 [Tujuan Dokumen](#11-tujuan-dokumen)
   - 1.2 [Penerapan Prinsip Clean Architecture & SOLID](#12-penerapan-prinsip-clean-architecture--solid)
   - 1.3 [Gaya Arsitektur Perangkat Lunak (Layered MVC with Services)](#13-gaya-arsitektur-perangkat-lunak-layered-mvc-with-services)
2. [DIAGRAM UML 1: USE CASE DIAGRAM & SKENARIO LENGKAP](#2-diagram-uml-1-use-case-diagram--skenario-lengkap)
   - 2.1 [Deskripsi Use Case Model](#21-deskripsi-use-case-model)
   - 2.2 [Visualisasi Diagram Use Case](#22-visualisasi-diagram-use-case)
   - 2.3 [Tabel Skenario Use Case Rinci (Core Use Cases)](#23-tabel-skenario-use-case-rinci-core-use-cases)
3. [DIAGRAM UML 2: ACTIVITY DIAGRAM ALUR BISNIS & AI](#3-diagram-uml-2-activity-diagram-alur-bisnis--ai)
   - 3.1 [Activity Diagram Alur Bisnis: Pengajuan & Pengesahan Surat Berjenjang](#31-activity-diagram-alur-bisnis-pengajuan--pengesahan-surat-berjenjang)
   - 3.2 [Activity Diagram Alur AI: Inferensi Cerdas & Eksekusi Function Calling](#32-activity-diagram-alur-ai-inferensi-cerdas--eksekusi-function-calling)
   - 3.3 [Activity Diagram: Panggilan Darurat (SOS Panic Button)](#33-activity-diagram-panggilan-darurat-sos-panic-button)
4. [DIAGRAM UML 3: CLASS DIAGRAM BERORIENTASI OBJEK (SOLID PRINCIPLES)](#4-diagram-uml-3-class-diagram-berorientasi-objek-solid-principles)
   - 4.1 [Penerapan Prinsip SOLID pada Struktur Kelas](#41-penerapan-prinsip-solid-pada-struktur-kelas)
   - 4.2 [Visualisasi Class Diagram Lengkap](#42-visualisasi-class-diagram-lengkap)
   - 4.3 [Daftar Kelas, Atribut, Metode & Relasi Antar-Kelas](#43-daftar-kelas-atribut-metode--relasi-antar-kelas)
5. [DIAGRAM UML 4: SEQUENCE DIAGRAM PERTUKARAN PESAN](#5-diagram-uml-4-sequence-diagram-pertukaran-pesan)
   - 5.1 [Sequence Diagram 1: Alur Layanan Persuratan Berjenjang & Digital Stamp](#51-sequence-diagram-1-alur-layanan-persuratan-berjenjang--digital-stamp)
   - 5.2 [Sequence Diagram 2: Alur Pemrosesan AI Chat & Function Calling Backend](#52-sequence-diagram-2-alur-pemrosesan-ai-chat--function-calling-backend)
   - 5.3 [Sequence Diagram 3: Alur Pemulihan Sandi via WhatsApp OTP](#53-sequence-diagram-3-alur-pemulihan-sandi-via-whatsapp-otp)
6. [DESAIN BASIS DATA RELASIONAL (ENTITY RELATIONSHIP DIAGRAM / ERD)](#6-desain-basis-data-relasional-entity-relationship-diagram--erd)
   - 6.1 [Visualisasi ERD Tingkat Konseptual & Fisik](#61-visualisasi-erd-tingkat-konseptual--fisik)
   - 6.2 [Kamus Data Rinci (Data Dictionary)](#62-kamus-data-rinci-data-dictionary)
7. [PERANCANGAN PROTOTIPE INTERAKTIF UI/UX FIGMA](#7-perancangan-prototipe-interaktif-uiux-figma)
   - 7.1 [Design System & Panduan Gaya (Typography, Color Palette, Grid)](#71-design-system--panduan-gaya-typography-color-palette-grid)
   - 7.2 [Struktur Hierarki Informasi (Information Architecture)](#72-struktur-hierarki-informasi-information-architecture)
   - 7.3 [Spesifikasi Wireframe Antarmuka Kunci](#73-spesifikasi-wireframe-antarmuka-kunci)
   - 7.4 [Panduan Walkthrough Prototipe untuk Presentasi Video 2](#74-panduan-walkthrough-prototipe-untuk-presentasi-video-2)
8. [BERKAS DIAGRAM DRAW.IO XML](#8-berkas-diagram-drawio-xml)

---

## 1. PENDAHULUAN & PRINSIP ARSITEKTUR

### 1.1 Tujuan Dokumen
Dokumen *System Design Document* (SDD) ini menyajikan spesifikasi arsitektur teknis menyeluruh dari aplikasi **SIWIL AI**. Dokumen ini mentranslasikan spesifikasi kebutuhan pada SRS/PRD ke dalam pemodelan visual berstandar UML 2.5, perancangan basis data relasional ternormalisasi (3NF), serta cetak biru antarmuka pengguna (UI/UX) untuk memenuhi kriteria penilaian mutu luaran rekayasa perangkat lunak.

### 1.2 Penerapan Prinsip Clean Architecture & SOLID
Struktur sistem SIWIL AI dibangun dengan kepatuhan ketat terhadap prinsip **SOLID**:
1. **Single Responsibility Principle (SRP):** Setiap controller dan service hanya memiliki satu tanggung jawab spesifik. Contoh: `DocumentRequestService` mengelola logika persuratan murni, sementara `TelegramService` dan `WhatsAppService` hanya mengurusi protokol komunikasi pengiriman pesan.
2. **Open/Closed Principle (OCP):** Fitur verifikasi dokumen dan pengiriman notifikasi terbuka untuk penambahan kanal baru tanpa memodifikasi basis kode inti persuratan.
3. **Liskov Substitution Principle (LSP):** Model entitas kependudukan dan pengguna (`User`, `Resident`, `Family`) mengimplementasikan kontrak relasi Eloquent standar yang dapat dipertukarkan dalam antarmuka query scopes (`scopeAccessibleBy`).
4. **Interface Segregation Principle (ISP):** Kontrak fungsi bot memisahkan antarmuka perpesanan teks sederhana dengan pengiriman dokumen biner (PDF).
5. **Dependency Inversion Principle (DIP):** Modul tingkat tinggi (Controller) bergantung pada abstraksi layanan (`Service Layer`) melalui *Dependency Injection* pada konstruktor, bukan instansiasi objek langsung.

### 1.3 Gaya Arsitektur Perangkat Lunak (Layered MVC with Services)

```
┌────────────────────────────────────────────────────────────────────────┐
│                   PRESENTATION LAYER (Blade + Tailwind v4)              │
│  - Public Portal Views       - Citizen Dashboard (Mobile-First)        │
│  - Admin/RT/RW Dashboard     - Interactive Modals & Canvas GIS Map     │
└───────────────────────────────────┬────────────────────────────────────┘
                                    │ HTTP / JSON Requests
┌───────────────────────────────────▼────────────────────────────────────┐
│                    HTTP & ROUTING LAYER (Laravel Web/API)              │
│  - Authenticate Middleware   - ProfileComplete Middleware              │
│  - RolePermission Middleware - FormRequest Validators (CSRF + Regex)   │
└───────────────────────────────────┬────────────────────────────────────┘
                                    │ Validated DTO / Request
┌───────────────────────────────────▼────────────────────────────────────┐
│                      APPLICATION CONTROLLERS LAYER                      │
│  - AuthController            - DocumentRequestController               │
│  - AiController              - SpatialAnalyticsController              │
│  - EmergencyController       - WhatsAppController / TelegramController │
└───────────────────────────────────┬────────────────────────────────────┘
                                    │ Service Invocation
┌───────────────────────────────────▼────────────────────────────────────┐
│                  DOMAIN BUSINESS LOGIC & SERVICE LAYER                  │
│  - AiToolService (Prompt + Guardrail + Tool Dispatcher)                 │
│  - DocumentRequestService (Multi-Tier Approval Engine)                  │
│  - WhatsAppService & TelegramService (Omnichannel Message Gateway)      │
│  - ResidentService & EmailNotificationService                           │
└───────────────────────────────────┬────────────────────────────────────┘
                                    │ Eloquent ORM Operations
┌───────────────────────────────────▼────────────────────────────────────┐
│                   PERSISTENCE & DATA ACCESS LAYER                      │
│  - Eloquent Models (20 Domain Entities with HasUuids)                  │
│  - Scopes: scopeAccessibleBy (Strict RBAC & UU PDP Enforcement)        │
│  - Database (MySQL / MariaDB / SQLite) + File Storage (Disk Public)    │
└────────────────────────────────────────────────────────────────────────┘
```

---

## 2. DIAGRAM UML 1: USE CASE DIAGRAM & SKENARIO LENGKAP

### 2.1 Deskripsi Use Case Model
Use Case model SIWIL AI membagi fungsionalitas sistem ke dalam 4 subsistem utama:
1. **Subsistem Kependudukan & Wilayah:** Melayani pendaftaran akun, validasi data NIK, struktur KK, dan pengelolaan RT/RW.
2. **Subsistem Pelayanan Warga (Civic Services):** Melayani persuratan berjenjang, pelaporan aduan berbasis lokasi, dan sinyal darurat SOS.
3. **Subsistem E-Demokrasi & Transparansi:** Melayani pemungutan suara musyawarah, portal APBDes, dan monitoring pembangunan fisik.
4. **Subsistem Kecerdasan Buatan & Omnichannel:** Melayani percakapan cerdas SIWIL AI, function calling, dan interaksi bot pesan instan.

### 2.2 Visualisasi Diagram Use Case

> [!TIP]
> File XML Draw.io resmi untuk diagram ini tersedia di [usecase_diagram.drawio](file:///c:/Users/alfar/Documents/laragon/www/siwil/usecase_diagram.drawio) atau workbook gabungan [siwil_architecture_all.drawio](file:///c:/Users/alfar/Documents/laragon/www/siwil/siwil_architecture_all.drawio) dengan pemisahan tegas: **Aktor Manusia di Kiri** dan **Aktor Pendukung / Eksternal di Kanan**.

```mermaid
graph LR
    subgraph Actors_Left ["👤 Aktor Manusia (Sisi Kiri)"]
        Warga["👤 Warga (Citizen)"]
        RT["👤 Pengurus / Ketua RT"]
        RW["👤 Pengurus / Ketua RW"]
        Desa["👤 Admin Desa / Kades"]
        SA["👤 Super Admin"]
        Publik["👥 Pengunjung Publik"]
    end

    subgraph SIWIL_System ["🏛️ Boundary: SIWIL AI Platform"]
        UC01(["UC-01: Registrasi & Login NIK"])
        UC02(["UC-02: Reset Sandi via OTP WA"])
        UC03(["UC-03: Kelola Data Warga & KK"])
        UC04(["UC-04: Ajukan Surat Administrasi"])
        UC05(["UC-05: Verifikasi Dokumen Berjenjang"])
        UC06(["UC-06: Terbitkan PDF & QR Code"])
        UC07(["UC-07: Laporkan Aduan Lingkungan GIS"])
        UC08(["UC-08: Pemicuan Tombol Darurat SOS"])
        UC09(["UC-09: Ikuti Polling Musyawarah"])
        UC10(["UC-10: Pantau APBDes & Proyek"])
        UC11(["UC-11: Konsultasi SIWIL AI Chat"])
        UC12(["UC-12: Layanan Bot WhatsApp/Telegram"])
        UC13(["UC-13: Kelola Master Desa & APBDes"])
        UC14(["UC-14: Konfigurasi Sistem & Audit Log"])
    end

    subgraph Actors_Right ["🤖 Aktor Pendukung / Sistem Eksternal (Sisi Kanan)"]
        AI["🧠 Google Gemini AI Engine"]
        WA["💬 WhatsApp Bot Gateway (Baileys)"]
        TG["✈️ Telegram Bot Gateway (cevltBot)"]
        PDF["📄 DomPDF & QR Generator"]
        SMTP["📧 SMTP Mail Server"]
    end

    Warga --> UC01
    Warga --> UC02
    Warga --> UC04
    Warga --> UC07
    Warga --> UC08
    Warga --> UC09
    Warga --> UC10
    Warga --> UC11
    Warga --> UC12

    RT --> UC01
    RT --> UC03
    RT --> UC05
    RT --> UC07
    RT --> UC08
    RT --> UC09
    RT --> UC11

    RW --> UC01
    RW --> UC03
    RW --> UC05
    RW --> UC07
    RW --> UC09
    RW --> UC11

    Desa --> UC01
    Desa --> UC03
    Desa --> UC05
    Desa --> UC06
    Desa --> UC10
    Desa --> UC13
    Desa --> UC11

    SA --> UC01
    SA --> UC13
    SA --> UC14

    Publik --> UC10
    Publik -.->|Informasi Terbuka| UC13

    UC11 <-->|«invoke LLM»| AI
    UC12 <-->|«webhook webhook»| WA
    UC12 <-->|«bot polling»| TG
    UC06 -.->|«render vector»| PDF
    UC02 -.->|«send token»| SMTP
    UC04 -.->|«include»| UC05
    UC05 -.->|«include»| UC06
```

### 2.3 Tabel Skenario Use Case Rinci (Core Use Cases)

#### Skenario Use Case UC-04 & UC-05: Pengajuan & Pengesahan Surat Berjenjang
* **Kode Use Case:** `UC-SRT-01`
* **Nama Use Case:** Pengajuan dan Verifikasi Persuratan Berjenjang
* **Aktor Utama:** Warga, Ketua RT, Ketua RW, Admin Desa / Kepala Desa
* **Prakondisi (*Preconditions*):** Warga telah login dengan akun berstatus aktif, biodata NIK terdaftar, dan data profil lengkap.
* **Pascakondisi (*Postconditions*):** Dokumen berstatus `DISETUJUI/SELESAI`, vector PDF resmi tersimpan dan siap diunduh, stempel/TTD digital tersemat, serta notifikasi terkirim ke WhatsApp warga.
* **Alur Utama (Main Flow):**
  1. Warga membuka menu "Pengajuan Surat" dan memilih jenis surat (contoh: *Surat Keterangan Tidak Mampu / SKTM*).
  2. Warga mengisi keperluan, melampirkan berkas bukti (KTP/KK), dan menekan tombol kirim.
  3. Sistem membuat ID pengajuan (`SRT-YYYYMMDD-XXXX`), status diset ke `DIAJUKAN_RT`, dan mencatat riwayat ke `document_request_histories`.
  4. Sistem mengirimkan notifikasi pesan ke WhatsApp/Telegram Ketua RT yang bersangkutan.
  5. Ketua RT masuk ke dashboard, memeriksa permohonan warga, dan menekan tombol "Setujui (Teruskan ke RW)".
  6. Sistem menyematkan identitas verifikator RT (`rt_approved_by_id`), TTD RT, dan memperbarui status menjadi `DIAJUKAN_RW`.
  7. Ketua RW meninjau surat di dashboard RW, lalu menekan tombol "Setujui (Teruskan ke Desa)".
  8. Sistem menyematkan identitas verifikator RW (`rw_approved_by_id`) dan status berubah menjadi `DIAJUKAN_DESA`.
  9. Admin Desa / Kepala Desa memeriksa kelengkapan surat di dashboard desa dan menekan tombol "Setujui & Terbitkan Surat".
  10. Sistem menyematkan stempel dinas desa, TTD Kepala Desa, menyusun QR Code verifikasi unik, dan mengubah status menjadi `DISETUJUI`.
  11. Mesin DomPDF merender berkas surat resmi menjadi vector PDF ber-kop surat.
  12. Sistem memicu WhatsApp & Telegram Service untuk mengirimkan tautan unduhan dan berkas PDF ke ponsel warga.
* **Alur Alternatif (Alternative Flows):**
  * *4a. Dokumen Berhenti di Tingkat RT:* Jika jenis surat adalah tingkat RT (contoh: *Izin Renovasi Rumah*), Ketua RT langsung menekan "Setujui & Selesai". Status langsung berubah ke `DISETUJUI` tanpa melewati RW/Desa.
  * *5a. Pengajuan Ditolak oleh RT/RW/Desa:* Verifikator menekan tombol "Tolak", memasukkan alasan penolakan pada kotak dialog. Status berubah menjadi `DITOLAK`, dan pemohon menerima notifikasi WhatsApp beserta alasan penolakan.

#### Skenario Use Case UC-11: Konsultasi Cerdas SIWIL AI (Function Calling)
* **Kode Use Case:** `UC-AI-01`
* **Nama Use Case:** Konsultasi Layanan & Eksekusi Tool Cerdas SIWIL AI
* **Aktor Utama:** Warga, Pengurus Wilayah (RT/RW/Desa)
* **Prakondisi:** Pengguna telah terautentikasi dalam sesi web aktif.
* **Pascakondisi:** Pengguna menerima jawaban akurat berbasis data faktual sistem tanpa pelanggaran privasi data kependudukan.
* **Alur Utama (Main Flow):**
  1. Pengguna membuka widget obrolan SIWIL AI di pojok kanan bawah dashboard.
  2. Pengguna mengetik pertanyaan: *"Bagaimana status pengajuan surat SKTM saya?"*
  3. Controller `AiController` mengaktifkan `AiToolService` dan menginjeksikan Dynamic System Prompt berisi identitas sesi pengguna (Nama, NIK, Peran, RT/RW).
  4. Controller menjalankan filter deterministik UU PDP untuk memastikan kueri tidak meminta data sensitif pihak ketiga.
  5. Pesan diteruskan ke Google Gemini API bersama dengan deklarasi 12 *function tools*.
  6. Model Gemini mengenali intensi pengguna dan mengembalikan respons `tool_call`: `fetch_document_requests(status="all")`.
  7. Backend mengeksekusi `AiToolService::executeTool`, mengambil data pengajuan milik NIK pengguna aktif dari database.
  8. Data hasil kueri dikirimkan kembali ke Gemini API sebagai hasil eksekusi fungsi.
  9. Gemini API merumuskan jawaban berbahasa manusia yang ramah, ringkas, dan faktual.
  10. Widget menampilkan jawaban kepada pengguna disertai tombol aksi cepat (contoh: *"Lihat Detail Surat"*).
* **Alur Alternatif (Alternative Flow - Pelanggaran UU PDP):**
  * *4a. Kueri Melanggar UU PDP:* Warga bertanya: *"Berapa nomor telepon dan alamat Pak Joko warga RT 02?"*.
  * *4b. Penolakan Deterministik:* Filter backend mendeteksi permintaan data pribadi warga lain. Sistem langsung memotong aliran dan mengembalikan pesan penolakan sopan tanpa memanggil API pihak ketiga.

---

## 3. DIAGRAM UML 2: ACTIVITY DIAGRAM ALUR BISNIS & AI

### 3.1 Activity Diagram Alur Bisnis: Pengajuan & Pengesahan Surat Berjenjang

```mermaid
flowchart TD
    Start([Mulai: Warga Butuh Surat]) --> Form[Warga Mengisi Form & Unggah Lampiran]
    Form --> Submit[Kirim Pengajuan Surat]
    Submit --> SavePending[Sistem Simpan Status: DIAJUKAN_RT]
    SavePending --> NotifRT[Sistem Kirim Notifikasi WhatsApp ke RT]
    NotifRT --> ReviewRT{Ketua RT Tinjau Berkas}
    
    ReviewRT -->|Tolak| RejectRT[RT Isi Alasan Penolakan]
    RejectRT --> UpdateReject[Update Status: DITOLAK]
    UpdateReject --> NotifRejectWarga[Kirim Notifikasi Penolakan ke Warga]
    NotifRejectWarga --> EndFail([Selesai: Pengajuan Ditolak])

    ReviewRT -->|Setujui| CheckLevelRT{Apakah Surat Cukup Tingkat RT?}
    CheckLevelRT -->|Ya: Izin Renovasi/Tamu| FinalRT[RT Setujui & Bubuhkan TTD]
    FinalRT --> ApproveFinal[Update Status: DISETUJUI / SELESAI]
    
    CheckLevelRT -->|Tidak: Butuh RW/Desa| PassRW[RT Teruskan ke RW]
    PassRW --> SaveRW[Sistem Simpan Status: DIAJUKAN_RW]
    SaveRW --> NotifRW[Notifikasi WhatsApp ke Ketua RW]
    NotifRW --> ReviewRW{Ketua RW Tinjau Berkas}
    
    ReviewRW -->|Tolak| RejectRW[RW Isi Alasan Penolakan]
    RejectRW --> UpdateReject
    
    ReviewRW -->|Setujui| CheckLevelRW{Apakah Surat Cukup Tingkat RW?}
    CheckLevelRW -->|Ya: Izin Keramaian| FinalRW[RW Setujui & Bubuhkan TTD/Stempel]
    FinalRW --> ApproveFinal

    CheckLevelRW -->|Tidak: Surat Resmi Desa| PassDesa[RW Teruskan ke Desa]
    PassDesa --> SaveDesa[Sistem Simpan Status: DIAJUKAN_DESA]
    SaveDesa --> NotifDesa[Notifikasi ke Admin Desa]
    NotifDesa --> ReviewDesa{Admin Desa / Kades Tinjau}
    
    ReviewDesa -->|Tolak| RejectDesa[Desa Isi Alasan Penolakan]
    RejectDesa --> UpdateReject
    
    ReviewDesa -->|Setujui| FinalDesa[Kades Otorisasi Akhir]
    FinalDesa --> ApproveFinal

    ApproveFinal --> GenerateQR[Sistem Generate QR Code Verifikasi]
    GenerateQR --> RenderPDF[DomPDF Render Surat Resmi Ber-Kop & Stempel]
    RenderPDF --> PushDoc[Kirim Dokumen PDF via WhatsApp & Telegram]
    PushDoc --> EndSuccess([Selesai: Dokumen Sah Diterbitkan])
```

### 3.2 Activity Diagram Alur AI: Inferensi Cerdas & Eksekusi Function Calling

```mermaid
flowchart TD
    StartAI([Pengguna Mengirim Pesan Chat]) --> ParseInput[Backend Menerima Pesan & Identitas Sesi]
    ParseInput --> PDPFilter{Lolos Filter Guardrail UU PDP?}
    
    PDPFilter -->|Tidak: Tanya Data Orang Lain| RejectPDP[Kembalikan Pesan Penolakan Kepatuhan Privasi]
    RejectPDP --> DisplayChat[Tampilkan Jawaban Penolakan di Widget UI]
    DisplayChat --> EndAI([Selesai])

    PDPFilter -->|Ya: Kueri Sah| BuildPrompt[AiToolService Rakit Dynamic System Prompt]
    BuildPrompt --> InjectRAG[Injeksi Basis Pengetahuan ai_knowledge]
    InjectRAG --> InjectTools[Daftarkan 12 Schema Function Declarations]
    InjectTools --> SendGemini[Kirim Payload ke Google Gemini API]
    
    SendGemini --> CheckCall{Model Meminta Tool Calling?}
    
    CheckCall -->|Tidak: Pertanyaan Umum| DirectReply[Model Berikan Teks Jawaban Langsung]
    DirectReply --> FormatOutput[Format Markdown & Buat Action Buttons]
    FormatOutput --> DisplayChat
    
    CheckCall -->|Ya: Butuh Data Database| ExtractTool[Ekstrak Nama Tool & Parameter JSON]
    ExtractTool --> CheckRBAC{Pengguna Punya Hak Akses Tool Tersebut?}
    
    CheckRBAC -->|Tidak Berhak| ReturnAccessDenied[Hasil Tool: Akses Ditolak untuk Role Anda]
    ReturnAccessDenied --> FeedModel[Kirim Balasan Error ke Model Gemini]
    
    CheckRBAC -->|Berhak| ExecuteBackend[Eksekusi Handler PHP dengan Auth Scopes]
    ExecuteBackend --> QueryDB[(Query Database Terproteksi Scope)]
    QueryDB --> ReturnJSON[Format Data Hasil ke Ringkasan JSON]
    ReturnJSON --> FeedModel
    
    FeedModel --> FinalInference[Gemini Rangkum Jawaban Faktual]
    FinalInference --> FormatOutput
```

---

## 4. DIAGRAM UML 3: CLASS DIAGRAM BERORIENTASI OBJEK (SOLID PRINCIPLES)

### 4.1 Penerapan Prinsip SOLID pada Struktur Kelas
Class diagram SIWIL AI dirancang untuk memisahkan logika antarmuka, orkestrasi bisnis, dan akses data secara modular:
- **Interfaces / Abstraksi:** `DocumentServiceInterface`, `NotificationChannelInterface`, `AiEngineInterface`.
- **Services (Business Layer):** `DocumentRequestService`, `AiToolService`, `WhatsAppService`, `TelegramService`, `ResidentService`.
- **Controllers (Presentation Orchestration):** Menangani validasi input, status HTTP, dan merender tampilan Blade.
- **Models (Domain Layer):** Membungkus entitas tabel, mutator, casting, dan query scopes keamanan (`scopeAccessibleBy`).

### 4.2 Visualisasi Class Diagram Lengkap

```mermaid
classDiagram
    direction TB

    class User {
        +UUID id
        +string name
        +string username
        +string email
        +string phone_number
        +string whatsapp_number
        +string role
        +string status
        +string signature_path
        +string stamp_path
        +resident() Resident
        +village() Village
        +rw() Rw
        +rt() Rt
        +hasRole(string role) bool
    }

    class Resident {
        +UUID id
        +string nik
        +string full_name
        +date birth_date
        +string gender
        +string occupation
        +string ktp_photo
        +UUID family_id
        +UUID rt_id
        +family() Family
        +rt() Rt
        +user() User
        +scopeAccessibleBy(query, User user)
    }

    class Family {
        +UUID id
        +string kk_number
        +string address
        +UUID rt_id
        +members() Resident[]
        +rt() Rt
    }

    class DocumentRequest {
        +UUID id
        +string request_number
        +string type
        +string status
        +string notes
        +string attachment_path
        +UUID resident_id
        +UUID rt_approved_by_id
        +UUID rw_approved_by_id
        +UUID desa_approved_by_id
        +resident() Resident
        +histories() DocumentRequestHistory[]
        +getFinalApprovalLevelAttribute() string
        +getQrCodeUrlAttribute() string
        +scopeAccessibleBy(query, User user)
    }

    class DocumentRequestHistory {
        +UUID id
        +UUID document_request_id
        +string status
        +UUID actor_id
        +string notes
        +actor() User
    }

    class Complaint {
        +UUID id
        +UUID user_id
        +string title
        +text description
        +decimal location_lat
        +decimal location_lng
        +string photo_path
        +string status
        +user() User
    }

    class Emergency {
        +UUID id
        +UUID user_id
        +string type
        +string location_lat
        +string location_lng
        +string status
        +user() User
    }

    class Poll {
        +UUID id
        +string title
        +text description
        +datetime expires_at
        +string status
        +UUID created_by
        +UUID rt_id
        +UUID rw_id
        +options() PollOption[]
        +votes() PollVote[]
        +getWinningOptionAttribute() PollOption
    }

    class PollOption {
        +UUID id
        +UUID poll_id
        +string option_text
        +votes() PollVote[]
    }

    class PollVote {
        +UUID id
        +UUID poll_id
        +UUID poll_option_id
        +UUID user_id
    }

    class DocumentRequestService {
        -TelegramService telegramService
        -WhatsAppService whatsAppService
        -EmailNotificationService emailService
        +store(array data, User user, file attachment) DocumentRequest
        +updateStatus(DocumentRequest doc, array data, User user) DocumentRequest
    }

    class AiToolService {
        +buildDynamicSystemPrompt(User user, string extraContext) string
        +getGeminiTools(User user) array
        +executeTool(string toolName, array args, User user) array
        -handleDashboardData(User user, array args) array
        -handleResidentsAndAreas(User user, array args) array
        -handleDocumentRequests(User user, array args) array
    }

    class WhatsAppService {
        -string botUrl
        +sendMessage(string to, string msg, array buttons) bool
        +sendDocument(string to, string base64Pdf, string filename) bool
        +sendDocumentCreatedNotification(DocumentRequest doc, User creator) void
        +sendDocumentStatusNotification(DocumentRequest doc, string status, User actor) void
        +sendPasswordResetOtp(User user, string otp) bool
    }

    class TelegramService {
        -string botToken
        +sendMessage(string chatId, string msg) bool
        +sendDocument(string chatId, string file, string filename) bool
        +sendDocumentCreatedNotification(DocumentRequest doc, User creator) void
        +sendDocumentStatusNotification(DocumentRequest doc, string status, User actor) void
    }

    class DocumentRequestController {
        -DocumentRequestService docService
        +index() View
        +create() View
        +store(StoreDocumentRequest req) Redirect
        +show(string id) View
        +updateStatus(UpdateDocumentStatusRequest req, string id) Redirect
        +print(string id) StreamedResponse
    }

    class AiController {
        -AiToolService aiToolService
        +chat(Request request) JsonResponse
        -processAi(string message, User user) array
        -checkRoleRestrictions(string msg, User user) ?string
    }

    %% Relationships
    User "1" <--> "1" Resident : "NIK = username"
    Family "1" o-- "*" Resident : "has members"
    Resident "1" o-- "*" DocumentRequest : "applies"
    DocumentRequest "1" *-- "*" DocumentRequestHistory : "tracks"
    User "1" o-- "*" Complaint : "reports"
    User "1" o-- "*" Emergency : "triggers"
    User "1" o-- "*" Poll : "creates"
    Poll "1" *-- "*" PollOption : "contains"
    PollOption "1" *-- "*" PollVote : "receives"
    User "1" o-- "*" PollVote : "casts"

    DocumentRequestController ..> DocumentRequestService : "depends on (DIP)"
    DocumentRequestService ..> WhatsAppService : "notifies"
    DocumentRequestService ..> TelegramService : "notifies"
    DocumentRequestService ..> DocumentRequest : "manages"

    AiController ..> AiToolService : "depends on (DIP)"
    AiToolService ..> DocumentRequest : "queries"
    AiToolService ..> Resident : "queries"
    AiToolService ..> User : "queries"
```

---

## 5. DIAGRAM UML 4: SEQUENCE DIAGRAM PERTUKARAN PESAN

> [!TIP]
> File XML Draw.io resmi untuk diagram sequence tersedia di [sequence_diagram.drawio](file:///c:/Users/alfar/Documents/laragon/www/siwil/sequence_diagram.drawio) atau workbook gabungan [siwil_architecture_all.drawio](file:///c:/Users/alfar/Documents/laragon/www/siwil/siwil_architecture_all.drawio) dengan 10 lifeline terstruktur: **Aktor Manusia di Lifeline Kiri**, **Core System di Lifeline Tengah**, dan **Aktor Pendukung di Lifeline Kanan**.

### 5.1 Sequence Diagram 1: Alur Layanan Persuratan Berjenjang & Digital Stamp

```mermaid
sequenceDiagram
    autonumber
    %% --- AKTOR MANUSIA DI KIRI ---
    actor W as 👤 Warga (Pemohon)
    actor RT as 👤 Ketua RT (Verifikator 1)
    actor Desa as 👤 Admin Desa / Kades (Pengesah)

    %% --- SISTEM INTI DI TENGAH ---
    participant FE as 🖥️ Web Frontend / Blade
    participant DC as 🎮 DocumentRequestController
    participant DS as ⚙️ DocumentRequestService
    participant DB as 🗄️ MySQL Database

    %% --- AKTOR PENDUKUNG DI KANAN ---
    participant DP as 📄 DomPDF & QR Generator
    participant WA as 💬 WhatsApp Gateway (Baileys)

    W->>FE: Isi Form Pengajuan Surat (Pilih SKTM & Lampiran)
    FE->>DC: POST /dashboard/documents (Form Data + File)
    DC->>DS: store(data, authUser, file)
    DS->>DB: INSERT into document_requests (Status: DIAJUKAN_RT)
    DS->>DB: INSERT into document_request_histories (Action: DIAJUKAN_RT)
    DS->>WA: sendDocumentCreatedNotification(doc, authUser)
    WA-->>RT: Pesan WhatsApp: "Pengajuan SKTM Baru dari Warga X"
    DS-->>DC: Return DocumentRequest Instance
    DC-->>FE: HTTP 302 Redirect (/dashboard/documents) + Flash Success
    FE-->>W: Tampilkan Kartu Surat Berstatus "Menunggu Verifikasi RT"

    Note over RT,Desa: Proses Verifikasi Lapangan & Approval Berjenjang
    RT->>FE: Buka Tinjauan Surat & Klik "Setujui (Teruskan ke RW)"
    FE->>DC: PATCH /dashboard/documents/{id}/status (Status: DIAJUKAN_RW)
    DC->>DS: updateStatus(doc, data, userRT)
    DS->>DB: UPDATE document_requests (rt_approved_by_id = RT.id, status = DIAJUKAN_RW)
    DS->>WA: sendDocumentStatusNotification(doc, 'DIAJUKAN_RW', userRT)
    WA-->>W: Pesan WA: "Surat Anda telah disetujui RT, diteruskan ke RW"

    Note over Desa: RW Menyetujui -> Diteruskan ke Desa
    Desa->>FE: Buka Tinjauan Akhir & Klik "Setujui & Terbitkan"
    FE->>DC: PATCH /dashboard/documents/{id}/status (Status: DISETUJUI)
    DC->>DS: updateStatus(doc, data, userDesa)
    DS->>DB: UPDATE document_requests (desa_approved_by_id, status = DISETUJUI, completed_at = NOW())
    DS->>DP: Facade/Pdf::loadView('formats.desa', docData)
    DP-->>DS: Generated Binary Vector PDF
    DS->>WA: sendDocumentStatusNotification() + Kirim Berkas PDF
    WA-->>W: Pesan WA: "Surat SKTM Resmi Telah Terbit! [Lampiran PDF]"
    DC-->>FE: HTTP 302 Redirect + Tautan Tombol "Cetak PDF" Aktif
```

### 5.2 Sequence Diagram 2: Alur Pemrosesan AI Chat & Function Calling Backend

```mermaid
sequenceDiagram
    autonumber
    %% --- AKTOR MANUSIA DI KIRI ---
    actor U as 👤 Pengguna (Warga / Pengurus)

    %% --- SISTEM INTI DI TENGAH ---
    participant UI as 🖥️ Chat Widget UI (Blade + JS)
    participant AC as 🎮 AiController
    participant TS as ⚙️ AiToolService
    participant DB as 🗄️ Database Backend

    %% --- AKTOR PENDUKUNG DI KANAN ---
    participant LLM as 🧠 Google Gemini 1.5 Pro API

    U->>UI: Ketik Pesan: "Berapa surat yang sedang diproses di RT saya?"
    UI->>AC: POST /dashboard/ai/chat (message, session_token)
    AC->>AC: checkRoleRestrictions(message, authUser)
    Note over AC: Filter UU PDP: Tidak ada pelanggaran privasi
    AC->>TS: buildDynamicSystemPrompt(authUser)
    TS-->>AC: Dynamic System Prompt (Nama, NIK, Scope RT/RW)
    AC->>TS: getGeminiTools(authUser)
    TS-->>AC: Array of 12 Function Declarations
    AC->>LLM: POST /v1beta/models/gemini-2.5-flash:generateContent (Prompt + Tools)
    
    LLM-->>AC: Response Function Call: fetch_document_requests(status="PENDING")
    
    AC->>TS: executeTool("fetch_document_requests", {status: "PENDING"}, authUser)
    TS->>DB: DocumentRequest::where('status', 'PENDING')->where(...)
    DB-->>TS: Dataset Record Surat
    TS-->>AC: JSON Result Object
    
    AC->>LLM: POST Follow-up (Function Result Payload)
    LLM-->>AC: Final Synthesized Text Response ("Terdapat 2 pengajuan surat...")
    AC-->>UI: HTTP 200 OK (JSON response)
    UI-->>U: Render Chat Bubble Markdown Response
```
    TS->>DB: DocumentRequest::accessibleBy(authUser)->where('status', 'PENDING')->get()
    DB-->>TS: Collection Data Surat (3 Dokumen di RT yang bersangkutan)
    TS-->>AC: JSON Result: {total: 3, items: [...]}
    
    AC->>LLM: POST Return Function Result (Tool Output)
    LLM-->>AC: Final Text Response: "Saat ini ada 3 pengajuan surat yang sedang menunggu verifikasi di wilayah Anda..."
    AC-->>UI: HTTP 200 JSON ({message: "...", options: [...]})
    UI-->>U: Render Pesan Balasan Ramah + Tombol Pintasan ke Menu Dokumen
```

---

## 6. DESAIN BASIS DATA RELASIONAL (ENTITY RELATIONSHIP DIAGRAM / ERD)

### 6.1 Visualisasi ERD Tingkat Konseptual & Fisik

```mermaid
erDiagram
    villages {
        uuid id PK
        string name
        string code
        timestamp created_at
    }

    rws {
        uuid id PK
        uuid village_id FK
        string number
    }

    rts {
        uuid id PK
        uuid rw_id FK
        string number
    }

    families {
        uuid id PK
        string kk_number UK
        string address
        uuid rt_id FK
    }

    residents {
        uuid id PK
        string nik UK
        string full_name
        uuid family_id FK
        uuid rt_id FK
        date birth_date
        string gender
        string religion
        string marital_status
        string education
        string occupation
        string ktp_photo
        string status
    }

    users {
        uuid id PK
        string name
        string username UK
        string email UK
        string phone_number
        string whatsapp_number
        string password
        string role
        string status
        uuid village_id FK
        uuid rw_id FK
        uuid rt_id FK
        string signature_path
        string stamp_path
    }

    document_requests {
        uuid id PK
        string request_number UK
        uuid resident_id FK
        string type
        string status
        text notes
        string attachment_path
        uuid verified_by_id FK
        uuid rt_approved_by_id FK
        uuid rw_approved_by_id FK
        uuid desa_approved_by_id FK
        timestamp completed_at
    }

    document_request_histories {
        uuid id PK
        uuid document_request_id FK
        string status
        uuid actor_id FK
        text notes
    }

    complaints {
        uuid id PK
        uuid user_id FK
        string title
        text description
        string photo_path
        decimal location_lat
        decimal location_lng
        string status
        uuid rt_id FK
        uuid rw_id FK
        uuid village_id FK
    }

    emergencies {
        uuid id PK
        uuid user_id FK
        string type
        string location_lat
        string location_lng
        string status
    }

    polls {
        uuid id PK
        string title
        text description
        uuid created_by FK
        uuid village_id FK
        uuid rw_id FK
        uuid rt_id FK
        datetime expires_at
        string status
    }

    poll_options {
        uuid id PK
        uuid poll_id FK
        string option_text
    }

    poll_votes {
        uuid id PK
        uuid poll_id FK
        uuid poll_option_id FK
        uuid user_id FK
    }

    budgets {
        uuid id PK
        uuid village_id FK
        year year
        string type
        string category
        decimal amount_budget
        decimal amount_realized
    }

    developments {
        uuid id PK
        uuid village_id FK
        string title
        decimal budget_allocated
        string source_of_fund
        string status
        int progress_percentage
        string location_lat
        string location_lng
    }

    announcements {
        uuid id PK
        uuid author_id FK
        string title
        text content
        boolean is_active
        timestamp published_at
    }

    audit_logs {
        uuid id PK
        uuid user_id FK
        string action
        string entity_name
        string entity_id
        text old_values
        text new_values
        string ip_address
    }

    system_configurations {
        uuid id PK
        string ai_provider
        string ai_model
        text ai_api_keys
        string whatsapp_bot_url
        string telegram_bot_token
    }

    villages ||--o{ rws : "contains"
    rws ||--o{ rts : "contains"
    rts ||--o{ families : "houses"
    families ||--o{ residents : "includes"
    rts ||--o{ residents : "registers"
    residents ||--o| users : "owns_account"
    residents ||--o{ document_requests : "submits"
    document_requests ||--o{ document_request_histories : "logs"
    users ||--o{ complaints : "files"
    users ||--o{ emergencies : "triggers"
    users ||--o{ polls : "authors"
    polls ||--o{ poll_options : "has"
    poll_options ||--o{ poll_votes : "tallies"
    users ||--o{ poll_votes : "casts"
    villages ||--o{ budgets : "allocates"
    villages ||--o{ developments : "executes"
    users ||--o{ audit_logs : "records"
```

---

## 7. PERANCANGAN PROTOTIPE INTERAKTIF UI/UX FIGMA

### 7.1 Design System & Panduan Gaya
* **Palet Warna Utama (Color System):**
  * `Primary (Emerald/Green)`: `#10B981` (Melambangkan pelayanan publik, kesejahteraan desa, dan kejelasan status).
  * `Primary Dark`: `#047857` (Kontras teks tombol & sidebar aktif).
  * `Danger / Emergency Red`: `#EF4444` (Warna khusus tombol SOS, status darurat aktif, dan penolakan berkas).
  * `Warning / Pending Amber`: `#F59E0B` (Warna status dokumen sedang diverifikasi / antrean).
  * `Info / Process Blue`: `#3B82F6` (Warna status pengaduan dalam pengerjaan & pengumuman warta).
  * `Neutral / Surface Dark`: `#0F172A` (Latar belakang sidebar dan teks tajam).
  * `Surface Light / Canvas`: `#F8FAFC` (Latar belakang kerja utama aplikasi).
* **Tipografi:** Keluarga font `Inter` / `Plus Jakarta Sans` dengan skala hierarki:
  * H1 (Judul Halaman): `24px` - SemiBold (600)
  * H2 (Sub-judul / Judul Kartu): `18px` - Medium (500)
  * Body Text: `14px` - Regular (400)
  * Caption & Badge Status: `12px` - SemiBold (600)
* **Aturan Tata Letak & Grid:**
  * Desktop Dashboard: 12-kolom Grid dengan sidebar tetap 260px di kiri.
  * Mobile Warga: 1-kolom Fluid Layout dengan navigasi bawah (*Bottom Navigation Bar*) dan kartu informasi bertumpuk vertikal.

### 7.2 Struktur Hierarki Informasi (Information Architecture)
1. **Portal Publik (Unauthenticated):**
   * `/` $\to$ Beranda Profil Wilayah, Ringkasan Statistik, Warta Terkini.
   * `/portal/apbdes` $\to$ Transparansi APBDes & Realisasi Anggaran.
   * `/portal/pembangunan` $\to$ Galeri Proyek Pembangunan Fisik & Titik Peta.
   * `/portal/statistik` $\to$ Piramida Penduduk, Sebaran Gender, dan Pendidikan.
2. **Dashboard Warga (`/dashboard`):**
   * Metrik Status Surat Saya (Pending, Selesai, Ditolak).
   * Tombol Aksi Cepat: Buat Surat Baru, Lapor Masalah Lingkungan, Tombol SOS.
   * Widget Musyawarah/Polling Aktif dengan Tombol Voting Langsung.
   * Widget Warta & Pengumuman Desa Terkini.
   * Float Button: Asisten Cerdas SIWIL AI.
3. **Dashboard Pengurus RT/RW/Desa (`/dashboard`):**
   * Metrik Yurisdiksi: Total Warga, Total KK, Surat Butuh Tindakan, Aduan Masuk.
   * Antrean Surat Masuk dengan Tombol Verifikasi Cepat.
   * Peta Pemantauan Spasial: Titik Aduan Warga & Titik Darurat Aktif.
   * Manajemen Data Kependudukan & Pendaftaran Akun Warga Baru.

### 7.3 Panduan Walkthrough Prototipe untuk Presentasi Video 2
Pada perekaman video evaluasi tahap 2 (durasi 5–7 menit), walkthrough prototipe Figma wajib membedah 3 alur utama:
1. **Flow 1 - Pengajuan Surat Mandiri Warga:** Demonstrasi pemilihan surat SKTM $\to$ Pengisian form $\to$ Tinjauan status di dashboard $\to$ Unduh berkas PDF yang telah memiliki tanda tangan digital dan QR Code.
2. **Flow 2 - Verifikasi Berjenjang Pengurus:** Demonstrasi pergantian peran (*Role Switching*) dari RT yang meneruskan surat $\to$ RW yang memverifikasi $\to$ Kepala Desa yang menyetujui akhir.
3. **Flow 3 - Interaksi Asisten SIWIL AI:** Demonstrasi pembukaan modal chat $\to$ Pengetikan pertanyaan status surat $\to$ Eksekusi function calling $\to$ Penyajian data live dari database ke balon percakapan.

---

## 8. BERKAS DIAGRAM DRAW.IO XML

Untuk memastikan portabilitas, standarisasi akademik, dan kemudahan evaluasi, seluruh diagram pada dokumen SDD ini telah diekspor ke dalam berkas standar **draw.io XML (`.drawio`)** dengan aturan baku: **Aktor Manusia di Kiri**, **Boundary/Sistem Inti di Tengah**, dan **Aktor Pendukung / Eksternal di Kanan**, dengan koordinat presisi dan tata letak garis penghubung ortogonal yang rapi tanpa saling menutupi (*zero line collision*):

1. 📄 **[usecase_diagram.drawio](file:///c:/Users/alfar/Documents/laragon/www/siwil/usecase_diagram.drawio)** — Use Case Diagram Lengkap (6 Aktor Manusia di Kiri, 5 Aktor Pendukung di Kanan, 24 use cases terkelompok).
2. 📄 **[activity_diagram.drawio](file:///c:/Users/alfar/Documents/laragon/www/siwil/activity_diagram.drawio)** — Activity Diagram 7 Swimlane (Warga, RT, RW, Desa di Kiri, Backend di Tengah, DomPDF & WhatsApp Bot di Kanan).
3. 📄 **[class_diagram.drawio](file:///c:/Users/alfar/Documents/laragon/www/siwil/class_diagram.drawio)** — Class Diagram 3 Package Berorientasi Objek (Controllers/Manusia di Kiri, Domain/Services di Tengah, External Gateways di Kanan - SOLID Compliant).
4. 📄 **[sequence_diagram.drawio](file:///c:/Users/alfar/Documents/laragon/www/siwil/sequence_diagram.drawio)** — Sequence Diagram Pertukaran Pesan (4 Aktor Manusia di Lifeline Kiri, Core Systems di Tengah, 2 Aktor Pendukung di Kanan).
5. 📄 **[erd_diagram.drawio](file:///c:/Users/alfar/Documents/laragon/www/siwil/erd_diagram.drawio)** — Entity Relationship Diagram Basis Data Relasional 18 Entitas (Data Manusia & Wilayah di Kiri, Transaksi di Tengah, Sistem Pendukung/AI di Kanan).
6. 📦 **[siwil_architecture_all.drawio](file:///c:/Users/alfar/Documents/laragon/www/siwil/siwil_architecture_all.drawio)** — **Workbook Lengkap (Multi-Tab)** yang menggabungkan seluruh kelima diagram di atas ke dalam satu file terpadu siap pakai di aplikasi Draw.io / diagrams.net.

---

## 9. PENGESAHAN DOKUMEN ARSITEKTUR

Dokumen Spesifikasi Desain Sistem (SDD) ini dinyatakan sah dan memenuhi kriteria penilaian mutu pemodelan perangkat lunak Tahap 2 Proyek Akhir Mandiri Rekayasa Perangkat Lunak.

*Ditetapkan di: Cirebon*  
*Tanggal: 12 November 2026*  
*Penulis Dokumen: Mahasiswa S1 Teknik Informatika (Full-Stack Engineer)*

