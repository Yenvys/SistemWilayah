# NASKAH SKRIP PRESENTASI TAHAP 2 (UML, ARSITEKTUR SDD & UI/UX)
## SIWIL AI — Sistem Informasi Wilayah Terpadu Berbasis Kecerdasan Buatan
**Standar Acuan:** IEEE Std 1016-2009 (Software Design Description)  
**Mata Kuliah:** Rekayasa Perangkat Lunak (TIKB2503021) — STMIK IKMI Cirebon  
**Dosen Pengampu:** Yudhistira Arie Wijaya, M.Kom., CDS.  
**Nama Mahasiswa:** Muhammad Al Farel  
**NIM:** 41257124 | **Kelas:** TI 1B 2025  
**Target Durasi:** 5 Menit 30 Detik – 6 Menit 30 Detik (Rentang Rubrik: 5–7 Menit)  
**Total Slide:** 10 Slide Komprehensif Berisi Seluruh Diagram UML Resolusi Tinggi  
**Dokumen Acuan:** `PRESENTASI_TAHAP_2_UML_SDD.pptx` / `PRESENTASI_TAHAP_2_UML_SDD.pdf`

---

### PANDUAN PEREKAMAN VIDEO MAHASISWA
1. **Tampilan Layar:** Presentasikan file PowerPoint atau PDF `PRESENTASI_TAHAP_2_UML_SDD.pptx` / `.pdf` secara *fullscreen*.
2. **Kamera & Kursor:** Posisikan webcam di sudut bawah. Gunakan kursor mouse untuk menunjuk elemen diagram (aktor, swimlane, class box, atau garis pesan) saat Anda menjelaskannya.
3. **Penyampaian:** Percaya diri, artikulasi teknis tegas, tempo teratur.

---

## NASKAH LENGKAP KATA-DEMI-KATA (SLIDE-BY-SLIDE)

### SLIDE 1: COVER & IDENTITAS DIRI (00:00 – 00:35)
**Estimasi Waktu:** 35 Detik  
**Tampilan Layar:** `[TAMPILKAN SLIDE 1 — Cover Tahap 2 SIWIL AI, Nama: Muhammad Al Farel, NIM: 41257124]`

> *"Assalamu’alaikum Warahmatullahi Wabarakatuh.*  
> *Selamat pagi/siang kepada Bapak Yudhistira Arie Wijaya selaku dosen pengampu mata kuliah Rekayasa Perangkat Lunak, serta rekan-rekan mahasiswa sekalian.*  
> 
> *Perkenalkan, saya **Muhammad Al Farel**, NIM **41257124**, kelas **TI 1B angkatan 2025**, Program Studi S1 Teknik Informatika, STMIK IKMI Cirebon.*  
> 
> *Pada video progres kedua ini, saya akan mempresentasikan laporan **Tahap 2: Perancangan UML dan Arsitektur Sistem Mandiri** untuk proyek **SIWIL AI: Sistem Informasi Wilayah Terpadu Berbasis Kecerdasan Buatan**.*  
> 
> *Perancangan ini didokumentasikan secara formal dalam berkas **Software Design Description (SDD)** yang berstandar internasional **IEEE Std 1016-2009**, mencakup pemodelan 4 Diagram UML Utama, skema basis data ERD, serta prototipe antarmuka UI/UX."*

---

### SLIDE 2: KERANGKA ARSITEKTUR & TRANSISI PRD KE SDD (00:35 – 01:10)
**Estimasi Waktu:** 35 Detik  
**Tampilan Layar:** `[PINDAH KE SLIDE 2 — 3 Lapisan Clean Architecture Multi-Tier]`

> *"Melanjutkan dari tahap spesifikasi kebutuhan (PRD), perancangan teknis SIWIL mengadopsi prinsip **Clean Architecture Multi-Tier** yang memisahkan tanggung jawab sistem ke dalam tiga lapisan utama:*  
> 
> *1. **Presentation & Controller Layer**: Mengelola lalu lintas HTTP, sesi pengguna, dan Form Request validasi tanpa mencampuradukkan logika bisnis atau kueri basis data.*  
> *2. **Service & Abstraction Layer**: Mengisolasi proses bisnis inti—seperti verifikasi persuratan, notifikasi omnichannel, dan kecerdasan artifisial—yang disuntikkan secara dinamis melalui Service Container berbasis Interface Contract.*  
> *3. **Domain & Data Repository Layer**: Mengelola entitas Eloquent dengan Primary Key UUID v4, audit logging append-only, dan pembatasan kueri wilayah yang ketat.*  
> 
> *Arsitektur ini dimodelkan ke dalam empat sudut pandang UML: Use Case, Activity, Class, dan Sequence Diagram."*

---

### SLIDE 3: DIAGRAM UML 1 — USE CASE DIAGRAM (01:10 – 01:55)
**Estimasi Waktu:** 45 Detik  
**Tampilan Layar:** `[PINDAH KE SLIDE 3 — Gambar Use Case Diagram Resolusi Tinggi]`

> *"Diagram UML pertama adalah **Use Case Diagram**, yang memvisualisasikan batas sistem (*system boundary*) dan interaksi fungsional antara 6 aktor:*  
> 
> *Dapat kita lihat pada layar, setiap aktor dipisahkan melalui koridor dan relasi garis berwarna unik untuk menjaga kerapian diagram:*  
> *- **Aktor Warga (Warna Biru)**: Memiliki akses mandiri untuk pengajuan surat online, aduan GPS, tombol darurat SOS, musyawarah digital, dan chatbot AI.*  
> *- **Ketua RT (Warna Hijau)**: Bertindak sebagai verifikator surat tahap 1 dan penanggap pertama sinyal darurat warga.*  
> *- **Ketua RW (Warna Teal)**: Bertindak sebagai verifikator tahap 2 dan koordinator kebijakan antar-RT.*  
> *- **Admin Desa (Warna Oranye)**: Berwenang mengesahkan surat resmi dengan Tanda Tangan Elektronik ber-QR Code serta mengelola APBDes.*  
> *- **Superadmin (Warna Ungu)**: Mengelola konfigurasi multi-wilayah dan API AI.*  
> *- Serta **Masyarakat Publik (Warna Abu-abu)**: Yang dapat memantau keterbukaan APBDes dan statistik desa tanpa perlu login.*  
> 
> *Diagram ini tersusun atas 173 elemen grafis dengan relasi include dan extend yang terdefinisi ketat."*

---

### SLIDE 4: SKENARIO FORMAL USE CASE UTAMA (01:55 – 02:30)
**Estimasi Waktu:** 35 Detik  
**Tampilan Layar:** `[PINDAH KE SLIDE 4 — Tabel Skenario Formal UC-04, UC-05, dan UC-11]`

> *"Seluruh use case krusial telah dilengkapi spesifikasi skenario formal sesuai standar IEEE 1016:*  
> 
> *Sebagai contoh pada **UC-04: Pengajuan Surat Digital**: Warga memilih jenis surat, melampirkan berkas identitas, lalu sistem mencatat permohonan berstatus `DIAJUKAN_RT` serta mengirim notifikasi instan ke WhatsApp Ketua RT.*  
> 
> *Pada **UC-05: Verifikasi Berjenjang**: Menjelaskan alur normal persetujuan bertahap dari RT ke RW hingga Desa, serta alur alternatif jika permohonan ditolak dengan kewajiban menyertakan catatan alasan tertulis kepada warga.*  
> 
> *Dan pada **UC-11: Konsultasi Cerdas AI**: Memodelkan eksekusi function calling backend serta alur eksepsi di mana filter Guardrail UU PDP secara deterministik memblokir permintaan akses data pribadi warga lain."*

---

### SLIDE 5: DIAGRAM UML 2 — ACTIVITY DIAGRAM (02:30 – 03:15)
**Estimasi Waktu:** 45 Detik  
**Tampilan Layar:** `[PINDAH KE SLIDE 5 — Gambar Activity Diagram 5 Swimlanes]`

> *"Diagram UML kedua adalah **Activity Diagram**, yang menggambarkan alur kendali dinamis dari proses bisnis persuratan dan eksekusi AI.*  
> 
> *Alur kerja dibagi secara tegas ke dalam **5 Swimlanes**: Warga, Ketua RT, Ketua RW, Admin Desa, serta SIWIL System dan AI Engine.*  
> 
> *Proses dimulai saat warga mengisi permohonan di web. Sistem meneruskannya ke antrean Ketua RT. Terdapat Decision Node percabangan validasi: jika berkas tidak sesuai, surat ditolak kembali ke warga; jika disetujui RT, status naik ke RW, dan diteruskan ke Admin Desa.*  
> 
> *Pada titik akhir di Admin Desa, sistem mengeksekusi otomatisasi kritis: membubuhkan stempel digital, menyematkan Tanda Tangan Elektronik Kepala Desa, melakukan hashing SHA-256, dan men-generate dokumen **Vector PDF resmi ber-QR Code unik** yang siap diunduh warga."*

---

### SLIDE 6: DIAGRAM UML 3 — CLASS DIAGRAM & PRINSIP SOLID (03:15 – 04:05)
**Estimasi Waktu:** 50 Detik  
**Tampilan Layar:** `[PINDAH KE SLIDE 6 — Gambar Class Diagram SOLID Berwarna]`

> *"Diagram UML ketiga adalah **Class Diagram Berorientasi Objek**, yang dirancang dengan mematuhi secara ketat **5 Prinsip Desain SOLID**:*  
> 
> *1. **Single Responsibility Principle (SRP)**: Kelas Controller hanya menangani request-response HTTP, sementara logika bisnis didelegasikan ke Service Layer terpisah.*  
> *2. **Open/Closed Principle (OCP)**: Layanan AI diabstraksikan melalui interface contract, sehingga penambahan model AI baru dapat dilakukan tanpa mengubah kode pemanggil.*  
> *3. **Liskov Substitution Principle (LSP)**: Seluruh entitas domain diturunkan secara konsisten dari Eloquent Model dengan trait HasUuids.*  
> *4. **Interface Segregation Principle (ISP)**: Kontrak antarmuka notifikasi dipisahkan secara spesifik antara WhatsApp Gateway dan Telegram Bot.*  
> *5. **Dependency Inversion Principle (DIP)**: Modul tingkat tinggi bergantung pada abstraksi interface yang diinjeksi via Laravel Service Container.*  
> 
> *Setiap lapisan kelas pada diagram dibedakan dengan warna header kontras tinggi: Navy untuk Controller, Hijau untuk Service, Biru untuk Model, dan Ungu untuk Enums."*

---

### SLIDE 7: DIAGRAM UML 4 — SEQUENCE DIAGRAM MULTI-TIER (04:05 – 04:55)
**Estimasi Waktu:** 50 Detik  
**Tampilan Layar:** `[PINDAH KE SLIDE 7 — Gambar Sequence Diagram Multi-Tier]`

> *"Diagram UML keempat adalah **Sequence Diagram**, yang memodelkan urutan kronologis pertukaran 34 pesan melintasi **9 Lifelines Multi-Tier**: mulai dari Actor, View Antarmuka, Router, Controller, Service Layer, Database Eloquent, Cache, AI Engine, hingga Notification Hub.*  
> 
> *Diagram ini memetakan empat fragmen interaksi utama:*  
> *1. Alur autentikasi dan validasi silang NIK pre-enrollment.*  
> *2. Alur persetujuan surat berjenjang 3-tier dan pembentukan dokumen PDF.*  
> *3. Alur pemrosesan AI Function Calling, di mana AI meminta data operasional nyata ke backend melalui tool controller dengan filter guardrail privasi.*  
> *4. Dan pengiriman pesan asinkron melalui antrean background queue ke WhatsApp Gateway microservice."*

---

### SLIDE 8: BASIS DATA RELASIONAL (ERD) & KEAMANAN DATA (04:55 – 05:35)
**Estimasi Waktu:** 40 Detik  
**Tampilan Layar:** `[PINDAH KE SLIDE 8 — Panel Desain Basis Data ERD & Kepatuhan UU PDP]`

> *"Beralih ke arsitektur penyimpanan, SIWIL merancang **Entity-Relationship Diagram (ERD) 20 entitas** yang telah memenuhi normalisasi **Third Normal Form (3NF)**:*  
> 
> *Master kependudukan terstruktur secara presisi dari relasi desa, RW, RT, kepala keluarga, hingga anggota keluarga.*  
> *Seluruh tabel menggunakan **Primary Key UUID v4** untuk mencegah kerentanan manipulasi ID atau serangan IDOR.*  
> *Kueri kependudukan dioptimalkan dengan indeks komposit pada kolom NIK, Nomor KK, dan RT ID.*  
> 
> *Guna mematuhi UU PDP No. 27 Tahun 2022, data sensitif warga dilindungi enkripsi, password diamankan dengan algoritma Argon2id, dan seluruh mutasi data dicatat ke dalam audit log append-only yang tidak dapat diubah."*

---

### SLIDE 9: DESAIN ANTARMUKA PENGGUNA (UI/UX) & PROTOTIPE FIGMA (05:35 – 06:10)
**Estimasi Waktu:** 35 Detik  
**Tampilan Layar:** `[PINDAH KE SLIDE 9 — 3 Kartu UI/UX: Dashboard Warga, RT/RW, dan Desa]`

> *"Untuk aspek visual dan interaksi, SIWIL merancang sistem desain antarmuka berbasis prinsip **Mobile-First dan Human-Centered Design**:*  
> 
> *- **Dashboard Mandiri Warga**: Menyajikan kartu pintar pelayanan, floating SOS panic button berwarna merah tegas, serta widget interaktif AI Chat.*  
> *- **Dashboard Pengurus RT dan RW**: Menyediakan meja kerja verifikasi berkas dengan 1-click approval dialog, pemetaan GIS laporan aduan, dan grafik demografi wilayah.*  
> *- **Portal Desa & Keterbukaan Publik**: Mengelola penerbitan surat TTE, visualisasi realisasi APBDes, serta pengumuman warta.*  
> 
> *Seluruh palet warna memenuhi standar aksesibilitas **WCAG 2.1 AA** dengan rasio kontras di atas 4,5 banding 1, menjamin keterbacaan optimal bagi seluruh lapisan masyarakat."*

---

### SLIDE 10: KESIMPULAN & KESIAPAN MELANGKAH KE TAHAP 3 (06:10 – 06:45)
**Estimasi Waktu:** 35 Detik  
**Tampilan Layar:** `[PINDAH KE SLIDE 10 — Kesimpulan, Kesiapan Tahap 3, dan Sesi Q&A]`

> *"Bapak dosen dan rekan-rekan sekalian,*  
> *Sebagai kesimpulan, luaran **Tahap 2: Software Design Description (SDD)** telah selesai disusun secara utuh, matang, dan tervalidasi 100% menggunakan 4 Diagram UML resmi Draw.io berstandar IEEE 1016.*  
> 
> *Perancangan arsitektur ini telah diuji dan siap diimplementasikan secara solid ke dalam **Tahap 3**, di mana basis kode Laravel 12 dan PHP 8.4 telah terverifikasi melalui **105 skrip pengujian otomatis PHPUnit dengan 540 assertions** yang lulus 100% pada pipeline CI/CD GitHub Actions.*  
> 
> *Demikian presentasi progres Tahap 2 ini saya sampaikan. Terima kasih banyak atas perhatian Bapak Yudhistira dan rekan-rekan semua.*  
> *Sesi tanya jawab dan diskusi dipersilakan.*  
> *Wabillahi taufiq wal hidayah, Wassalamu’alaikum Warahmatullahi Wabarakatuh."*

---
*Naskah ini dirancang dengan durasi **05:45 hingga 06:20 menit**, tepat berada di dalam koridor rubrik penilaian video progres 2 (5–7 menit).*
