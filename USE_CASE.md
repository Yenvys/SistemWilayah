---

# 📄 USE CASE DIAGRAM — SIWIL
## Sistem Informasi Wilayah & Pemerintahan

```
╔══════════════════════════════════════════════════════════════════════════════════╗
║         USE CASE DIAGRAM — SIWIL (Sistem Informasi Wilayah & Pemerintahan)       ║
╚══════════════════════════════════════════════════════════════════════════════════╝
```

---

## 🎭 DAFTAR AKTOR

### ◀ MAIN ACTOR (Business Owner) — KIRI

| Simbol | Aktor | Peran |
| :---: | :--- | :--- |
| **👤** | **WARGA** | Pemilik kepentingan bisnis, pengguna utama layanan |

### SUPPORTING ACTORS ▶ — KANAN

| Simbol | Aktor | Peran |
| :---: | :--- | :--- |
| **👤** | **SUPERADMIN** | System Owner (verifikasi akun, kelola sistem) |
| **👤** | **KETUA RT** | Verifikator Tingkat 1 |
| **👤** | **KETUA RW** | Verifikator Tingkat 2 |
| **👤** | **DESA / KELURAHAN** | Penerbit Surat Resmi (TTE) |
| **🤖** | **AI ENGINE** | Chatbot + Analytics (Secondary Actor) |

---

## 🏛️ SYSTEM BOUNDARY

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                                                                             │
│         SIWIL — Sistem Informasi Wilayah & Pemerintahan                     │
│                                                                             │
│  ┌─────────────────────────────────────────────────────────────────────┐    │
│  │  A. AUTENTIKASI & PROFIL                                            │    │
│  ├─────────────────────────────────────────────────────────────────────┤    │
│  │  B. ADMINISTRASI & PERSURATAN                                       │    │
│  ├─────────────────────────────────────────────────────────────────────┤    │
│  │  C. PENGADUAN & PARTISIPASI WARGA                                   │    │
│  ├─────────────────────────────────────────────────────────────────────┤    │
│  │  D. INFORMASI & TRANSPARANSI PUBLIK                                 │    │
│  ├─────────────────────────────────────────────────────────────────────┤    │
│  │  E. LAYANAN CERDAS BERBASIS AI                                      │    │
│  └─────────────────────────────────────────────────────────────────────┘    │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## 📦 A. AUTENTIKASI & PROFIL
**Total: 6 Use Case**

| ID | Use Case | Main Actor | Supporting Actor | Relasi |
| :--- | :--- | :---: | :--- | :--- |
| **UC-W01** | Registrasi Akun Warga (`<<extend>>` verifikasi NIK/KK) | Warga | SuperAdmin *(verifikasi)* | — |
| **UC-W02** | Login / Autentikasi (Multi-role: warga & admin) | Warga | SuperAdmin *(kelola akun)* | `<<include>>` UC-W01 |
| **UC-W03** | Update Profil Pribadi & Foto KTP | Warga | — | — |
| **UC-W04** | Kelola Data Keluarga (KK) & Anggota Keluarga | Warga | RT *(verifikasi)* | — |
| **UC-W04b** | Lapor Perubahan Data (Lahir/Mati/Pindah) | Warga | RT *(verifikasi)* | — |
| **UC-W04c** | Lihat Riwayat Aktivitas & Notifikasi | Warga | — | — |

---

## 📦 B. ADMINISTRASI & PERSURATAN
**Total: 13 Use Case**

| ID | Use Case | Main Actor | Supporting Actor | Relasi |
| :--- | :--- | :---: | :--- | :--- |
| **UC-W05** | Ajukan Surat Pengantar RT (Domisili, SKTM dasar, dll) | Warga | RT *(proses)* | — |
| **UC-W06** | Ajukan Surat Keterangan Desa (SKTM, SKU, Ahli Waris, dll) | Warga | RT, RW, Desa *(terbitkan surat)* | `<<include>>` UC-W05, UC-W08 |
| **UC-W07** | Ajukan Surat Pengantar Nikah (ke KUA) | Warga | RW *(approve)* | `<<include>>` UC-W05 |
| **UC-W08** | Upload Dokumen Persyaratan | Warga | SuperAdmin | — |
| **UC-W09** | Lacak Status Pengajuan (RT → RW → Desa) | Warga | RW *(monitoring)* | `<<include>>` UC-W10 |
| **UC-W10** | Terima Notifikasi Perubahan Status | Warga | — | — |
| **UC-W11** | Unduh Surat PDF (TTE / Barcode) | Warga | Desa *(TTE)* | — |
| **UC-W12** | Beri Rating & Feedback atas Layanan | Warga | — | — |
| **UC-W06b** | Ajukan Surat Pindah / Datang | Warga | — | `<<include>>` UC-W05 |
| **UC-W06c** | Ajukan Izin Keramaian / Rekomendasi | Warga | — | — |
| **UC-W06d** | Ajukan Surat Kehilangan / Legalitas | Warga | — | — |
| **UC-W06e** | Booking Antrian Pelayanan Tatap Muka | Warga | — | — |

---

## 📦 C. PENGADUAN & PARTISIPASI WARGA
**Total: 7 Use Case**

| ID | Use Case | Main Actor | Supporting Actor | Relasi |
| :--- | :--- | :---: | :--- | :--- |
| **UC-W13** | Kirim Pengaduan (Foto + Geotag) | Warga | RT *(tindak lanjut)*, RW *(lintas RT)*, Desa *(kelola laporan)*, AI *(klasifikasi)* | — |
| **UC-W14** | Lacak & Diskusi Pengaduan | Warga | — | — |
| **UC-W15** | Ikut Polling / Musyawarah Digital | Warga | RW *(fasilitasi)* | — |
| **UC-W16** | Usulkan Program Pembangunan | Warga | RW *(kaji usulan)* | — |
| **UC-W13b** | 🚨 Lapor Darurat (Kebakaran, Medis, Bencana) | Warga | RT *(respons darurat)*, Desa *(tindak darurat)* | — |
| **UC-W13c** | Lihat Peta Laporan Warga (Real-time) | Warga | AI *(geo-clustering)* | — |

---

## 📦 D. INFORMASI & TRANSPARANSI PUBLIK
**Total: 7 Use Case**

| ID | Use Case | Main Actor | Supporting Actor | Relasi |
| :--- | :--- | :---: | :--- | :--- |
| **UC-W17** | Lihat Profil Desa & Peta Wilayah | Warga | Desa *(data master)* | — |
| **UC-W18** | Lihat Statistik Penduduk & Demografi | Warga | Desa *(publikasi data)* | — |
| **UC-W19** | Lihat APBDes & Realisasi Anggaran | Warga | Desa *(transparansi)* | — |
| **UC-W20** | Lihat Pengumuman & Agenda Kegiatan | Warga | RT *(publikasi)* | — |
| **UC-W19b** | Lihat Peta Pembangunan / Proyek Desa | Warga | Desa *(publikasi proyek)* | — |
| **UC-W19c** | Cek Titik Rawan (Banjir, Kriminal) | Warga | AI *(analitik spasial)* | — |

---

## 📦 E. LAYANAN CERDAS BERBASIS AI
**Total: 4 Use Case**

| ID | Use Case | Main Actor | Supporting Actor | Relasi |
| :--- | :--- | :---: | :--- | :--- |
| **UC-W21** | Konsultasi Chatbot AI (24/7 Bahasa Indonesia) | Warga | AI *(NLP engine)* | — |
| **UC-W22** | Terima Rekomendasi Layanan Personal | Warga | AI *(ML personalisasi)* | — |
| **UC-W23** | Terima Peringatan Dini (Bencana / Kesehatan) | Warga | AI *(prediksi bencana)*, Desa *(early warning)* | — |
| **UC-W24** | Ajukan Pertanyaan via Suara / Voice AI | Warga | AI *(speech-to-text)* | — |

---

## 🔗 RELASI `<<include>>` / `<<extend>>` ANTAR USE CASE

| No | Dari | Ke | Jenis | Penjelasan |
| :---: | :--- | :--- | :---: | :--- |
| 1 | UC-W02 | UC-W01 | `<<include>>` | Login memerlukan registrasi |
| 2 | UC-W06 | UC-W05 | `<<include>>` | Surat Desa wajib lewat pengantar RT |
| 3 | UC-W07 | UC-W05 | `<<include>>` | Surat Nikah wajib lewat pengantar RT |
| 4 | UC-W06b | UC-W05 | `<<include>>` | Surat Pindah wajib lewat pengantar RT |
| 5 | UC-W06 | UC-W08 | `<<include>>` | Surat Desa wajib upload dokumen |
| 6 | UC-W09 | UC-W10 | `<<include>>` | Lacak status disertai notifikasi |

---

## 🔀 ALUR SURAT BERJENJANG (Jantung Sistem)

```
   ┌──────────────┐
   │    WARGA     │
   │  (Main Actor)│
   └───────┬──────┘
           │  UC-W06 (Ajukan Surat Keterangan Desa)
           ▼
   ┌──────────────────────────────────────┐
   │  <<include>> UC-W05 (Pengantar RT)   │◀──── KETUA RT
   │  <<include>> UC-W08 (Upload Dokumen) │◀──── SUPERADMIN
   └───────────────┬──────────────────────┘
                   ▼
   ┌──────────────────────────────────────┐
   │   Verifikasi Lanjutan di Tingkat RW  │◀──── KETUA RW
   └───────────────┬──────────────────────┘
                   ▼
   ┌──────────────────────────────────────┐
   │   Terbitkan Surat Resmi (TTE)        │◀──── DESA/KELURAHAN
   │   → UC-W11 (Unduh PDF)               │
   └───────────────┬──────────────────────┘
                   ▼
   ┌──────────────────────────────────────┐
   │   UC-W10 (Notifikasi) & UC-W09       │
   │   (Lacak Status → Warga)             │
   └──────────────────────────────────────┘
```

---

## 🎨 MATRIKS AKTOR × USE CASE (Traceability Matrix)

| Kode | Use Case | Warga | RT | RW | Desa | SA | AI |
| :---: | :--- | :---: | :---: | :---: | :---: | :---: | :---: |
| W01 | Registrasi | ✅ | | | | 🔧 | |
| W02 | Login | ✅ | | | | 🔧 | |
| W03 | Update Profil | ✅ | | | | | |
| W04 | Kelola KK | ✅ | 🔧 | | | | |
| W04b | Lapor Perubahan | ✅ | 🔧 | | | | |
| W04c | Riwayat Aktivitas | ✅ | | | | | |
| W05 | Surat Pengantar RT | ✅ | 🔧 | | | | |
| W06 | Surat Ket. Desa | ✅ | 🔧 | 🔧 | 🔧 | | |
| W07 | Surat Nikah | ✅ | | 🔧 | | | |
| W08 | Upload Dokumen | ✅ | | | | 🔧 | |
| W09 | Lacak Status | ✅ | | 🔧 | | | |
| W10 | Notifikasi | ✅ | | | | | |
| W11 | Unduh PDF | ✅ | | | 🔧 | | |
| W12 | Rating Layanan | ✅ | | | | | |
| W06b | Surat Pindah | ✅ | | | | | |
| W06c | Izin Keramaian | ✅ | | | | | |
| W06d | Surat Kehilangan | ✅ | | | | | |
| W06e | Booking Antrian | ✅ | | | | | |
| W13 | Kirim Pengaduan | ✅ | 🔧 | 🔧 | 🔧 | | 🤖 |
| W14 | Diskusi Pengaduan | ✅ | | | | | |
| W15 | Polling Warga | ✅ | | 🔧 | | | |
| W16 | Usul Pembangunan | ✅ | | 🔧 | | | |
| **W13b** | **🚨 Darurat** | ✅ | 🔧 | | 🔧 | | |
| W13c | Peta Laporan | ✅ | | | | | 🤖 |
| W17 | Profil Desa | ✅ | | | 🔧 | | |
| W18 | Statistik Penduduk | ✅ | | | 🔧 | | |
| W19 | APBDes | ✅ | | | 🔧 | | |
| W20 | Pengumuman | ✅ | 🔧 | | | | |
| W19b | Peta Pembangunan | ✅ | | | 🔧 | | |
| W19c | Titik Rawan | ✅ | | | | | 🤖 |
| W21 | Chatbot AI | ✅ | | | | | 🤖 |
| W22 | Rekomendasi Personal | ✅ | | | | | 🤖 |
| W23 | Peringatan Dini | ✅ | | | 🔧 | | 🤖 |
| W24 | Voice AI | ✅ | | | | | 🤖 |

**Keterangan:**
- ✅ = Aktor utama yang menginisiasi
- 🔧 = Supporting actor yang mendukung (verifikasi, proses, publikasi)
- 🤖 = Layanan AI yang di-*include*

---

## 📋 LEGENDA

| Simbol | Arti |
| :---: | :--- |
| **━━━━** | Garis Solid Merah — Main Actor (Warga) ↔ Use Case |
| **━━━━** | Garis Solid Hijau — Supporting Actor ↔ Use Case |
| **- - -** | Garis Putus Biru — `<<include>>` / `<<extend>>` antar Use Case |
| **━━━━** | Garis Solid Kuning — Peran AI Engine |
| 🟥 Use Case Merah | Layanan Darurat (Prioritas Tinggi) |
| 🟨 Use Case Kuning | Layanan AI (Cross-cutting) |
| 🟦 Use Case Biru | Use Case Fungsional Standar |

---

## 📊 STATISTIK AKHIR

| Kategori | Jumlah |
| :--- | :---: |
| **Main Actor** | 1 (Warga) |
| **Supporting Actors** | 5 (SuperAdmin, RT, RW, Desa, AI) |
| **Total Use Case** | **39 UC** |
| — Domain A (Autentikasi) | 6 UC |
| — Domain B (Persuratan) | 13 UC |
| — Domain C (Pengaduan) | 7 UC |
| — Domain D (Informasi) | 7 UC |
| — Domain E (AI) | 4 UC |
| **Relasi `<<include>>`/`<<extend>>`** | 6 relasi |
| **Asosiasi Aktor ↔ UC** | 41 garis |

---

## 🎯 HIGHLIGHT UTAMA

1. **Warga = Main Actor** — semua 34 UC fungsional bermuara pada Warga sebagai penerima manfaat utama.
2. **AI bukan aktor terpisah** — melainkan *cross-cutting concern* yang di-*include* oleh UC tertentu.
3. **Alur Surat Berjenjang** — UC-W06 → UC-W05 → RW → Desa → UC-W11 adalah *core business process* SIWIL.
4. **Layanan Darurat (UC-W13b)** — satu-satunya UC yang melibatkan **3 supporting actor sekaligus** (RT + Desa + response cepat).
5. **Transparansi Publik (Domain D)** — membedakan SIWIL dari sistem admin biasa; semua data APBDes, statistik, dan peta proyek terbuka untuk Warga.

---

# 📄 Use Case: Warga

Warga adalah **Actor** (Aktor Utama) dalam sistem SIWIL. Seluruh layanan fungsional bermuara pada Warga sebagai pengguna utama dan penerima manfaat. Berikut adalah daftar Use Case dimana Warga berinteraksi dengan sistem:

## A. Autentikasi & Profil
1. **UC-W01**: Registrasi Akun Warga (`<<extend>>` verifikasi NIK/KK)
2. **UC-W02**: Login / Autentikasi
3. **UC-W03**: Update Profil Pribadi & Foto KTP
4. **UC-W04**: Kelola Data Keluarga (KK) & Anggota Keluarga
5. **UC-W04b**: Lapor Perubahan Data (Lahir/Mati/Pindah)
6. **UC-W04c**: Lihat Riwayat Aktivitas & Notifikasi

## B. Administrasi & Persuratan
9. **UC-W07**: Ajukan Surat Pengantar
10. **UC-W08**: Upload Dokumen Persyaratan
11. **UC-W09**: Lacak Status Pengajuan (RT → RW → Desa)
12. **UC-W10**: Terima Notifikasi Perubahan Status
13. **UC-W11**: Unduh Surat PDF (TTE / Barcode)
14. **UC-W12**: Beri Rating & Feedback atas Layanan
15. **UC-W06b**: Ajukan Surat Pindah / Datang
16. **UC-W06c**: Ajukan Izin Keramaian / Rekomendasi
17. **UC-W06d**: Ajukan Surat Kehilangan / Legalitas
18. **UC-W06e**: Booking Antrian Pelayanan Tatap Muka

## C. Pengaduan & Partisipasi Warga
19. **UC-W13**: Kirim Pengaduan (Foto + Geotag)
20. **UC-W14**: Lacak & Diskusi Pengaduan
21. **UC-W15**: Ikut Polling / Musyawarah Digital
22. **UC-W16**: Usulkan Program Pembangunan
23. **UC-W13b**: 🚨 Lapor Darurat (Kebakaran, Medis, Bencana)
24. **UC-W13c**: Lihat Peta Laporan Warga (Real-time)

## D. Informasi & Transparansi Publik
25. **UC-W17**: Lihat Profil Desa & Peta Wilayah
26. **UC-W18**: Lihat Statistik Penduduk & Demografi
27. **UC-W19**: Lihat APBDes & Realisasi Anggaran
28. **UC-W20**: Lihat Pengumuman & Agenda Kegiatan
29. **UC-W19b**: Lihat Peta Pembangunan / Proyek Desa
30. **UC-W19c**: Cek Titik Rawan (Banjir, Kriminal)

## E. Layanan Cerdas (AI)
31. **UC-W21**: Konsultasi Chatbot AI (24/7 Bahasa Indonesia)
32. **UC-W22**: Terima Rekomendasi Layanan Personal
33. **UC-W23**: Terima Peringatan Dini (Bencana / Kesehatan)
34. **UC-W24**: Ajukan Pertanyaan via Suara / Voice AI

---

# 📄 Use Case: Ketua RT & Ketua RW

Ketua RT dan Ketua RW bertindak sebagai **Actor** (Verifikator) dalam sistem SIWIL. Mereka bertugas menyaring, memverifikasi, dan merespons interaksi dari warga di tingkat wilayah masing-masing.

## 👥 A. Peran Ketua RT (Verifikator Tingkat 1)

Ketua RT berinteraksi pada Use Case berikut:
1. **UC-W04**: Kelola Data Keluarga (KK) *(Verifikasi pendataan KK)*
2. **UC-W04b**: Lapor Perubahan Data *(Verifikasi mutasi penduduk)*
3. **UC-W05**: Surat Pengantar RT *(Memproses pengantar)*
4. **UC-W06**: Surat Keterangan Desa *(Verifikasi awal sebelum ke RW/Desa)*
5. **UC-W13**: Kirim Pengaduan *(Tindak lanjut laporan warga di tingkat RT)*
6. **UC-W13b**: 🚨 Lapor Darurat *(Memberikan respons darurat pertama)*
7. **UC-W20**: Lihat Pengumuman & Agenda Kegiatan *(Mempublikasikan pengumuman warga)*

## 👥 B. Peran Ketua RW (Verifikator Tingkat 2)

Ketua RW berinteraksi pada Use Case berikut:
1. **UC-W06**: Surat Keterangan Desa *(Verifikasi lanjutan dari RT)*
2. **UC-W07**: Surat Pengantar Nikah *(Persetujuan sebelum ke Desa/KUA)*
3. **UC-W09**: Lacak Status Pengajuan *(Monitoring surat di wilayahnya)*
4. **UC-W13**: Kirim Pengaduan *(Tindak lanjut laporan lintas RT)*
5. **UC-W15**: Ikut Polling / Musyawarah Digital *(Mempublikasi dan memfasilitasi musyawarah)*
6. **UC-W16**: Usulkan Program Pembangunan *(Mengkaji usulan dari warga/RT)*

---

# 📄 Use Case: Admin Desa / Kelurahan

Admin Desa atau Kelurahan bertindak sebagai **Actor** (Penerbit dan Pemegang Data Master) pada sistem SIWIL. Mereka memiliki wewenang untuk menerbitkan dokumen resmi, merespons darurat di tingkat luas, dan mengatur data keterbukaan informasi publik.

Berikut adalah daftar Use Case dimana Admin Desa/Kelurahan terlibat:

## Persuratan & Pengaduan
1. **UC-W06**: Surat Keterangan Desa *(Memverifikasi akhir dan menerbitkan surat)*
2. **UC-W11**: Unduh Surat PDF *(Membubuhkan TTE/Barcode sebelum diunduh)*
3. **UC-W13**: Kirim Pengaduan *(Mengelola pelaporan berskala kelurahan/desa)*
4. **UC-W13b**: 🚨 Lapor Darurat *(Melakukan tindak lanjut darurat resmi, misal: damkar, ambulans)*

## Informasi, Transparansi, & Layanan (Papan Publik)
5. **UC-W17**: Lihat Profil Desa & Peta Wilayah *(Mengelola data master profil)*
6. **UC-W18**: Lihat Statistik Penduduk & Demografi *(Mempublikasikan data demografi)*
7. **UC-W19**: Lihat APBDes & Realisasi Anggaran *(Mengelola asas transparansi keuangan)*
8. **UC-W19b**: Lihat Peta Pembangunan / Proyek Desa *(Mempublikasikan progres proyek)*
9. **UC-W23**: Terima Peringatan Dini *(Men-trigger dan menyiarkan peringatan dini bencana)*

---

# 📄 Use Case: Super Admin

Super Admin bertindak sebagai **Actor** (Pemilik Sistem / System Owner) dalam SIWIL. Mereka bertugas menjaga stabilitas aplikasi, mengatur akun, dan mengelola validitas file dari sisi *back-office*.

Berikut adalah Use Case yang beririsan dengan Super Admin:

1. **UC-W01**: Registrasi Akun Warga *(Melakukan verifikasi akun atau NIK)*
2. **UC-W02**: Login / Autentikasi *(Mengelola akun dan mengatur hak akses)*
3. **UC-W08**: Upload Dokumen Persyaratan *(Memastikan server menyimpan file yang sah dan aman)*

---

# 📄 Use Case: AI Engine

AI Engine merupakan **Secondary/Supporting Actor** yang memberikan sentuhan layanan cerdas, otomasi, dan analitik data tingkat lanjut (*Machine Learning* dan *NLP*) bagi pengguna SIWIL. AI Engine bukan manusia, melainkan modul yang beroperasi *cross-cutting* ke beberapa fitur.

Berikut adalah fitur-fitur yang digerakkan/didukung oleh AI Engine:

## Analitik Geospasial & Laporan
1. **UC-W13**: Kirim Pengaduan *(Melakukan klasifikasi topik laporan secara otomatis)*
2. **UC-W13c**: Lihat Peta Laporan Warga *(Melakukan geo-clustering untuk memetakan laporan yang berdekatan)*
3. **UC-W19c**: Cek Titik Rawan *(Menganalisis dan memprediksi titik rawan kriminal/banjir)*

## Interaksi & Personalisasi (Modul Cerdas AI)
4. **UC-W21**: Konsultasi Chatbot AI *(Menjadi mesin NLP / Natural Language Processing 24/7)*
5. **UC-W22**: Terima Rekomendasi Layanan Personal *(Menggunakan algoritma ML untuk rekomendasi warga)*
6. **UC-W23**: Terima Peringatan Dini *(Algoritma AI untuk prediksi atau deteksi bencana awal)*
7. **UC-W24**: Ajukan Pertanyaan via Suara *(Berperan sebagai modul Speech-to-Text)*
