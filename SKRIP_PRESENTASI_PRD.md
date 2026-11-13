# NASKAH SKRIP PRESENTASI PRD & SRS MANDIRI (7 SLIDE RINGKAS)
## SIWIL AI — Sistem Informasi Wilayah Terpadu Berbasis Kecerdasan Buatan
**Standar Acuan:** IEEE Std 830-1998 & ISO/IEC/IEEE 29148:2018  
**Mata Kuliah:** Rekayasa Perangkat Lunak (TIKB2503021) — STMIK IKMI Cirebon  
**Dosen Pengampu:** Yudhistira Arie Wijaya, M.Kom., CDS.  
**Nama Mahasiswa:** Muhammad Al Farel  
**NIM:** 41257124 | **Kelas:** TI 1B 2025  
**Target Durasi:** 03 Menit 45 Detik – 04 Menit 15 Detik (**Batas Maksimal: 5 Menit**)  
**Total Slide:** 7 Slide Ringkas & Padat Visual  
**Total Kata:** ~460 Kata Spoken (Kecepatan Bicara: 120–130 kata/menit)  
**Dokumen Acuan:** `PRESENTASI_PRD_SIWIL.pptx` / `PRESENTASI_PRD_SIWIL.pdf`

---

### PANDUAN CEPAT PEREKAMAN VIDEO (MAHASISWA)
1. **Jumlah Slide Ringkas:** Hanya 7 slide, setiap slide bertahan 30–40 detik di layar sehingga Anda tidak perlu terburu-buru berpindah slide.
2. **Target Waktu Aman:** Naskah ini telah dikalibrasi ketat untuk selesai pada menit ke **03:50 – 04:10**, memberikan ruang jeda aman ~50 detik sebelum batas maksimal 5 menit.
3. **Gaya Penyampaian:** Tenang, percaya diri, intonasi jelas, tatap kamera secara berkala.

---

## NASKAH LENGKAP KATA-DEMI-KATA (7 SLIDE)

### SLIDE 1: JUDUL & IDENTITAS DIRI (00:00 – 00:25)
**Estimasi Waktu:** 25 Detik  
**Tampilan Layar:** `[TAMPILKAN SLIDE 1 — Cover SIWIL AI, Nama: Muhammad Al Farel, NIM: 41257124, Kelas: TI 1B 2025]`

> *"Assalamu’alaikum Warahmatullahi Wabarakatuh.*  
> *Selamat pagi/siang kepada Bapak Yudhistira Arie Wijaya dan rekan-rekan sekalian.*  
> *Saya **Muhammad Al Farel**, NIM **41257124**, kelas **TI 1B angkatan 2025**, Program Studi S1 Teknik Informatika, STMIK IKMI Cirebon.*  
> 
> *Pada video ini, saya mempresentasikan luaran **Tahap 1: Product Requirements Document (PRD)** untuk sistem **SIWIL AI (Sistem Informasi Wilayah Terpadu Berbasis Kecerdasan Buatan)**, yang disusun berstandar formal **IEEE Std 830** dan patuh pada regulasi Undang-Undang Perlindungan Data Pribadi."*

---

### SLIDE 2: MASALAH BIROKRASI WILAYAH & VISI SOLUSI (00:25 – 01:00)
**Estimasi Waktu:** 35 Detik  
**Tampilan Layar:** `[PINDAH KE SLIDE 2 — 4 Masalah Birokrasi & Visi 3 Pilar SIWIL]`

> *"SIWIL AI lahir dari empat persoalan birokrasi nyata di tingkat RT, RW, dan kantor desa:*  
> *Pertama, rantai persuratan manual yang lambat hingga 5 hari kerja.*  
> *Kedua, jam pelayanan pengurus yang sangat terbatas.*  
> *Ketiga, kerentanan buku register kependudukan fisik terhadap kebocoran data.*  
> *Dan keempat, minimnya transparansi anggaran APBDes.*  
> 
> *Solusinya, SIWIL mengusung visi: **'Satu Data Wilayah, Satu Tata Kelola, Didukung AI'**, menghadirkan pelayanan nirkertas 24 jam dengan sentralisasi data digital yang aman."*

---

### SLIDE 3: STRUKTUR PENGGUNA (RBAC) & KEPENDUDUKAN (01:00 – 01:35)
**Estimasi Waktu:** 35 Detik  
**Tampilan Layar:** `[PINDAH KE SLIDE 3 — Matriks 10 Aktor Sistem (6 Human & 4 Engine) & Panel Zero Fake Account]`

> *"SIWIL memodelkan **10 Aktor Sistem** yang terbagi dalam dua kelompok:*  
> *- **6 Aktor Manusia**: Publik, Warga terdaftar, Ketua RT, Ketua RW, Admin Desa, dan Super Admin.*  
> *- Serta **4 Aktor Sistem dan Gateway Eksternal**: SIWIL AI Agent, WhatsApp Bot, Telegram Bot, dan SMTP Mail System.*  
> 
> *Untuk modul kependudukan, kami menjamin prinsip **Zero Fake Account**: registrasi warga mewajibkan validasi silang 16-digit NIK dan Nomor KK ke database desa, diverifikasi faktual oleh RT setempat, serta memetakan struktur Kartu Keluarga digital dan mutasi penduduk secara real-time."*

---

### SLIDE 4: PERSURATAN 3-TIER & DIGITAL SIGNATURE (01:35 – 02:10)
**Estimasi Waktu:** 35 Detik  
**Tampilan Layar:** `[PINDAH KE SLIDE 4 — Alur 4 Langkah Persuratan Berjenjang & Verifikasi QR Code]`

> *"Layanan persuratan digital dirancang nirkertas melalui 4 tahap efisien: pengajuan mandiri warga via ponsel, persetujuan cepat Ketua RT, validasi Ketua RW, hingga pengesahan resmi Kantor Desa.*  
> 
> *Setiap surat yang disahkan otomatis dibubuhi **Tanda Tangan Elektronik** dan diterbitkan dalam format **Vector PDF ber-QR Code unik** berenkripsi SHA-256, yang dapat diverifikasi keasliannya secara instan oleh pihak eksternal seperti perbankan atau kampus tanpa perlu login."*

---

### SLIDE 5: CIVIC ENGAGEMENT, GIS & TRANSPARANSI (02:10 – 02:45)
**Estimasi Waktu:** 35 Detik  
**Tampilan Layar:** `[PINDAH KE SLIDE 5 — 3 Kartu: Aduan GIS, SOS Panic Button, dan APBDes Terbuka]`

> *"Untuk partisipasi warga dan tanggap darurat, SIWIL menyediakan tiga modul unggulan:*  
> *1. **Pengaduan GIS**: Pelaporan kerusakan fasilitas umum disertai bukti foto dan titik koordinat GPS presisi.*  
> *2. **Tombol Darurat SOS**: Satu ketukan darurat yang seketika memancarkan sinyal bahaya ke pengurus RT/RW dan satgas terdekat.*  
> *Dan 3. **Portal APBDes Terbuka**: Visualisasi anggaran desa dan proyek pembangunan, didukung musyawarah digital e-voting berasaskan 1 NIK 1 suara."*

---

### SLIDE 6: INOVASI AI ENGINE & STANDAR KUALITAS ISO 25010 (02:45 – 03:25)
**Estimasi Waktu:** 40 Detik  
**Tampilan Layar:** `[PINDAH KE SLIDE 6 — Arsitektur SIWIL AI & 4 Dimensi Non-Fungsional ISO 25010]`

> *"Nilai pembeda utama sistem ini adalah **SIWIL AI Assistant** bertenaga Google Gemini, yang dibekali **12 native backend tools** agar menjawab administrasi warga secara akurat tanpa halusinasi.*  
> *AI dilindungi **guardrails deterministik** yang menolak membocorkan NIK warga lain sesuai amanat UU PDP.*  
> 
> *Mengacu standar kualitas **ISO/IEC 25010**, SIWIL menjamin respon halaman di bawah 1,5 detik, keamanan password Argon2id, uptime 99,5%, serta antarmuka ramah smartphone berstandar aksesibilitas WCAG AA."*

---

### SLIDE 7: ROADMAP, KPI & KESIAPAN TAHAP 2 (03:25 – 03:55)
**Estimasi Waktu:** 30 Detik  
**Tampilan Layar:** `[PINDAH KE SLIDE 7 — Roadmap Rilis, Target KPI, Kesiapan SDD Tahap 2, dan Sesi Q&A]`

> *"Pengembangan SIWIL terbagi dalam 3 fase: MVP inti, ekspansi AI dan GIS, hingga integrasi PWA offline-first.*  
> *Target KPI utama kami adalah memangkas waktu pengurusan surat dari 3 hari menjadi **di bawah 15 menit**, dengan digitalisasi arsip di atas 90%.*  
> 
> *Dokumen PRD ini telah tuntas dan siap menjadi fondasi eksekusi **Tahap 2**, yaitu perancangan arsitektur sistem SDD IEEE 1016 dan diagram UML.*  
> 
> *Terima kasih atas perhatian Bapak Yudhistira dan rekan-rekan. Sesi tanya jawab dipersilakan.*  
> *Wabillahi taufiq wal hidayah, Wassalamu’alaikum Warahmatullahi Wabarakatuh."*

---

### RINGKASAN MONITORING DURASI PRESENTASI TAHAP 1 (MAKS. 5 MENIT)

| No. Slide | Topik Bahasan | Target Durasi | Akumulasi Waktu | Status Rubrik |
|:---:|:---|:---:|:---:|:---:|
| **Slide 1** | Judul & Identitas Mahasiswa | 00:25 | 00:25 | Sesuai Rubrik |
| **Slide 2** | Masalah Birokrasi Wilayah & Visi SIWIL | 00:35 | 01:00 | Sesuai Rubrik |
| **Slide 3** | Struktur Pengguna (RBAC) & Kependudukan | 00:35 | 01:35 | Sesuai Rubrik |
| **Slide 4** | Persuratan 3-Tier & Digital Signature TTE | 00:35 | 02:10 | Sesuai Rubrik |
| **Slide 5** | Civic Engagement, Aduan GIS & Transparansi | 00:35 | 02:45 | Sesuai Rubrik |
| **Slide 6** | Inovasi AI Engine & Kualitas ISO 25010 | 00:40 | 03:25 | Sesuai Rubrik |
| **Slide 7** | Roadmap, KPI, Kesiapan Tahap 2 & Sesi Q&A | 00:30 | **03:55** | **Sangat Aman (< 5 Menit)** |

*Naskah ini dirancang dengan total durasi pembacaan sekitar **03 Menit 50 Detik hingga 04 Menit 15 Detik**, memberikan ruang jeda aman sekitar 45–60 detik di bawah batas maksimal 5 menit.*
