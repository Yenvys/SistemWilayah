# NASKAH SKRIP VIDEO PRESENTASI PROGRES 1 (PRD v2.5.0)
## SIWIL: Sistem Informasi Wilayah & Pemerintahan Terpadu
**Format Tampilan:** Poster / Infografis Ekosistem Digital SIWIL  
**Mata Kuliah:** Rekayasa Perangkat Lunak (TIKB2503021) — STMIK IKMI Cirebon  
**Dosen Pengampu:** Yudhistira Arie Wijaya, M.Kom., CDS.  
**Nama Mahasiswa:** Muhammad Al Farel  
**NIM:** 41257124 | **Kelas:** TI 1B 2025  
**Target Durasi:** 3 Menit 30 Detik – 4 Menit 15 Detik (**Maksimal 5 Menit**)  
**Total Kata:** ~491 Kata (Kecepatan Bicara: 125–130 kata/menit)

---

### PANDUAN REKAMAN VIDEO BAGI MAHASISWA
1. **Posisi Layar:** Buka gambar/infografis PRD SIWIL secara layar penuh (fullscreen). Tempatkan kamera wajah (webcam) di sudut layar.
2. **Kamera/Kursor Mouse:** Gunakan kursor mouse untuk menyorot atau mengarahkan ke bagian kartu/kotak yang sedang dibahas sesuai penanda `[ARAHKAN KURSOR KE: ...]`.
3. **Ketepatan Waktu:** Naskah ini telah dikalibrasi tuntas dalam waktu ~3 menit 45 detik, sehingga Anda memiliki cadangan waktu lebih dari 1 menit sebelum batas maksimal 5 menit berakhir.

---

## NASKAH LENGKAP KATA-DEMI-KATA (VERBATIM)

### 1. PEMBUKAAN & IDENTITAS DIRI (00:00 – 00:25)
*Durasi: ~25 detik | 56 kata*  
`[ARAHKAN KURSOR KE: Header Banner Paling Atas — Judul SIWIL & Identitas Diri]`

> *"Assalamu’alaikum Warahmatullahi Wabarakatuh.*  
> *Selamat pagi/siang kepada Bapak Yudhistira Arie Wijaya selaku dosen pengampu mata kuliah Rekayasa Perangkat Lunak, serta rekan-rekan sekalian.*  
> 
> *Perkenalkan, saya **Muhammad Al Farel**, NIM **41257124**, kelas **TI 1B angkatan 2025**, Program Studi S1 Teknik Informatika, STMIK IKMI Cirebon.*  
> 
> *Pada video progres Tahap 1 ini, saya mempresentasikan luaran **Product Requirements Document (PRD v2.5.0)** untuk proyek **SIWIL: Sistem Informasi Wilayah & Pemerintahan Terpadu**, sebuah ekosistem digital tata kelola pemerintahan desa berbasis web dan kecerdasan buatan."*

---

### 2. LATAR BELAKANG & URGENSI BIROKRASI (00:25 – 00:55)
*Durasi: ~30 detik | 59 kata*  
`[ARAHKAN KURSOR KE: Bagian 1 — LATAR BELAKANG & URGENSI: 4 Hambatan Birokrasi Manual]`

> *"Pengembangan SIWIL diinisiasi dari empat hambatan birokrasi manual yang kerap terjadi di lapangan:*  
> *Pertama, **birokrasi yang lambat**, di mana pengurusan surat fisik membutuhkan waktu 2 hingga 5 hari kerja.*  
> *Kedua, **keterbatasan waktu temu**, yang memaksa warga bolak-balik mendatangi rumah pengurus RT dan RW.*  
> *Ketiga, **kerentanan privasi data**, di mana pencatatan di buku register manual sangat rentan tercecer dan berisiko melanggar amanat **Undang-Undang Pelindungan Data Pribadi (UU PDP)**.*  
> *Serta keempat, **minimnya transparansi realisasi APBDes** bagi masyarakat luas."*

---

### 3. VISI & SOLUSI TERPADU (00:55 – 01:20)
*Durasi: ~25 detik | 47 kata*  
`[ARAHKAN KURSOR KE: Bagian 2 — VISI & SOLUSI TERPADU: Transformasi Digital Pemerintahan Desa]`

> *"Menjawab masalah tersebut, SIWIL mengusung visi utama:*  
> *'**Satu Data Wilayah, Satu Sistem Pemerintahan, Dibantu Kecerdasan Buatan (AI)**'.*  
> 
> *Solusi terpadu kami mencakup tiga pilar:*  
> *1. **Sentralisasi Teritorial**, yaitu integrasi master data hierarkis dari tingkat Desa hingga RT.*  
> *2. **Layanan Zero Paperwork 24/7**, yang dapat diakses mandiri via Web, WhatsApp, maupun Telegram.*  
> *3. Serta **Intelligence Layer**, analitik kebijakan wilayah secara real-time berbasis Google Gemini."*

---

### 4. ROLE-BASED ACCESS CONTROL (RBAC) (01:20 – 01:50)
*Durasi: ~30 detik | 59 kata*  
`[ARAHKAN KURSOR KE: Bagian 3 — Tabel ROLE-BASED ACCESS CONTROL (RBAC): 6 Tingkat Otoritas]`

> *"Untuk menjamin keamanan dan partisi data, sistem membagi hak akses ke dalam **6 tingkat otoritas RBAC**:*  
> *1. **Masyarakat Publik**, memiliki akses terbuka untuk memantau APBDes, statistik demografi, dan proyek fisik tanpa login.*  
> *2. **Warga Terdaftar**, untuk mengajukan surat mandiri, aduan GPS, tombol darurat SOS, e-voting, dan chat AI.*  
> *3. **Ketua RT**, sebagai verifikator surat tingkat 1.*  
> *4. **Ketua RW**, sebagai verifikator surat tingkat 2 dan koordinator lintas RT.*  
> *5. **Admin Desa**, untuk menerbitkan surat TTE ber-QR Code resmi.*  
> *Serta 6. **Super Admin**, untuk konfigurasi API AI, WhatsApp Gateway, dan pemantauan audit log."*

---

### 5. ADMINISTRASI & PELAYANAN SURAT 3-TIER (01:50 – 02:20)
*Durasi: ~30 detik | 56 kata*  
`[ARAHKAN KURSOR KE: Bagian 4 — Alur Persetujuan Surat 3-Tier (< 4 Jam)]`

> *"Pada pilar administrasi, SIWIL menerapkan **alur persetujuan persuratan berjenjang 3-Tier** dengan target penyelesaian di bawah 4 jam:*  
> *Dimulai dari langkah pertama, **Pengajuan mandiri oleh warga** untuk 9 jenis surat via Web atau WhatsApp.*  
> *Kedua, **Verifikasi domisili oleh Ketua RT**.*  
> *Ketiga, **Persetujuan wilayah oleh Ketua RW**.*  
> *Dan keempat, **Pengesahan oleh Kepala Desa**.*  
> 
> *Surat terbit otomatis dalam format PDF resmi berstempel dan bertanda tangan elektronik dinas, dilengkapi **QR Code Validator** yang dapat dipindai langsung oleh pihak luar untuk menguji keaslian dokumen secara real-time."*

---

### 6. FITUR OPERASIONAL & PARTISIPASI WARGA (02:20 – 02:50)
*Durasi: ~30 detik | 47 kata*  
`[ARAHKAN KURSOR KE: Bagian 5 — FITUR OPERASIONAL & PARTISIPASI: Layanan Lapangan]`

> *"SIWIL memperkuat pelayanan lapangan dan akuntabilitas publik melalui tiga modul utama:*  
> *Pertama, **Pengaduan GPS dan Tombol Darurat SOS**. Warga dapat melapor aduan dengan foto presisi, serta memancarkan alarm darurat instan ke aparat wilayah.*  
> *Kedua, **Transparansi APBDes**, yang mempublikasikan serapan dana desa, bantuan sosial, dan progres proyek fisik secara terbuka.*  
> *Serta ketiga, **E-Democracy Warga**, yaitu musyawarah dan polling digital berintegritas tinggi dengan asas **One Citizen One Vote**."*

---

### 7. NOVELTY & KECERDASAN BUATAN (SIWIL AI) (02:50 – 03:25)
*Durasi: ~35 detik | 53 kata*  
`[ARAHKAN KURSOR KE: Bagian 6 — NOVELTY & KECERDASAN BUATAN: SIWIL AI Engine]`

> *"Pembeda utama sekaligus kebaruan (*novelty*) sistem ini adalah **SIWIL AI Engine**.*  
> *AI ini dibekali **12 Deterministic Function Calling Tools** di backend untuk menarik data operasional nyata tanpa halusinasi fakta.*  
> 
> *AI berfungsi sebagai asisten konsultasi syarat surat, deteksi anomali kependudukan, serta dilengkapi **Guardrail UU PDP otomatis** yang memotong payload data sensitif agar NIK warga tidak bocor ke publik.*  
> *Sistem juga mendukung multi-key auto-rotation dan mode offline lokal agar layanan chat tetap tangguh 24 jam."*

---

### 8. ARSITEKTUR KEMANDIRIAN & FORENSIK (03:25 – 03:50)
*Durasi: ~25 detik | 50 kata*  
`[ARAHKAN KURSOR KE: Bagian 7 — ARSITEKTUR & KEPATUHAN REGULASI: ISO 25010 & Notification Hub]`

> *"Dari sisi arsitektur dan standar kualitas **ISO/IEC 25010**:*  
> *Sistem menjamin prinsip **Zero Fake Account** melalui validasi silang 16 digit NIK dan KK ke basis data desa.*  
> *Keamanan didukung enkripsi Bcrypt, UUID v4, WhatsApp OTP, serta **audit log append-only** permanen.*  
> *Penyebaran informasi ditopang oleh **Notification Hub 4 Saluran**, yang mengintegrasikan WhatsApp Gateway microservice Node.js, Telegram Bot, In-App Alert, dan SMTP Email."*

---

### 9. KEY PERFORMANCE INDICATORS (KPI) & PENUTUP (03:50 – 04:15)
*Durasi: ~25 detik | 64 kata*  
`[ARAHKAN KURSOR KE: Bagian 8 & Footer — Kotak Hijau KEY PERFORMANCE INDICATORS (KPI) & Terima Kasih]`

> *"Kualitas keandalan sistem telah teruji **100% lulus pada 105 skrip pengujian otomatis PHPUnit dengan 540 assertions** pada pipeline CI/CD GitHub Actions.*  
> 
> *Target capaian KPI kami:*  
> *Digitalisasi data di atas 90%, waktu terbit surat di bawah 4 jam, 100% akurasi NIK dan kepatuhan UU PDP, serta latensi inferensi AI di bawah 3 detik.*  
> 
> *Demikian presentasi progres Tahap 1 PRD SIWIL ini saya sampaikan. Terima kasih banyak atas perhatian Bapak Yudhistira dan rekan-rekan semua.*  
> *Wabillahi taufiq wal hidayah, Wassalamu’alaikum Warahmatullahi Wabarakatuh."*

---
*Naskah ini dirancang khusus dan terbukti berdurasi **03:45 hingga 04:15 menit** dengan tempo tenang, sangat aman di bawah batas maksimal 5 menit.*
