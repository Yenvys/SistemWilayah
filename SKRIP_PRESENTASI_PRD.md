# NASKAH SKRIP PRESENTASI PRD & SRS MANDIRI (7 SLIDE RINGKAS)
## SIWIL AI — Sistem Informasi Wilayah Terpadu Berbasis Kecerdasan Buatan
**Standar Acuan:** IEEE Std 830-1998 & ISO/IEC/IEEE 29148:2018  
**Mata Kuliah:** Rekayasa Perangkat Lunak (TIKB2503021) — STMIK IKMI Cirebon  
**Dosen Pengampu:** Yudhistira Arie Wijaya, M.Kom., CDS.  
**Nama Mahasiswa:** Muhammad Al Farel  
**NIM:** 41257124 | **Kelas:** TI 1B 2025  
**Target Durasi:** 4 Menit 00 Detik – 4 Menit 25 Detik (**Batas Maksimal: 5 Menit**)  
**Total Slide:** 7 Slide Ringkas & Padat Visual  
**Total Kata:** ~570 Kata (Kecepatan Bicara: 125–135 kata/menit)  
**Dokumen Acuan:** `PRESENTASI_PRD_SIWIL.pptx` / `PRESENTASI_PRD_SIWIL.pdf`

---

### PANDUAN CEPAT PEREKAMAN VIDEO (MAHASISWA)
1. **Jumlah Slide Ringkas:** Hanya 7 slide, sehingga Anda tidak perlu terburu-buru memencet tombol *next slide*. Tiap slide bertahan 30–45 detik di layar.
2. **Target Waktu:** Naskah ini telah dikalibrasi ketat untuk selesai pada menit ke **04:15 – 04:25**, memberikan ruang aman 35–45 detik sebelum batas 5 menit.
3. **Gaya Penyampaian:** Tenang, percaya diri, intonasi jelas, tatap kamera secara berkala.

---

## NASKAH LENGKAP KATA-DEMI-KATA (7 SLIDE)

### SLIDE 1: JUDUL & IDENTITAS DIRI (00:00 – 00:25)
**Estimasi Waktu:** 25 Detik  
**Tampilan Layar:** `[TAMPILKAN SLIDE 1 — Cover SIWIL AI, Nama: Muhammad Al Farel, NIM: 41257124, Kelas: TI 1B 2025]`

> *"Assalamu’alaikum Warahmatullahi Wabarakatuh.*  
> *Selamat pagi/siang kepada Bapak Yudhistira Arie Wijaya dan rekan-rekan sekalian.*  
> *Perkenalkan, nama saya **Muhammad Al Farel**, NIM **41257124**, dari kelas **TI 1B angkatan 2025**, Program Studi S1 Teknik Informatika, STMIK IKMI Cirebon.*  
> 
> *Hari ini saya mempresentasikan luaran **Tahap 1: Product Requirements Document (PRD)** untuk proyek **SIWIL AI: Sistem Informasi Wilayah Terpadu Berbasis Kecerdasan Buatan**, yang disusun berstandar formal internasional **IEEE Std 830** dan patuh pada amanat **Undang-Undang Nomor 27 Tahun 2022 tentang Perlindungan Data Pribadi**."*

---

### SLIDE 2: MASALAH BIROKRASI WILAYAH & VISI SOLUSI (00:25 – 01:00)
**Estimasi Waktu:** 35 Detik  
**Tampilan Layar:** `[PINDAH KE SLIDE 2 — 4 Masalah Birokrasi & Visi 3 Pilar SIWIL]`

> *"Pengembangan SIWIL AI dilatarbelakangi empat permasalahan nyata pada birokrasi tingkat RT, RW, dan kantor Desa:*  
> *1. **Rantai birokrasi persuratan fisik yang lambat**, memakan waktu 2 hingga 5 hari kerja.*  
> *2. **Keterbatasan jam pelayanan pengurus RT/RW** yang memiliki profesi utama di luar perannya.*  
> *3. **Kerentanan kebocoran data kependudukan manual**, di mana buku register fisik rentan tercecer dan melanggar prinsip privasi UU PDP.*  
> *Serta 4. **Minimnya transparansi anggaran APBDes** kepada warga.*  
> 
> *Menjawab tantangan tersebut, SIWIL mengusung visi: **'Satu Data Wilayah, Satu Sistem Pemerintahan, Didukung AI'**, dengan tiga pilar: sentralisasi master data, layanan nirkertas 24 jam, dan kecerdasan buatan untuk analisis wilayah."*

---

### SLIDE 3: STRUKTUR PENGGUNA (RBAC) & KEPENDUDUKAN (01:00 – 01:40)
**Estimasi Waktu:** 40 Detik  
**Tampilan Layar:** `[PINDAH KE SLIDE 3 — Matriks 5 Aktor RBAC & Panel Zero Fake Account]`

> *"Untuk menjaga akuntabilitas, sistem menerapkan **Role-Based Access Control (RBAC)** ketat pada 5 aktor:*  
> *1. **Publik**, untuk portal statistik terbuka tanpa login.*  
> *2. **Warga Terverifikasi**, untuk permohonan surat mandiri, aduan GPS, dan tombol darurat.*  
> *3. **Ketua RT**, sebagai verifikator tahap 1 dan pengelola data warga RT.*  
> *4. **Ketua RW**, sebagai verifikator tahap 2 dan koordinator wilayah.*  
> *Serta **Admin Desa**, untuk pengesahan surat resmi dan pengelolaan sistem.*  
> 
> *Pada modul kependudukan, SIWIL menjamin prinsip **Zero Fake Account**:*  
> *Registrasi warga mewajibkan validasi silang 16-digit NIK dan Nomor KK langsung ke master database desa, lalu diverifikasi faktual oleh Ketua RT setempat. Sistem juga menyajikan silsilah Kartu Keluarga digital dan mencatat mutasi penduduk secara real-time."*

---

### SLIDE 4: PERSURATAN 3-TIER & DIGITAL SIGNATURE (01:40 – 02:20)
**Estimasi Waktu:** 40 Detik  
**Tampilan Layar:** `[PINDAH KE SLIDE 4 — Alur 4 Langkah Persuratan Berjenjang & Verifikasi QR Code]`

> *"Fitur persuratan digital dirancang nirkertas atau **zero paperwork** melalui 4 tahapan efisien:*  
> *Warga mengajukan permohonan dari smartphone, Ketua RT menerima notifikasi instan dan menyetujui berkas, diteruskan ke Ketua RW untuk validasi silang, hingga akhirnya disahkan oleh Kantor Desa.*  
> 
> *Setelah disahkan, sistem otomatis membubuhkan **Tanda Tangan Elektronik dinas** dan menerbitkan dokumen **Vector PDF resmi ber-QR Code unik** berstandar enkripsi SHA-256.*  
> *QR Code ini dapat dipindai oleh pihak luar—seperti perbankan, universitas, atau dinas sosial—untuk memverifikasi keaslian surat secara instan tanpa perlu akun login."*

---

### SLIDE 5: CIVIC ENGAGEMENT, GIS & TRANSPARANSI (02:20 – 03:00)
**Estimasi Waktu:** 40 Detik  
**Tampilan Layar:** `[PINDAH KE SLIDE 5 — 3 Kartu: Aduan GIS, SOS Panic Button, dan APBDes Terbuka]`

> *"Untuk meningkatkan partisipasi dan keselamatan warga, SIWIL menghadirkan tiga inovasi:*  
> *Pertama, **Pengaduan Warga Berbasis GIS**. Warga dapat melaporkan infrastruktur rusak lengkap dengan foto bukti dan titik koordinat GPS presisi.*  
> 
> *Kedua, **Tombol Darurat (SOS Panic Button)** satu ketukan. Dalam kondisi kritis, sinyal darurat seketika memancar disertai lokasi peta ke Ketua RT, RW, satgas, dan tetangga terdekat.*  
> 
> *Dan ketiga, **Portal Transparansi APBDes dan Proyek Pembangunan** berbasis peta spasial, didukung fitur musyawarah **E-Voting** berasaskan 1 NIK 1 suara, serta layanan chatbot WhatsApp untuk memudahkan warga senior."*

---

### SLIDE 6: INOVASI AI ENGINE & STANDAR KUALITAS ISO 25010 (03:00 – 03:45)
**Estimasi Waktu:** 45 Detik  
**Tampilan Layar:** `[PINDAH KE SLIDE 6 — Arsitektur SIWIL AI & 4 Dimensi Non-Fungsional ISO 25010]`

> *"Keunggulan pembeda utama sistem ini adalah **SIWIL AI Assistant** berbasis **Google Gemini**.*  
> *AI dibekali **12 native backend function tools** agar menjawab pertanyaan warga secara faktual berdasarkan data riil tanpa halusinasi.*  
> *Untuk mematuhi UU PDP, AI diperkuat **guardrails deterministik** yang menolak tegas membocorkan NIK atau data pribadi warga lain. Sistem juga mendukung multi-key auto-rotation dan smart fallback lokal jika jaringan terputus.*  
> 
> *Dari sisi kualitas perangkat lunak mengacu standar **ISO/IEC 25010**, sistem menjamin: waktu respons halaman di bawah 1,5 detik, enkripsi password Argon2id, ketersediaan uptime 99,5%, serta antarmuka responsif mobile-first berstandar aksesibilitas WCAG AA."*

---

### SLIDE 7: ROADMAP, KPI & KESIAPAN TAHAP 2 (03:45 – 04:20)
**Estimasi Waktu:** 35 Detik  
**Tampilan Layar:** `[PINDAH KE SLIDE 7 — Roadmap Rilis, Target KPI, Kesiapan SDD Tahap 2, dan Sesi Q&A]`

> *"Pengembangan SIWIL terbagi dalam 3 fase: **P0 untuk MVP inti**, **P1 untuk ekspansi AI dan GIS**, serta **P2 untuk aplikasi PWA offline-first dan integrasi Dukcapil nasional**.*  
> *Target KPI kami adalah memangkas waktu pemrosesan surat dari 3 hari menjadi **kurang dari 15 menit**, dengan digitalisasi arsip di atas 90%.*  
> 
> *Sebagai kesimpulan, dokumen PRD ini telah selesai disusun secara matang dan siap menjadi acuan eksekusi **Tahap 2**, yaitu perancangan arsitektur perangkat lunak (SDD) yang mencakup 4 diagram UML Draw.io, ERD basis data, dan prototipe Figma.*  
> 
> *Terima kasih atas perhatian Bapak Yudhistira dan rekan-rekan semua. Sesi tanya jawab dipersilakan.*  
> *Wabillahi taufiq wal hidayah, Wassalamu’alaikum Warahmatullahi Wabarakatuh."*

---
*Naskah 7 slide ringkas ini dijamin tuntas dalam rentang waktu **04:15 hingga 04:25 menit**, sangat ideal dan aman di bawah batas maksimal 5 menit.*
