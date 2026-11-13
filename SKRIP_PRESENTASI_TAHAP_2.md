# NASKAH SKRIP PRESENTASI TAHAP 2 (KHUSUS UML & UI/UX)
## SIWIL AI — Sistem Informasi Wilayah Terpadu Berbasis Kecerdasan Buatan
**Standar Acuan:** IEEE Std 1016-2009 (Software Design Description)  
**Mata Kuliah:** Rekayasa Perangkat Lunak (TIKB2503021) — STMIK IKMI Cirebon  
**Dosen Pengampu:** Yudhistira Arie Wijaya, M.Kom., CDS.  
**Nama Mahasiswa:** Muhammad Al Farel  
**NIM:** 41257124 | **Kelas:** TI 1B 2025  
**Target Durasi:** 05 Menit 45 Detik – 06 Menit 20 Detik (**Batas Maksimal: 7 Menit**)  
**Total Slide:** 7 Slide Terfokus (4 Diagram UML Resmi & Prototipe 4 Dashboard Nyata)  
**Dokumen Acuan:** `PRESENTASI_TAHAP_2_UML_SDD.pptx` / `PRESENTASI_TAHAP_2_UML_SDD.pdf`

---

### PANDUAN PEREKAMAN VIDEO PRESENTASI (MAKSIMAL 7 MENIT)
1. **Tampilan Layar:** Buka file `PRESENTASI_TAHAP_2_UML_SDD.pptx` atau `.pdf` secara *fullscreen* (layar penuh 16:9).
2. **Kamera & Kursor Mouse:** Posisikan webcam di pojok layar. Gunakan kursor mouse untuk menunjuk bagian diagram yang sedang dijelaskan (misalnya: menunjuk aktor, swimlane, nama kelas, atau alur pesan).
3. **Tempo Bicara:** Bicara dengan nada percaya diri, artikulasi teknis tegas, dan tempo wajar (sekitar 125–130 kata per menit) agar presentasi selesai dengan tenang pada kisaran menit ke **05:50 – 06:15** (sangat aman di bawah batas maksimal 7 menit).

---

## NASKAH LENGKAP KATA-DEMI-KATA (SLIDE-BY-SLIDE)

### SLIDE 1: COVER PRESENTASI & IDENTITAS DIRI (00:00 – 00:30)
**Estimasi Waktu:** 30 Detik  
**Tampilan Layar:** `[TAMPILKAN SLIDE 1 — Cover Tahap 2: Muhammad Al Farel, NIM: 41257124, STMIK IKMI Cirebon]`

> *"Assalamu’alaikum Warahmatullahi Wabarakatuh.*  
> *Selamat pagi/siang kepada Bapak Yudhistira Arie Wijaya, M.Kom., CDS. selaku dosen pengampu mata kuliah Rekayasa Perangkat Lunak, serta rekan-rekan sekalian.*  
> 
> *Perkenalkan, saya **Muhammad Al Farel**, NIM **41257124**, kelas **TI 1B angkatan 2025**, Program Studi S1 Teknik Informatika, STMIK IKMI Cirebon.*  
> 
> *Pada video progres kedua ini, saya mempresentasikan laporan **Tahap 2: Perancangan UML dan Desain Antarmuka UI/UX** untuk proyek sistem **SIWIL AI (Sistem Informasi Wilayah Terpadu Berbasis Kecerdasan Buatan)**.*  
> 
> *Presentasi ini berfokus pada pemodelan 4 Diagram UML resmi berstandar IEEE 1016 serta visualisasi nyata 4 dashboard sistem dalam batas waktu maksimal 7 menit. Mari kita mulai."*

---

### SLIDE 2: DIAGRAM UML 1 — USE CASE DIAGRAM & SYSTEM BOUNDARY (00:30 – 01:25)
**Estimasi Waktu:** 55 Detik  
**Tampilan Layar:** `[PINDAH KE SLIDE 2 — Diagram Use Case 173 Elemen & 6 Koridor Aktor]`

> *"Diagram UML pertama yang dirancang adalah **Use Case Diagram**, yang memodelkan batasan sistem (*system boundary*) dengan total **173 elemen visual** dan **6 aktor sistem**:*  
> 
> *- Pertama, **Masyarakat Publik**: Mengakses warta pengumuman dan transparansi APBDes secara terbuka tanpa login.*  
> *- Kedua, **Warga Terdaftar**: Mengakses layanan mandiri pengajuan surat, pelaporan aduan geotagging, polling musyawarah, tombol darurat SOS, dan asisten pintar SIWIL AI.*  
> *- Ketiga, **Ketua RT**: Verifikator tahap awal pengajuan surat warga dan penanggap darurat pertama lingkungan.*  
> *- Keempat, **Ketua RW**: Validator berjenjang surat pengantar dan pengawas wilayah antar-RT.*  
> *- Kelima, **Admin Desa**: Pengesah dokumen resmi dengan Tanda Tangan Elektronik ber-QR Code serta pengelola data master.*  
> *- Dan keenam, **SIWIL AI**: Sistem otomatisasi validasi OCR berkas serta pengirim notifikasi bot WhatsApp dan Telegram.*  
> 
> *Setiap aktor memiliki koridor visual berwarna unik sehingga seluruh relasi include autentikasi NIK dan extend rekomendasi AI dapat ditelusuri secara presisi tanpa tumpang tindih."*

---

### SLIDE 3: DIAGRAM UML 2 — ACTIVITY DIAGRAM & ALUR KONTROL (01:25 – 02:15)
**Estimasi Waktu:** 50 Detik  
**Tampilan Layar:** `[PINDAH KE SLIDE 3 — Diagram Aktivitas 5 Swimlanes: Warga, RT, RW, Desa, dan AI]`

> *"Diagram UML kedua adalah **Activity Diagram**, yang menggambarkan alur kendali dinamis pelayanan persuratan digital dalam **5 Swimlanes**:*  
> 
> *Proses dimulai saat Warga mengajukan surat secara online. Di latar belakang, SIWIL AI memvalidasi kelayakan berkas via teknologi OCR.*  
> 
> *Permohonan masuk ke antrean Ketua RT. Melalui Decision Node, jika berkas tidak sesuai, RT menolak dengan catatan revisi yang langsung diterima warga. Jika valid, status disetujui dan berlanjut ke Ketua RW untuk validasi berjenjang kedua.*  
> 
> *Setelah disetujui RW, berkas diterima Admin Desa untuk penomoran otomatis dan pembubuhan Tanda Tangan Elektronik Kepala Desa.*  
> 
> *Sistem kemudian menerbitkan file PDF resmi berstempel QR Code terenkripsi SHA-256 dan memicu notifikasi WhatsApp ke warga. Seluruh rantai birokrasi selesai secara digital tanpa tatap muka fisik."*

---

### SLIDE 4: DIAGRAM UML 3 — CLASS DIAGRAM & KEPATUHAN SOLID (02:15 – 03:15)
**Estimasi Waktu:** 60 Detik  
**Tampilan Layar:** `[PINDAH KE SLIDE 4 — Class Diagram 4 Layer & 5 Prinsip SOLID]`

> *"Diagram UML ketiga adalah **Class Diagram**, yang menyusun arsitektur berorientasi objek dalam 4 lapisan bersih dengan kepatuhan penuh terhadap **5 Prinsip SOLID**:*  
> 
> *- **Single Responsibility Principle (SRP)**: Controller hanya menangani request-response HTTP, logika bisnis diisolasi ke Service Layer seperti `SuratService`, dan manipulasi data diserahkan ke `SuratRepository`.*  
> *- **Open/Closed Principle (OCP)**: Layanan kecerdasan buatan diabstraksikan via `AIServiceInterface`, sehingga model AI dapat diperbarui tanpa mengubah kode bisnis inti.*  
> *- **Liskov Substitution Principle (LSP)**: Seluruh kelas domain turunan Eloquent mempertahankan kontrak perilaku entitas yang konsisten.*  
> *- **Interface Segregation Principle (ISP)**: Interface dirancang ramping dan spesifik peran, seperti `NotifiableInterface` dan `AuditableInterface`, tanpa membebani kelas klien.*  
> *- Dan **Dependency Inversion Principle (DIP)**: Lapisan Controller bergantung pada abstraksi interface yang diinjeksikan secara otomatis melalui Service Container Laravel."*

---

### SLIDE 5: DIAGRAM UML 4 — SEQUENCE DIAGRAM MULTI-TIER (03:15 – 04:15)
**Estimasi Waktu:** 60 Detik  
**Tampilan Layar:** `[PINDAH KE SLIDE 5 — Sequence Diagram 9 Lifelines & 34 Interaksi Pesan]`

> *"Diagram UML keempat adalah **Sequence Diagram**, yang memodelkan interaksi pertukaran pesan secara kronologis melalui **9 Lifelines Multi-Tier**: dari Actor Warga, Blade View, Router, Controller, Service Layer, Eloquent, PostgreSQL, AI Engine, hingga Notification Hub.*  
> 
> *Diagram ini mencakup 4 tahapan: autentikasi sesi NIK, validasi berkas via AI, persetujuan berjenjang RT/RW/Desa, dan penerbitan surat.*  
> 
> *Skenario alternatif (`alt`) menangani percabangan antara berkas valid versus penolakan dengan pesan revisi.*  
> 
> *Secara arsitektur, SIWIL memadukan pemrosesan sinkron dan asinkron: permintaan HTTP dijawab instan di bawah 2 detik, sedangkan kompilasi dokumen PDF TTE dan broadcast WhatsApp diproses di latar belakang menggunakan antrean Queue Job agar sistem tetap ringan dan tanggap."*

---

### SLIDE 6: DESAIN ANTARMUKA PENGGUNA (UI/UX) & 4 DASHBOARD REAL (04:15 – 05:15)
**Estimasi Waktu:** 60 Detik  
**Tampilan Layar:** `[PINDAH KE SLIDE 6 — Galeri 4 Dashboard Nyata: Warga, RT, RW, dan Admin Desa]`

> *"Beralih ke aspek desain antarmuka, pada Slide 6 ditampilkan implementasi nyata dari **4 Dashboard Utama SIWIL** sesuai peran pengguna:*  
> 
> *- **Kiri Atas — Dashboard Warga**: Berbasis mobile-first dengan kartu permohonan surat, aduan lingkungan, musyawarah polling, tombol darurat SOS merah, serta floating widget asisten cerdas SIWIL AI.*  
> *- **Kanan Atas — Dashboard Ketua RT**: Meja kerja verifikasi surat warga RT 01 dan monitoring aduan jalan rusak yang telah terselesaikan.*  
> *- **Kiri Bawah — Dashboard Ketua RW**: Panel supervisi berjenjang untuk menyetujui surat pengantar serta memantau agregasi kependudukan lintas RT.*  
> *- **Kanan Bawah — Dashboard Admin Desa**: Pusat kendali terpadu yang memetakan matriks wilayah Desa, RW, RT, sensus demografi, serta transparansi APBDes.*  
> 
> *Seluruh antarmuka ini mengusung filosofi **Clean Antislop** yang bebas ornamen semu serta mematuhi standar aksesibilitas **WCAG 2.1 AA** dengan rasio kontras warna di atas 4,5 banding 1 demi kemudahan membaca seluruh lapisan warga."*

---

### SLIDE 7: KESIMPULAN PERANCANGAN & KESIAPAN TAHAP 3 (05:15 – 05:45)
**Estimasi Waktu:** 30 Detik  
**Tampilan Layar:** `[PINDAH KE SLIDE 7 — Kesimpulan, Kesiapan Eksekusi Tahap 3, dan Sesi Q&A]`

> *"Bapak dosen dan rekan-rekan sekalian,*  
> *Sebagai kesimpulan, luaran **Tahap 2 SDD** telah selesai dirancang secara tuntas, teruji, dan patuh 100% pada standar IEEE 1016 melalui 4 Diagram UML resmi dan implementasi nyata 4 dashboard sistem.*  
> 
> *Perancangan ini siap dieksekusi ke **Tahap 3**, di mana basis kode Laravel 12 dan PHP 8.4 telah terverifikasi melalui **105 skrip pengujian otomatis PHPUnit dengan 540 assertions** yang lulus 100% hijau di pipeline CI/CD GitHub Actions.*  
> 
> *Demikian presentasi progres Tahap 2 ini saya sampaikan. Terima kasih banyak atas perhatian Bapak Yudhistira dan rekan-rekan semua.*  
> *Sesi tanya jawab dan masukan saya persilakan.*  
> *Wabillahi taufiq wal hidayah, Wassalamu’alaikum Warahmatullahi Wabarakatuh."*

---

### RINGKASAN MONITORING DURASI PRESENTASI (MAKSIMAL 7 MENIT)

| No. Slide | Topik Bahasan | Target Durasi | Akumulasi Waktu | Status Rubrik |
|:---:|:---|:---:|:---:|:---:|
| **Slide 1** | Cover & Identitas Mahasiswa | 00:30 | 00:30 | Sesuai Rubrik |
| **Slide 2** | Diagram UML 1: Use Case Diagram & System Boundary | 00:55 | 01:25 | Sesuai Rubrik |
| **Slide 3** | Diagram UML 2: Activity Diagram & 5 Swimlanes AI | 00:50 | 02:15 | Sesuai Rubrik |
| **Slide 4** | Diagram UML 3: Class Diagram 4 Layer & 5 Prinsip SOLID | 01:00 | 03:15 | Sesuai Rubrik |
| **Slide 5** | Diagram UML 4: Sequence Diagram Multi-Tier & Lifelines | 01:00 | 04:15 | Sesuai Rubrik |
| **Slide 6** | Desain UI/UX: 4 Dashboard Real & Standar WCAG 2.1 AA | 01:00 | 05:15 | Sesuai Rubrik |
| **Slide 7** | Kesimpulan SDD, Kesiapan Tahap 3 & Sesi Tanya Jawab | 00:30 | **05:45** | **Sangat Aman (< 7 Menit)** |

*Naskah ini dirancang dengan total durasi pembacaan sekitar **05 Menit 45 Detik hingga 06 Menit 15 Detik**, memberikan margin aman sekitar 1 menit penuh di bawah batas maksimal 7 menit.*
