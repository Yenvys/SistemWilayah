# Product Requirements Document (PRD)
**Proyek:** SIWIL (Sistem Informasi Wilayah & Pemerintahan)
**Dokumen:** PRD v1.0

---

## 1. Pendahuluan
### 1.1. Latar Belakang
Tata kelola administrasi di tingkat wilayah terkecil (RT, RW, hingga Kelurahan/Desa) seringkali masih bersifat manual, lambat, dan kurang transparan. Warga kesulitan mengakses informasi anggaran, progres pembangunan, serta pelaporan keluhan secara *real-time*.

### 1.2. Tujuan (Objectives)
SIWIL dibangun untuk:
- Mendigitalisasi alur perizinan dan persuratan warga (bebas kertas).
- Membangun keterbukaan informasi (anggaran, kegiatan, pembangunan) agar warga dapat mengawasi dana desanya.
- Meningkatkan kecepatan respons perangkat desa terhadap aduan dan kejadian darurat melalui teknologi.
- Menghadirkan asisten cerdas (AI Chatbot) yang memandu warga 24/7.

## 2. Segmen Pengguna (Target Audience)
Sistem ini menargetkan 4 pilar utama pengguna:
1. **Warga**: Penduduk wilayah yang memerlukan kemudahan layanan birokrasi, pelaporan, dan akses informasi desa.
2. **Ketua RT/RW**: Pemangku kepentingan tingkat dasar yang perlu mengelola data keluarga (KK) warganya dan memverifikasi kelayakan surat/aduan.
3. **Pemerintah Desa (Admin)**: Institusi resmi yang mencetak dokumen hukum (surat) dan mempublikasikan dana/pembangunan (data makro).
4. **SuperAdmin**: Administrator teknis pengelola infrastruktur IT sistem dan master *knowledge base* (AI).

## 3. Fitur Utama & Epics

### Epic 1: Manajemen Kependudukan (Warga & Keluarga)
- Registrasi dan profil warga terintegrasi dengan Nomor Induk Kependudukan (NIK).
- Pendaftaran struktur Kartu Keluarga (KK) yang diverifikasi oleh RT setempat.
- Laporan mutasi penduduk (Kelahiran, Kematian, Pindah, Datang).

### Epic 2: Layanan Persuratan Berjenjang
- Modul pembuatan pengajuan surat oleh warga secara online (SKTM, Surat Pengantar, dll) dengan sistem *upload* berkas pendukung.
- Verifikasi *multi-tier*: Persetujuan secara berurutan oleh RT -> RW -> Desa.
- *Output* PDF final dengan Tanda Tangan Elektronik (TTE) / Barcode agar surat bisa dicetak mandiri oleh warga.

### Epic 3: Partisipasi dan Keamanan Wilayah
- **Pengaduan Warga**: Sistem *ticketing* masalah fasilitas/lingkungan dilengkapi foto & geolokasi (*geotagging*).
- **Lapor Darurat (Emergency)**: Modul *panic button* untuk laporan medis, kebakaran, atau kecelakaan yang langsung memberikan _alert_ ke operator Desa/RT.
- **Poling Digital**: Modul jajak pendapat / musyawarah warga untuk mengambil keputusan kolektif secara elektronik.

### Epic 4: Transparansi & Dashboard Publik
- **Keterbukaan APBDes / Anggaran**: Modul untuk memajang laporan keuangan desa.
- **Proyek Pembangunan**: Peta dan status proyek (infrastruktur) yang dibiayai dana publik.
- **Papan Pengumuman**: Diseminasi informasi resmi (agenda kegiatan, posyandu, peringatan).

### Epic 5: Implementasi AI (Layanan Cerdas)
- **Chatbot Asisten Desa**: NLP bot berbasis _Knowledge Base_ (`AiKnowledge`) untuk menjawab SOP atau pertanyaan repetitif warga.
- **Spatial Analytics**: Geo-clustering titik pelaporan keluhan/kejahatan untuk memetakan "Titik Rawan" pada sebuah peta digital interaktif bagi pimpinan Desa.

## 4. Stack Teknologi & Arsitektur
Aplikasi ini dikembangkan sebagai aplikasi *web-based* yang responsif:
- **Backend**: Laravel 11/13 (PHP ^8.3)
- **Frontend**: Blade Templating, Tailwind CSS v4, Vite 8
- **Database**: MySQL / SQLite (mendukung transaksi data relasional warga dan surat).
- **Security & Authorization**: Spatie Laravel Permission (RBAC).

## 5. Matriks Kriteria Penerimaan (Acceptance Criteria) Umum
- **Surat**: Warga tidak bisa melompati verifikasi RT; surat hanya bisa diunduh jika status sudah di-_approve_ oleh Desa.
- **Aksesibilitas**: Modul transparansi keuangan, pembangunan, dan pengumuman dapat diakses *read-only* oleh warga tanpa batasan, namun hanya bisa diedit oleh Admin Desa.
- **Notifikasi**: Transisi status (*Pending -> Processed -> Approved/Rejected*) tercatat dalam *Audit Log* dan sejarah (_history_).

---
*Dokumen ini merupakan pedoman dasar produk dan dapat berkembang menyesuaikan feedback dari fase Beta / Uji Coba pengguna.*
