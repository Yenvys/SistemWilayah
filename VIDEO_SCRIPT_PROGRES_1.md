# NASKAH PRESENTASI VIDEO PROGRES 1 (PRD / SRS MANDIRI)
## SIWIL — Sistem Informasi Wilayah & Pemerintahan Terpadu
**Mata Kuliah:** Rekayasa Perangkat Lunak (RPL)  
**Dosen Pengampu:** Yudhistira  
**Nama Mahasiswa:** Muhammad Al Farel  
**NIM:** 41257124  
**Kelas:** TI 1B 2025  
**Target Durasi:** 3 – 5 Menit (Optimal: ~4 Menit)

---

### PANDUAN PEREKAMAN BAGI MAHASISWA
- **Gaya Penyampaian:** Percaya diri, lugas, artikulasi jelas, tempo stabil (tidak terlalu cepat, tidak berbelit-belit).
- **Tata Letak Layar:** 
  - Kamera wajah (webcam) di pojok kanan bawah atau kiri bawah.
  - Tampilan utama merekam dokumen PRD.md / slide presentasi PowerPoint / PDF.
- **Tanda Visual:** Ikuti petunjuk `[TAMPILKAN PADA LAYAR]` untuk mengganti tampilan visual yang sedang direkam.

---

## TIMELINE & NASKAH KATA-DEMI-KATA (VERBATIM)

### BAGIAN 1: PEMBUKAAN & IDENTITAS DIRI (00:00 - 00:35)
**Panduan Durasi:** 35 Detik  
**Tampilan Layar:** `[TAMPILKAN PADA LAYAR: Slide Judul berisi Nama: Muhammad Al Farel, NIM: 41257124, Kelas: TI 1B 2025, Dosen: Yudhistira, Mata Kuliah: Rekayasa Perangkat Lunak, Judul: SIWIL]`

> *"Assalamu’alaikum Warahmatullahi Wabarakatuh,*  
> *Selamat pagi/siang kepada Bapak Yudhistira selaku dosen pengampu mata kuliah Rekayasa Perangkat Lunak, serta rekan-rekan sekalian.*  
> 
> *Perkenalkan, nama saya Muhammad Al Farel, NIM 41257124, dari kelas TI 1B angkatan 2025.*  
> 
> *Pada kesempatan kali ini, saya akan mempresentasikan laporan **Tahap 1: Inisiasi dan Penyusunan Product Requirements Document (PRD) atau Software Requirements Specification (SRS)** untuk proyek aplikasi web yang saya kembangkan, yaitu **SIWIL — Sistem Informasi Wilayah dan Pemerintahan Terpadu**.*  
> 
> *Penyusunan dokumen ini mengacu secara ketat pada standar internasional **IEEE Std 830** dan **ISO/IEC/IEEE 29148**, serta standar evaluasi kualitas **ISO/IEC 25010**."*

---

### BAGIAN 2: LATAR BELAKANG MASALAH & URGENSI RIIL (00:35 - 01:20)
**Panduan Durasi:** 45 Detik  
**Tampilan Layar:** `[TAMPILKAN PADA LAYAR: PRD Bab 2.1 — Poin Masalah Birokrasi Manual, Keterbatasan Jam RT/RW, Kerentanan Privasi UU PDP, dan Minimnya Transparansi APBDes]`

> *"Bapak dosen dan rekan-rekan, proyek SIWIL diinisiasi dari permasalahan nyata dalam birokrasi pemerintahan akar rumput di Indonesia, yaitu di tingkat RT, RW, dan kantor Desa/Kelurahan.*  
> 
> *Setidaknya ada empat masalah utama yang kami temukan:*  
> *1. **Rantai birokrasi berjenjang yang lambat:** Warga harus bolak-balik meminta surat pengantar fisik ke rumah Ketua RT, lalu ke rumah Ketua RW, dan mengantre di kantor desa. Proses ini memakan waktu 2 hingga 5 hari kerja.*  
> *2. **Keterbatasan waktu pengurus wilayah:** Pengurus RT dan RW adalah tokoh masyarakat yang memiliki pekerjaan utama di luar perannya, sehingga warga kerap kali kesulitan menemui mereka secara langsung.*  
> *3. **Kerentanan privasi data:** Pencatatan kependudukan di buku register manual sangat rentan hilang, rusak, dan melanggar **Undang-Undang Nomor 27 Tahun 2022 tentang Pelindungan Data Pribadi (UU PDP)** karena NIK dan data keluarga terbuka secara bebas.*  
> *4. **Minimnya transparansi anggaran:** Informasi realisasi APBDes dan progres proyek pembangunan desa jarang tersosialisasikan secara terbuka kepada warga.*  
> 
> *Hadirnya SIWIL adalah untuk mentransformasikan seluruh birokrasi fisik ini menjadi **layanan digital nirkertas (zero paperwork)**, transparan, dan dapat diakses 24 jam sehari."*

---

### BAGIAN 3: RUANG LINGKUP & PENGGUNA SISTEM (RBAC) (01:20 - 02:00)
**Panduan Durasi:** 40 Detik  
**Tampilan Layar:** `[TAMPILKAN PADA LAYAR: PRD Bab 2.4 — Diagram Hirarki Aktor & Tabel Matriks Hak Akses RBAC: Warga, RT, RW, Desa, Superadmin, dan Publik]`

> *"Sistem SIWIL menerapkan arsitektur kontrol akses berbasis peran atau **Role-Based Access Control (RBAC)** yang ketat dan terbagi atas 5 aktor utama:*  
> 
> *1. **Warga Masyarakat:** Memiliki layanan mandiri untuk mengajukan surat, melapor aduan dengan bukti foto dan koordinat GPS, voting musyawarah, menekan tombol darurat SOS, dan berkonsultasi dengan asisten AI.*  
> *2. **Ketua RT:** Sebagai verifikator tingkat pertama permohonan surat warga, verifikator akun warga baru, serta pengelola master kependudukan di lingkup RT-nya.*  
> *3. **Ketua RW:** Sebagai verifikator tingkat kedua dan koordinator kebijakan antar-RT.*  
> *4. **Admin Desa:** Sebagai penerbit surat resmi berkekuatan hukum dengan Tanda Tangan Elektronik ber-QR Code, pengelola proyek fisik, dan administrator APBDes.*  
> *5. **Super Administrator:** Mengelola konfigurasi terpusat sistem seperti API Key AI, WhatsApp Gateway, dan audit logs.*  
> *Serta **Pengunjung Publik** yang dapat mengakses portal keterbukaan APBDes dan statistik desa tanpa perlu login."*

---

### BAGIAN 4: KEBUTUHAN FUNGSIONAL & CRITERIA GHERKIN (02:00 - 02:50)
**Panduan Durasi:** 50 Detik  
**Tampilan Layar:** `[TAMPILKAN PADA LAYAR: PRD Bab 3 — Contoh User Stories dan Acceptance Criteria Gherkin (Given-When-Then) untuk Modul Surat 3-Tier & Registrasi NIK/KK]`

> *"Dalam mendefinisikan kebutuhan fungsional, dokumen PRD SIWIL menyusun seluruh kebutuhan ke dalam format **User Stories** dan memvalidasinya menggunakan kriteria penerimaan **Gherkin (Given-When-Then)**.*  
> 
> *Sebagai contoh pada **Modul Autentikasi (US-03)**, registrasi warga menerapkan **Zero Fake Account validation**:*  
> *`Given` calon warga berada di halaman register,*  
> *`When` menginput NIK dan Nomor KK 16 digit,*  
> *`Then` sistem memvalidasi silang ke basis data kependudukan desa. Akun hanya akan dibuat berstatus pending jika NIK dan No. KK-nya terdaftar resmi di database desa.*  
> 
> *Contoh lain pada **Modul Persuratan Digital 3-Tier (US-08)**:*  
> *Ketika warga mengajukan surat SKTM atau Domisili, sistem memproses alur persetujuan berjenjang: disetujui RT terlebih dahulu, diteruskan ke RW, hingga disahkan oleh Kantor Desa.*  
> *Setelah disahkan, sistem secara otomatis membubuhkan Tanda Tangan Elektronik dan men-generate dokumen PDF resmi ber-QR Code verifikasi yang dapat dipindai oleh pihak instansi luar."*

---

### BAGIAN 5: SPESIFIKASI FITUR KECERDASAN BUATAN (AI ENGINE) (02:50 - 03:45)
**Panduan Durasi:** 55 Detik  
**Tampilan Layar:** `[TAMPILKAN PADA LAYAR: PRD Bab 5 — Diagram Arsitektur AI SIWIL, Daftar 12 Backend Function Calling Tools, dan Klausul Guardrail UU PDP]`

> *"Salah satu keunggulan inovatif SIWIL adalah integrasi fitur kecerdasan buatan, yaitu **SIWIL AI Assistant**.*  
> 
> *Kami menggunakan Large Language Model berbasis **Google Gemini API** sebagai engine primer, yang kami integrasikan secara real-time ke dalam backend aplikasi.*  
> 
> *Fitur AI ini dirancang dengan prinsip **kebenaran data dan kepatuhan privasi** melalui tiga arsitektur utama:*  
> *1. **12 Dynamic Function Calling Tools:** AI tidak dibiarkan berhalusinasi atau mengarang data. AI dibekali 12 tools di backend untuk membaca data riil secara aman, seperti memeriksa status surat, jadwal pengumuman, data APBDes, hingga biodata keluarga pengguna.*  
> *2. **Penegakan Guardrails UU PDP:** AI kami bekali instruksi sistem yang melarang keras membocorkan NIK, nomor telepon, alamat, atau biodata warga lain kepada akun berstatus warga biasa.*  
> *3. **Multi-Key Rotation & Smart Fallback:** Sistem mendukung rotasi otomatis kunci API cadangan jika kuota habis, serta dilengkapi fallback berbasis pattern matching lokal sehingga layanan chat tetap aktif meski koneksi API luar terputus."*

---

### BAGIAN 6: PENUTUP & KESIAPAN TAHAP 2 (03:45 - 04:10)
**Panduan Durasi:** 25 Detik  
**Tampilan Layar:** `[TAMPILKAN PADA LAYAR: Bab 7 Matriks RTM dan Slide Penutup yang memuat Identitas Mahasiswa & Ucapan Terima Kasih]`

> *"Seluruh spesifikasi kebutuhan fungsional dan non-fungsional berstandar **ISO 25010** ini telah dipetakan secara lengkap ke dalam **Requirements Traceability Matrix (RTM)** di dalam dokumen PRD.*  
> 
> *Dokumen PRD ini menjadi fondasi yang kokoh bagi saya untuk melanjutkan ke **Tahap 2**, yaitu perancangan arsitektur sistem dan pemodelan empat diagram UML utama, ERD basis data, serta prototipe antarmuka UI/UX.*  
> 
> *Demikian presentasi progres tahap 1 ini saya sampaikan. Terima kasih banyak atas perhatian Bapak Yudhistira dan rekan-rekan semua.*  
> *Wassalamu’alaikum Warahmatullahi Wabarakatuh."*

---
*Naskah ini dirancang khusus untuk memenuhi rubrik penilaian Tahap 1 RPL: waktu tepat 3–5 menit, isi komprehensif, berbasis standar IEEE 830 / ISO 29148, dan berfokus pada keunggulan teknis serta kepatuhan UU PDP.*

