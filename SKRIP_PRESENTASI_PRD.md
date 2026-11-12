# NASKAH SKRIP PRESENTASI PRD & SRS MANDIRI (DURASI <= 5 MENIT)
## SIWIL AI — Sistem Informasi Wilayah Terpadu Berbasis Kecerdasan Buatan
**Standar Acuan:** IEEE Std 830-1998 & ISO/IEC/IEEE 29148:2018  
**Mata Kuliah:** Rekayasa Perangkat Lunak (TIKB2503021) — STMIK IKMI Cirebon  
**Dosen Pengampu:** Yudhistira Arie Wijaya, M.Kom., CDS.  
**Nama Mahasiswa:** Muhammad Al Farel  
**NIM:** 41257124 | **Kelas:** TI 1B 2025  
**Target Durasi:** 4 Menit 00 Detik – 4 Menit 30 Detik (**Batas Maksimal: 5 Menit**)  
**Total Kata:** ~564 Kata (Kecepatan Bicara Standar: 125–135 kata/menit)  
**Dokumen Acuan:** `PRESENTASI_PRD_SIWIL.pptx` / `PRESENTASI_PRD_SIWIL.pdf`

---

### PANDUAN CEPAT PEREKAMAN VIDEO (MAHASISWA)
1. **Target Waktu:** Naskah ini telah dikalibrasi ketat untuk selesai pada menit ke **04:15 – 04:30**, sehingga dijamin aman di bawah batas 5 menit.
2. **Gaya Penyampaian:** Tenang, lugas, artikulasi jelas, tatap kamera secara berkala.
3. **Sinkronisasi Visual:** Pindahkan slide tepat saat narasi berpindah sesuai penanda `[TAMPILKAN SLIDE X]`.

---

## NASKAH LENGKAP KATA-DEMI-KATA (SLIDE-BY-SLIDE)

### SLIDE 1: JUDUL & IDENTITAS DIRI (00:00 – 00:25)
**Estimasi Durasi:** 25 Detik | **Jumlah Kata:** 61 kata  
**Tampilan Layar:** `[TAMPILKAN SLIDE 1 — Judul SIWIL AI, Nama: Muhammad Al Farel, NIM: 41257124, Kelas: TI 1B 2025]`

> *"Assalamu’alaikum Warahmatullahi Wabarakatuh.*  
> *Selamat pagi/siang kepada Bapak Yudhistira dan rekan-rekan sekalian.*  
> *Perkenalkan, saya **Muhammad Al Farel**, NIM **41257124**, kelas **TI 1B angkatan 2025**.*  
> 
> *Hari ini saya mempresentasikan luaran **Tahap 1: Product Requirements Document (PRD)** untuk proyek **SIWIL AI: Sistem Informasi Wilayah Terpadu Berbasis Kecerdasan Buatan**, yang disusun mengacu pada standar formal **IEEE Std 830** dan kepatuhan **Undang-Undang Perlindungan Data Pribadi**."*

---

### SLIDE 2: LATAR BELAKANG MASALAH (00:25 – 00:45)
**Estimasi Durasi:** 20 Detik | **Jumlah Kata:** 45 kata  
**Tampilan Layar:** `[PINDAH KE SLIDE 2 — 4 Kartu Masalah Birokrasi Wilayah]`

> *"Pengembangan SIWIL dilatarbelakangi empat masalah utama birokrasi wilayah:*  
> *Pertama, **rantai birokrasi persuratan fisik** yang lambat 2 hingga 5 hari kerja.*  
> *Kedua, **keterbatasan jam pelayanan** pengurus RT dan RW.*  
> *Ketiga, **kerentanan kebocoran berkas kependudukan manual** yang melanggar amanat UU PDP.*  
> *Serta keempat, **minimnya transparansi realisasi anggaran** APBDes dan pembangunan desa kepada masyarakat."*

---

### SLIDE 3: VISI & TUJUAN PRODUK (00:45 – 01:05)
**Estimasi Durasi:** 20 Detik | **Jumlah Kata:** 43 kata  
**Tampilan Layar:** `[PINDAH KE SLIDE 3 — Visi, Misi & 3 Pilar Sasaran Strategis]`

> *"SIWIL mengusung prinsip: **'Satu Data Wilayah, Satu Sistem Pemerintahan, Didukung AI'**.*  
> *Tiga sasaran utamanya adalah:*  
> *1. **Sentralisasi master data** kependudukan sebagai single source of truth.*  
> *2. **Transformasi layanan persuratan nirkertas 24 jam** dengan validasi Tanda Tangan Elektronik dan QR Code resmi.*  
> *3. Serta penyediaan **civic intelligence** melalui asisten kecerdasan artifisial."*

---

### SLIDE 4: PROFIL PENGGUNA & RBAC (01:05 – 01:30)
**Estimasi Durasi:** 25 Detik | **Jumlah Kata:** 48 kata  
**Tampilan Layar:** `[PINDAH KE SLIDE 4 — Matriks 5 Aktor Sistem & Partisi Hak Akses RBAC]`

> *"Sistem menerapkan **Role-Based Access Control** ketat pada 5 aktor utama:*  
> *1. **Publik**, untuk portal terbuka APBDes tanpa login.*  
> *2. **Warga Terverifikasi**, untuk layanan mandiri dan tombol darurat SOS.*  
> *3. **Ketua RT**, sebagai verifikator tahap 1.*  
> *4. **Ketua RW**, sebagai verifikator tahap 2.*  
> *Serta **Admin Desa**, untuk pengesahan surat ber-TTE resmi dan konfigurasi sistem terpusat."*

---

### SLIDE 5: KEPENDUDUKAN & ZERO FAKE ACCOUNT (01:30 – 01:50)
**Estimasi Durasi:** 20 Detik | **Jumlah Kata:** 45 kata  
**Tampilan Layar:** `[PINDAH KE SLIDE 5 — Pre-Enrollment NIK & Struktur Kartu Keluarga Digital]`

> *"Pada modul kependudukan, SIWIL menjamin **Zero Fake Account**.*  
> *Calon warga wajib menginput 16-digit NIK dan Nomor KK yang tervalidasi silang ke master database desa, lalu diverifikasi faktual oleh Ketua RT.*  
> *Sistem juga menyajikan **silsilah Kartu Keluarga digital** dan mencatat riwayat mutasi warga—baik kelahiran, kematian, maupun kepindahan—secara otomatis."*

---

### SLIDE 6: PERSURATAN 3-TIER & DIGITAL SIGNATURE (01:50 – 02:15)
**Estimasi Durasi:** 25 Detik | **Jumlah Kata:** 47 kata  
**Tampilan Layar:** `[PINDAH KE SLIDE 6 — Alur 4 Langkah Persuratan Berjenjang & Kotak Verifikasi QR Code]`

> *"Alur persuratan digital berjalan efisien dalam 4 langkah:*  
> *Pengajuan mandiri oleh warga, verifikasi Ketua RT, validasi Ketua RW, hingga pengesahan Kantor Desa.*  
> *Dokumen terbit otomatis dalam format **Vector PDF resmi ber-QR Code** dengan tanda tangan elektronik, yang dapat divalidasi keasliannya langsung oleh pihak eksternal tanpa perlu login ke sistem."*

---

### SLIDE 7: PARTISIPASI WARGA, GIS & TOMBOL SOS (02:15 – 02:40)
**Estimasi Durasi:** 25 Detik | **Jumlah Kata:** 47 kata  
**Tampilan Layar:** `[PINDAH KE SLIDE 7 — 3 Kolom: Aduan GIS, SOS Panic Button, dan Musyawarah E-Voting]`

> *"Untuk keselamatan dan partisipasi warga, SIWIL menyediakan 3 fitur unggulan:*  
> *Pertama, **Pengaduan warga berbasis peta GIS** disertai foto bukti dan titik koordinat GPS.*  
> *Kedua, **Tombol Darurat SOS** satu ketukan yang memancarkan sinyal darurat instan ke pengurus RT dan tetangga sekitar.*  
> *Serta fasilitas **E-Voting musyawarah warga** yang berasaskan 1 NIK 1 suara bebas manipulasi."*

---

### SLIDE 8: INOVASI UTAMA — SIWIL AI ENGINE (02:40 – 03:10)
**Estimasi Durasi:** 30 Detik | **Jumlah Kata:** 49 kata  
**Tampilan Layar:** `[PINDAH KE SLIDE 8 — 3 Pilar Arsitektur SIWIL AI & Guardrails UU PDP]`

> *"Pembeda utama sistem ini adalah **SIWIL AI Assistant** berbasis **Google Gemini**.*  
> *AI dibekali **12 native backend function tools** agar menjawab berdasarkan data nyata tanpa halusinasi, diproteksi **guardrails deterministik** yang menolak membocorkan data pribadi warga lain sesuai amanat **UU PDP No. 27 Tahun 2022**, serta didukung auto-rotation API key dan smart fallback."*

---

### SLIDE 9: TRANSPARANSI APBDES & OMNICHANNEL BOT (03:10 – 03:30)
**Estimasi Durasi:** 20 Detik | **Jumlah Kata:** 40 kata  
**Tampilan Layar:** `[PINDAH KE SLIDE 9 — Visualisasi APBDes & Layanan WhatsApp Bot]`

> *"SIWIL menyajikan portal grafik realisasi APBDes dan peta progres proyek pembangunan fisik secara terbuka kepada publik.*  
> *Selain itu, hadir integrasi **Omnichannel Bot via WhatsApp dan Telegram** untuk memudahkan warga lansia mengecek status berkas dan menerima pengumuman penting tanpa harus mengakses browser web."*

---

### SLIDE 10: NON-FUNCTIONAL REQUIREMENTS (ISO 25010) (03:30 – 03:50)
**Estimasi Durasi:** 20 Detik | **Jumlah Kata:** 44 kata  
**Tampilan Layar:** `[PINDAH KE SLIDE 10 — 4 Metrik Kualitas ISO/IEC 25010]`

> *"Mengacu standar kualitas **ISO/IEC 25010**, sistem dirancang dengan:*  
> *Respons halaman di bawah 1,5 detik, keamanan kriptografi password Argon2id dan hashing SHA-256 pada berkas resmi, jaminan ketersediaan uptime 99,5% dengan backup harian otomatis, serta antarmuka responsif mobile-first berstandar aksesibilitas WCAG 2.1 AA."*

---

### SLIDE 11: ROADMAP PENGEMBANGAN & TARGET KPI (03:50 – 04:10)
**Estimasi Durasi:** 20 Detik | **Jumlah Kata:** 49 kata  
**Tampilan Layar:** `[PINDAH KE SLIDE 11 — Fase P0, P1, P2 dan Indikator Kinerja Utama]`

> *"Pengembangan dibagi 3 fase: **P0** untuk MVP kependudukan dan persuratan, **P1** untuk ekspansi AI dan pemetaan GIS, serta **P2** untuk integrasi PWA offline-first dan sinkronisasi API Dukcapil nasional.*  
> *Target KPI utama adalah memangkas waktu pengurusan surat dari 3 hari menjadi **di bawah 15 menit**, dengan digitalisasi arsip di atas 90%."*

---

### SLIDE 12: PENUTUP & KESIAPAN TAHAP 2 (04:10 – 04:30)
**Estimasi Durasi:** 20 Detik | **Jumlah Kata:** 46 kata  
**Tampilan Layar:** `[PINDAH KE SLIDE 12 — Kesimpulan, Kesiapan Tahap 2 SDD/UML, dan Sesi Q&A]`

> *"Kesimpulannya, dokumen PRD SIWIL telah tuntas disusun dan siap menjadi landasan kokoh bagi **Tahap 2**, yaitu perancangan arsitektur perangkat lunak (SDD), 4 diagram UML Draw.io, ERD basis data, dan prototipe Figma.*  
> *Terima kasih atas perhatian Bapak Yudhistira dan rekan-rekan semua.*  
> *Wabillahi taufiq wal hidayah, Wassalamu’alaikum Warahmatullahi Wabarakatuh."*

---
*Naskah ini dijamin tuntas dalam rentang waktu **04:15 hingga 04:30 menit**, memberikan ruang aman 30–45 detik sebelum batas waktu maksimal 5 menit.*
