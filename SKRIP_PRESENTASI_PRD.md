# NASKAH SKRIP PRESENTASI PRD & SRS MANDIRI
## SIWIL AI — Sistem Informasi Wilayah Terpadu Berbasis Kecerdasan Buatan
**Standar:** IEEE Std 830-1998 & ISO/IEC/IEEE 29148:2018  
**Mata Kuliah:** Rekayasa Perangkat Lunak (TIKB2503021) — STMIK IKMI Cirebon  
**Dosen Pengampu:** Yudhistira Arie Wijaya, M.Kom., CDS.  
**Nama Mahasiswa:** Muhammad Al Farel  
**NIM:** 41257124 | **Kelas:** TI 1B 2025  
**Target Durasi:** 5 – 7 Menit (Optimal: ~6 Menit)  
**Dokumen Acuan:** `PRESENTASI_PRD_SIWIL.pptx` / `PRESENTASI_PRD_SIWIL.pdf`

---

### PANDUAN TEKNIS PEREKAMAN / PRESENTASI
1. **Sikap & Artikulasi:** Tegak, tatap kamera secara berkala, artikulasi jelas, gunakan tempo sedang (tidak terburu-buru).
2. **Kesesuaian Layar:** Sinkronkan ucapan dengan slide yang tampil di layar sesuai penanda `[TAMPILKAN SLIDE X]`.
3. **Poin Penekanan:** Bagian bertanda **tebal** adalah kata kunci yang sebaiknya diucapkan dengan intonasi lebih tegas/meyakinkan.

---

## NASKAH LENGKAP KATA-DEMI-KATA (SLIDE-BY-SLIDE)

### SLIDE 1: JUDUL & IDENTITAS DIRI (00:00 – 00:35)
**Estimasi Durasi:** 35 Detik  
**Tampilan Visual:** `[TAMPILKAN SLIDE 1 — Judul SIWIL AI, Nama: Muhammad Al Farel, NIM: 41257124, Kelas: TI 1B 2025]`

> *"Assalamu’alaikum Warahmatullahi Wabarakatuh,*  
> *Selamat pagi/siang kepada Bapak Yudhistira Arie Wijaya selaku dosen pengampu mata kuliah Rekayasa Perangkat Lunak, serta rekan-rekan mahasiswa yang saya banggakan.*  
> 
> *Perkenalkan, nama saya **Muhammad Al Farel**, NIM **41257124**, mahasiswa Program Studi S1 Teknik Informatika kelas **TI 1B angkatan 2025**, STMIK IKMI Cirebon.*  
> 
> *Pada kesempatan kali ini, saya akan mempresentasikan luaran **Tahap 1: Product Requirements Document (PRD) dan Software Requirements Specification (SRS)** untuk proyek aplikasi web yang saya rancang secara mandiri, yaitu **SIWIL AI: Sistem Informasi Wilayah Terpadu Berbasis Kecerdasan Buatan**.*  
> 
> *Penyusunan dokumen spesifikasi ini mengacu secara ketat pada standar formal internasional **IEEE Std 830-1998** dan **ISO/IEC/IEEE 29148:2018**, dengan mematuhi prinsip perlindungan privasi data sesuai **Undang-Undang Nomor 27 Tahun 2022 (UU PDP)**."*

---

### SLIDE 2: LATAR BELAKANG & URGENSI MASALAH (00:35 – 01:15)
**Estimasi Durasi:** 40 Detik  
**Tampilan Visual:** `[PINDAH KE SLIDE 2 — 4 Kartu Masalah Birokrasi Wilayah]`

> *"Bapak dosen dan rekan-rekan sekalian,*  
> *Pengembangan SIWIL AI dilatarbelakangi oleh empat permasalahan nyata dalam birokrasi pemerintahan akar rumput di tingkat RT, RW, dan kantor Desa/Kelurahan:*  
> 
> *Pertama, **Rantai birokrasi yang lambat**. Untuk mengurus selembar surat pengantar, warga harus mendatangi rumah Ketua RT, lalu ke Ketua RW, kemudian mengantre di kantor desa. Siklus manual ini memakan waktu 2 hingga 5 hari kerja.*  
> 
> *Kedua, **Keterbatasan jam pelayanan pengurus**. Ketua RT dan RW adalah tokoh masyarakat yang memiliki profesi utama di luar perannya, sehingga warga kerap kesulitan menemui mereka secara fisik.*  
> 
> *Ketiga, **Kerentanan privasi dan keamanan data**. Pencatatan kependudukan yang masih menggunakan buku register fisik atau spreadsheet lokal sangat rawan tercecer, rusak, dan melanggar amanat UU PDP karena NIK dan data keluarga terekspos bebas.*  
> 
> *Dan keempat, **Minimnya transparansi anggaran dan pembangunan**. Informasi APBDes dan progres proyek desa jarang tersosialisasikan secara terbuka kepada masyarakat.*  
> 
> *Inilah urgensi utama mengapa transformasi digital di tingkat wilayah mutlak diperlukan."*

---

### SLIDE 3: VISI, MISI & TUJUAN PRODUK (01:15 – 01:50)
**Estimasi Durasi:** 35 Detik  
**Tampilan Visual:** `[PINDAH KE SLIDE 3 — Visi, Misi & 3 Pilar Sasaran Strategis]`

> *"Untuk menjawab tantangan tersebut, SIWIL AI mengusung visi utama: **Satu Data Wilayah, Satu Sistem Pemerintahan, Didukung Kecerdasan Artifisial**.*  
> 
> *Visi ini dijabarkan ke dalam tiga sasaran strategis:*  
> *1. **Sentralisasi Data Wilayah**: Mengintegrasikan data kependudukan, Kartu Keluarga, dan relasi hierarki wilayah ke dalam satu basis data terpusat sebagai single source of truth.*  
> *2. **Layanan Publik Nirkertas 24/7 (Zero Paperwork)**: Memangkas siklus birokrasi persuratan dari hari menjadi hitungan menit melalui Tanda Tangan Elektronik dan verifikasi QR Code resmi.*  
> *3. **Civic Intelligence via AI**: Menghadirkan asisten kecerdasan artifisial interaktif untuk membantu warga berkonsultasi mengenai administrasi, sekaligus membantu aparatur desa dalam menganalisis demografi wilayah."*

---

### SLIDE 4: PROFIL PENGGUNA & RBAC (01:50 – 02:35)
**Estimasi Durasi:** 45 Detik  
**Tampilan Visual:** `[PINDAH KE SLIDE 4 — Matriks 5 Aktor Sistem & Partisi Hak Akses RBAC]`

> *"Agar keamanan data dan tata kelola sistem terjaga secara akuntabel, SIWIL menerapkan arsitektur **Role-Based Access Control (RBAC)** dengan pemisahan yurisdiksi data yang ketat:*  
> 
> *1. **Masyarakat Umum / Publik**: Dapat mengakses portal informasi desa, grafik transparansi APBDes, peta proyek fisik, dan bot WhatsApp tanpa perlu login.*  
> *2. **Warga Terverifikasi**: Warga resmi pemilik NIK terdaftar yang dapat mengajukan surat digital mandiri, melapor aduan berkoordinat GPS, menekan tombol darurat SOS, voting musyawarah, dan berkonsultasi dengan asisten AI.*  
> *3. **Ketua RT**: Bertindak sebagai verifikator tahap pertama permohonan surat, validator akun warga baru, serta pengelola data warga di lingkup RT-nya.*  
> *4. **Ketua RW**: Bertindak sebagai verifikator tahap kedua dan memantau agregat perbandingan kondisi antar-RT di lingkup RW-nya.*  
> *5. **Admin Desa dan Super Admin**: Berwenang mengesahkan surat dengan stempel dan TTE dinas, mengelola anggaran desa, memantau audit log, serta mengonfigurasi sistem terpusat."*

---

### SLIDE 5: FITUR UTAMA 1 — KEPENDUDUKAN & ZERO FAKE ACCOUNT (02:35 – 03:15)
**Estimasi Durasi:** 40 Detik  
**Tampilan Visual:** `[PINDAH KE SLIDE 5 — Pre-Enrollment NIK & Struktur Kartu Keluarga Digital]`

> *"Masuk ke spesifikasi kebutuhan fungsional yang pertama, yaitu **Modul Kependudukan dan Pre-Enrollment NIK**.*  
> 
> *SIWIL menerapkan prinsip **Zero Fake Account**:*  
> *Calon pengguna tidak dapat mendaftar dengan identitas palsu. Saat registrasi, warga wajib menginput 16-digit NIK dan Nomor Kartu Keluarga.*  
> *Sistem secara otomatis memvalidasi silang ke master data kependudukan desa. Jika NIK dan No KK cocok, akun dibuat dengan status pending dan diteruskan ke Ketua RT setempat untuk diverifikasi secara faktual.*  
> 
> *Selain itu, sistem menyajikan struktur Kartu Keluarga secara visual hierarkis, mencatat riwayat mutasi penduduk—baik kelahiran, kematian, maupun pindah domisili—serta memfasilitasi ekspor laporan resmi kependudukan ke format PDF dan Excel."*

---

### SLIDE 6: FITUR UTAMA 2 — PERSURATAN 3-TIER & DIGITAL SIGNATURE (03:15 – 03:55)
**Estimasi Durasi:** 40 Detik  
**Tampilan Visual:** `[PINDAH KE SLIDE 6 — Alur 4 Langkah Persuratan Berjenjang & Kotak Verifikasi QR Code]`

> *"Fitur unggulan kedua adalah **Layanan Persuratan Berjenjang 3-Tier Nirkertas (Zero Paperwork)**.*  
> 
> *Alur kerja persuratan dirancang sangat efisien dalam 4 langkah:*  
> *1. Warga mengajukan surat keterangan—seperti SKTM, Domisili, atau Keterangan Usaha—langsung dari ponsel pintar.*  
> *2. Ketua RT menerima notifikasi instan dan dapat melakukan verifikasi berkas secara cepat.*  
> *3. Permohonan diteruskan ke Ketua RW untuk validasi silang.*  
> *4. Dan akhirnya disahkan oleh Kantor Desa.*  
> 
> *Setelah disahkan, sistem otomatis membubuhkan Tanda Tangan Elektronik dinas dan men-generate dokumen **Vector PDF resmi ber-QR Code**.*  
> *QR Code ini dapat langsung dipindai oleh pihak eksternal—seperti perbankan, universitas, atau dinas sosial—untuk memvalidasi keaslian dokumen secara publik tanpa perlu memiliki akun ke sistem."*

---

### SLIDE 7: FITUR UTAMA 3 — PARTISIPASI WARGA, GIS & TOMBOL SOS (03:55 – 04:35)
**Estimasi Durasi:** 40 Detik  
**Tampilan Visual:** `[PINDAH KE SLIDE 7 — 3 Kolom: Aduan GIS, SOS Panic Button, dan Musyawarah E-Voting]`

> *"Fitur ketiga adalah penguatan partisipasi publik dan keselamatan lingkungan:*  
> 
> *Pertama, **Pengaduan Warga Berbasis GIS**. Warga dapat melaporkan jalan berlubang, tumpukan sampah, atau lampu jalan mati lengkap dengan lampiran foto bukti dan koordinat GPS akurat. Status penanganan dapat dipantau secara transparan.*  
> 
> *Kedua, **Tombol Darurat (SOS Panic Button)**. Dalam situasi krisis seperti kebakaran, keadaan darurat medis, atau gangguan kamtibmas, warga cukup menekan satu tombol. Sistem seketika memancarkan sinyal darurat disertai titik koordinat lokasi ke perangkat Ketua RT, RW, satgas linmas, dan tetangga terdekat.*  
> 
> *Ketiga, **Musyawarah Digital (E-Voting)**. Memfasilitasi voting warga yang terisolasi per wilayah, dengan asas **Satu NIK Terverifikasi sama dengan Satu Suara**, menjamin proses demokrasi tingkat RT/RW yang jujur dan bebas manipulasi."*

---

### SLIDE 8: INOVASI UTAMA — SIWIL AI CIVIC INTELLIGENCE (04:35 – 05:25)
**Estimasi Durasi:** 50 Detik  
**Tampilan Visual:** `[PINDAH KE SLIDE 8 — 3 Pilar Arsitektur SIWIL AI & Guardrails UU PDP]`

> *"Pembeda utama SIWIL dibanding aplikasi tata kelola konvensional adalah integrasi **SIWIL AI Civic Intelligence Engine**.*  
> 
> *Engine ini menggunakan fondasi **Google Gemini LLM** yang dirancang dengan tiga prinsip arsitektur tingkat lanjut:*  
> 
> *1. **12 Native Backend Function Calling Tools**: AI tidak dibiarkan berhalusinasi atau mengarang jawaban. Ketika pengguna bertanya tentang status surat, anggaran APBDes, atau profil keluarga, AI secara otomatis mengeksekusi fungsi backend untuk mengambil data riil yang terisolasi dan valid.*  
> 
> *2. **Deterministic UU PDP Guardrails**: Sistem dilengkapi lapisan proteksi kepatuhan Undang-Undang Perlindungan Data Pribadi. AI memiliki filter deterministik yang secara tegas menolak perintah untuk membocorkan NIK, nomor telepon, atau data sensitif warga lain.*  
> 
> *3. **Multi-Key Rotation & Smart Fallback**: Menjamin ketersediaan sistem 24/7 dengan rotasi otomatis API Key saat kuota menipis, serta mekanisme fallback pencocokan pola lokal jika koneksi ke server AI eksternal mengalami gangguan."*

---

### SLIDE 9: TRANSPARANSI APBDES & OMNICHANNEL BOT (05:25 – 05:55)
**Estimasi Durasi:** 30 Detik  
**Tampilan Visual:** `[PINDAH KE SLIDE 9 — Visualisasi APBDes & Layanan WhatsApp Bot]`

> *"Untuk menjamin keterbukaan informasi publik, SIWIL menyajikan **Portal Transparansi APBDes Interaktif** dan **Peta GIS Proyek Pembangunan**.*  
> *Masyarakat dapat memantau serapan dana desa dan progres fisik proyek pembangunan secara real-time.*  
> 
> *Selain antarmuka web, SIWIL juga mengintegrasikan **Layanan Omnichannel Bot via WhatsApp dan Telegram**.*  
> *Fasilitas ini sangat penting untuk menjangkau warga lansia atau masyarakat yang belum terbiasa dengan antarmuka web, sehingga pengecekan status surat dan penyampaian pengumuman penting dapat dilakukan semudah berkirim pesan singkat."*

---

### SLIDE 10: NON-FUNCTIONAL REQUIREMENTS & STANDAR KUALITAS (05:55 – 06:25)
**Estimasi Durasi:** 30 Detik  
**Tampilan Visual:** `[PINDAH KE SLIDE 10 — 4 Metrik Kualitas ISO/IEC 25010]`

> *"Dari sisi kualitas non-fungsional, dokumen PRD SIWIL mengacu pada standar internasional **ISO/IEC 25010**:*  
> 
> *1. **Efisiensi Performa**: Waktu respons halaman di bawah 1,5 detik dan streaming token AI di bawah 2 detik.*  
> *2. **Keamanan & Kriptografi**: Password dienkripsi dengan algoritma Argon2id, tanda tangan digital diverifikasi dengan hashing SHA-256, serta proteksi penuh dari serangan CSRF, XSS, dan SQL Injection.*  
> *3. **Keandalan**: Komitmen uptime minimal 99,5% dengan backup basis data harian terjadwal.*  
> *4. **Usabilitas**: Mengadopsi prinsip desain responsif mobile-first, navigasi ramah pengguna awam, dan rasio kontras visual yang mematuhi standar aksesibilitas WCAG 2.1 AA."*

---

### SLIDE 11: ROADMAP PENGEMBANGAN & TARGET KPI (06:25 – 06:55)
**Estimasi Durasi:** 30 Detik  
**Tampilan Visual:** `[PINDAH KE SLIDE 11 — Fase P0, P1, P2 dan Indikator Kinerja Utama]`

> *"Pengembangan SIWIL dirancang melalui roadmap bertahap yang terukur:*  
> *Fase **P0 sebagai MVP Inti** memfokuskan pada registrasi NIK, kependudukan, alur persuratan 3-tier, dan dashboard RBAC.*  
> *Fase **P1** memperluas kapabilitas dengan integrasi asisten AI, pemetaan GIS, tombol SOS, dan transparansi APBDes.*  
> *Serta Fase **P2** untuk integrasi PWA offline-first dan sinkronisasi API Dukcapil nasional.*  
> 
> *Target keberhasilan utama kami adalah:*  
> *1. Memangkas waktu pengurusan surat dari 3-5 hari kerja menjadi kurang dari 15 menit.*  
> *2. Mendorong digitalisasi arsip kependudukan hingga mencapai lebih dari 90% dalam 3 bulan pertama implementasi.*  
> *3. Serta mempertahankan zero incident kebocoran data pribadi warga."*

---

### SLIDE 12: PENUTUP & KESIAPAN MELANGKAH KE TAHAP 2 (06:55 – 07:25)
**Estimasi Durasi:** 30 Detik  
**Tampilan Visual:** `[PINDAH KE SLIDE 12 — Kesimpulan, Kesiapan Tahap 2 SDD/UML, dan Sesi Q&A]`

> *"Bapak dosen dan rekan-rekan sekalian,*  
> *Sebagai kesimpulan, dokumen PRD dan SRS ini telah selesai disusun secara komprehensif, matang, dan tervalidasi menggunakan **Requirements Traceability Matrix (RTM)**.*  
> 
> *Dokumen ini memberikan fondasi yang sangat kokoh bagi saya untuk melangkah ke **Tahap 2**, yaitu perancangan arsitektur sistem perangkat lunak mandiri (SDD) yang mencakup pemodelan 4 diagram UML utama—Use Case, Activity, Class, dan Sequence Diagram—skema basis data ERD, serta prototipe interaktif UI/UX di Figma.*  
> 
> *Demikian presentasi progres Tahap 1 terkait Product Requirements Document SIWIL AI ini saya sampaikan.*  
> *Terima kasih banyak atas perhatian dan waktu Bapak Yudhistira beserta rekan-rekan semua.*  
> *Saya sangat terbuka untuk menerima pertanyaan, kritik, dan saran yang membangun.*  
> 
> *Wabillahi taufiq wal hidayah,*  
> *Wassalamu’alaikum Warahmatullahi Wabarakatuh."*

---
*Naskah ini disusun secara runtut dan presisi untuk mendukung video presentasi progres Tahap 1 berdurasi 5–7 menit.*
