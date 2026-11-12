# Product Requirements Document (PRD)
## SIWIL — Sistem Informasi Wilayah & Pemerintahan Berbasis Web + AI

---

### Informasi Dokumen
| Atribut | Keterangan Dokumen |
| :--- | :--- |
| **Nama Produk** | **SIWIL** (*Sistem Informasi Wilayah & Pemerintahan Terpadu*) |
| **Versi Dokumen** | **v2.5.0 (Final - Production Ready / As-Built)** |
| **Penulis / Product Owner** | **Muhammad Al Farel** (NIM: 41257124) |
| **Kelas / Angkatan** | **TI 1B 2025** |
| **Program Studi / Institusi** | **S1 Teknik Informatika — STMIK IKMI Cirebon** |
| **Mata Kuliah / Kode** | **Rekayasa Perangkat Lunak (TIKB2503021)** |
| **Dosen Pengampu** | **Yudhistira Arie Wijaya, M.Kom., CDS.** |
| **Tema Topik Kelompok** | **Smart Governance & Public Service AI** |
| **Standar Acuan Rekayasa** | **IEEE Std 830 / ISO/IEC/IEEE 29148 & ISO/IEC 25010** |
| **Status Dokumen** | **Approved / Production-Ready** |

---

## 1. Ringkasan Produk
**Nama Produk:** SIWIL (Sistem Informasi Wilayah & Pemerintahan Terpadu)

**SIWIL** adalah aplikasi berbasis web cerdas untuk membantu **Warga, Ketua RT, Ketua RW, Admin Desa/Kelurahan, dan Super Administrator** dalam mengelola data wilayah, kependudukan, administrasi pemerintahan, pelayanan persuratan mandiri, pelaporan masalah lingkungan, serta memperoleh wawasan (*insight*) analitik melalui **Artificial Intelligence (AI)** berbasis Large Language Model.

Sistem dirancang untuk menggantikan proses birokrasi manual yang lambat dan pencatatan berbasis kertas/spreadsheet menjadi ekosistem digital yang terpusat, transparan, terstruktur, aman sesuai regulasi privasi data, dan mudah dipantau secara *real-time*.

### Masalah yang Diselesaikan:
1. **Rantai Birokrasi Berjenjang yang Lambat:** Memangkas waktu pengurusan surat pengantar fisik yang biasanya memakan 2–5 hari kerja menjadi kurang dari 4 jam melalui alur persetujuan digital 3-tier (*RT $ightarrow$ RW $ightarrow$ Desa*) berkekuatan hukum dengan Tanda Tangan Elektronik (TTE) ber-QR Code.
2. **Keterbatasan Waktu Temu Pengurus Wilayah:** Menyediakan akses layanan mandiri 24/7 melalui web browser serta saluran pesan instan (WhatsApp dan Telegram Bot) sehingga warga tidak perlu mendatangi rumah pengurus berkali-kali.
3. **Penyimpanan Terfragmentasi & Kerentanan Privasi (UU PDP):** Menghilangkan risiko tercecer atau bocornya buku register manual kependudukan dengan basis data relasional terenkripsi dan penegakan ketat **Undang-Undang No. 27 Tahun 2022 tentang Pelindungan Data Pribadi (UU PDP)**.
4. **Minimnya Transparansi Fiskal APBDes & Partisipasi Warga:** Membuka data pagu anggaran dan realisasi APBDes serta proyek pembangunan desa kepada masyarakat luas, serta memfasilitasi musyawarah warga digital (*One Citizen One Vote*).

---

## 2. Tujuan Produk
1. **Memusatkan data wilayah dan pemerintahan** dalam satu basis data terstruktur dari tingkat desa hingga RT.
2. **Mempermudah RT dan RW** dalam mengelola data kependudukan, mutasi warga, dan verifikasi permohonan surat administratif tanpa berkas fotokopi manual.
3. **Membantu Pemerintah Desa/Kelurahan** memonitor kondisi seluruh RT/RW, memvalidasi persuratan berkekuatan hukum, mengelola proyek fisik, dan mempublikasikan APBDes secara akuntabel.
4. **Mempercepat proses pelayanan masyarakat (Zero Paperwork)** dengan layanan mandiri warga berbasis web, WhatsApp Bot, dan Telegram Bot.
5. **Menyediakan dashboard analitik dan laporan *real-time*** mengenai kependudukan, persuratan, pengaduan sarana, dan serapan anggaran.
6. **Memanfaatkan Kecerdasan Buatan (Google Gemini AI Engine)** dengan 12 *function calling tools* untuk menjawab pertanyaan prosedural, menarik data wilayah secara aman, dan menyajikan *insight* analitik bagi pimpinan wilayah tanpa melanggar privasi data kependudukan.
7. **Menjamin keaslian akun warga (Zero Fake Account)** melalui validasi silang NIK 16 digit dan Nomor KK terhadap basis data kependudukan desa saat registrasi.

---

## 3. Pengguna & Hak Akses (Role-Based Access Control)

Sistem menerapkan pembatasan hak akses berbasis peran (RBAC) yang ketat menggunakan Spatie Laravel Permission dan *Eloquent Scoped Queries*:

| Role Pengguna | Ruang Lingkup Otoritas (Scope) | Fungsi Utama |
| :--- | :--- | :--- |
| **Masyarakat / Publik** | Tanpa Login (Akses Terbuka) | Melihat profil desa, transparansi APBDes, statistik demografi kependudukan, dan pemantauan proyek pembangunan fisik. |
| **Warga** | Data Pribadi & Anggota KK-nya | Pengajuan surat mandiri, tracking status, unduh berkas PDF resmi ber-barcode, lapor aduan foto & GPS, tombol darurat SOS, voting musyawarah, dan konsultasi asisten AI. |
| **Ketua RT** | 1 Rukun Tetangga (RT binaan) | Verifikasi permohonan surat tingkat 1, verifikasi akun warga baru, kelola data master penduduk & KK RT, penanggap pertama peringatan darurat SOS, dan buat polling RT. |
| **Ketua RW** | 1 Rukun Warga (Seluruh RT binaan) | Verifikasi berkas surat tingkat 2, pemantauan statistik agregat RW, koordinasi musyawarah tingkat RW, dan disposisi aduan lingkungan lintas RT. |
| **Admin Desa / Kelurahan** | 1 Desa / Kelurahan Terkait | Verifikasi akhir dan penerbitan surat resmi berkop desa dengan stempel dan TTE QR Code Kepala Desa, kelola anggaran APBDes dan proyek fisik pembangunan, rilis pengumuman desa. |
| **Super Administrator** | Global Sistem (Seluruh Desa) | Manajemen multi-desa, konfigurasi global (AI Provider, API Keys, token WhatsApp/Telegram, SMTP mailer), inspeksi audit log forensik, dan manajemen seluruh user. |

---

## 4. Fitur Utama MVP

### A. Dashboard
Dashboard adaptif berbasis peran yang menyajikan metrik operasional secara *real-time*:
- **Dashboard Warga:** Ringkasan status surat aktif, riwayat aduan pribadi, musyawarah/polling yang sedang berjalan, papan pengumuman desa terbaru, dan tombol darurat SOS.
- **Dashboard RT/RW:** Statistik jumlah jiwa, jumlah KK, rasio gender, sebaran usia, antrean verifikasi surat masuk, aduan warga belum tertangani, dan notifikasi insiden darurat aktif.
- **Dashboard Admin Desa & Super Admin:** Pohon struktur teritorial wilayah lengkap (Desa $ightarrow$ RW $ightarrow$ RT beserta jumlah KK dan penduduk per wilayah), serapan fiskal APBDes, progres proyek pembangunan, direktori pengguna, dan rekapitulasi audit log.

### B. Manajemen Wilayah
Struktur wilayah hierarkis berjenjang:
$$	ext{Provinsi} ightarrow 	ext{Kabupaten/Kota} ightarrow 	ext{Kecamatan} ightarrow 	ext{Desa/Kelurahan} ightarrow 	ext{RW} ightarrow 	ext{RT}$$
- **Fitur:** Tambah/sunting nama desa dan kode wilayah, pendataan nomor RW dan RT, penugasan kepala wilayah/ketua RT/RW, dan rekapitulasi jumlah KK serta penduduk di setiap level teritorial.

### C. Data Penduduk (Residents Master Data)
Pengelolaan data induk kependudukan warga secara mendalam:
- **Atribut Lengkap:** NIK (16 digit unik), Nama Lengkap, Nomor KK, Tempat/Tanggal Lahir, Jenis Kelamin, Alamat Domisili, RT/RW, Agama, Status Perkawinan, Pendidikan Terakhir, Pekerjaan, Status Kependudukan (Aktif, Pindah, Meninggal), Penanda Kepala Keluarga, dan Foto e-KTP.
- **Fitur Tambahan:** Pencarian multi-kriteria (berdasarkan NIK, Nama, RT, RW), filter kependudukan, riwayat perubahan biodata, dan ekspor data ke format PDF/Excel.

### D. Data Keluarga / Kartu Keluarga (KK)
Pengelolaan data Kartu Keluarga terpusat:
- **Atribut:** Nomor KK (16 digit unik), Alamat Domisili, relasi RT/RW, dan Nama Kepala Keluarga.
- **Fitur "Keluarga Saya" (`/dashboard/my-family`):** Memberikan akses mandiri kepada warga untuk memeriksa seluruh daftar anggota keluarga yang terdaftar pada Kartu Keluarganya secara privat dan terisolasi.

### E. Administrasi & Pelayanan Surat Digital
Masyarakat dapat mengajukan kebutuhan administrasi secara mandiri dari web browser, WhatsApp, atau Telegram:
- **Katalog Layanan Surat:**
  1. Surat Keterangan Domisili
  2. Surat Keterangan Tidak Mampu (SKTM)
  3. Surat Keterangan Usaha (SKU)
  4. Surat Pengantar Nikah (ke KUA)
  5. Surat Izin Keramaian
  6. Surat Keterangan Kehilangan / Legalitas
  7. Surat Izin Renovasi Rumah
  8. Surat Pendataan Tamu Menginap
  9. Surat Pengantar Umum RT/RW
- **Alur Kerja Persetujuan 3-Tier:**
  $$	ext{Pengajuan Warga} ightarrow 	ext{Verifikasi RT} ightarrow 	ext{Verifikasi RW} ightarrow 	ext{Pengesahan Admin Desa} ightarrow 	ext{Terbit}$$
- **Status Alur Persuratan:**
  `DRAFT` $ightarrow$ `DIAJUKAN_RT` $ightarrow$ `DIAJUKAN_RW` $ightarrow$ `DIAJUKAN_DESA` $ightarrow$ `DISETUJUI / SELESAI` (atau `DITOLAK` disertai catatan alasan).
- **Tanda Tangan Elektronik & Validasi Dokumen (QR Code TTE):**
  Sistem secara otomatis menyematkan stempel desa dan TTE digital Kepala Desa serta men-generate berkas PDF resmi via DomPDF lengkap dengan **QR Code Validator** yang dapat dipindai oleh pihak eksternal (Perbankan, Kepolisian, Instansi Dinas) untuk verifikasi keaslian dokumen secara *real-time*.

### F. Data Bantuan Sosial, APBDes & Proyek Pembangunan
- **Transparansi APBDes:** Pencatatan anggaran desa per tahun buku yang terbagi ke dalam 3 klasifikasi fiskal: **Pendapatan**, **Belanja**, dan **Pembiayaan**, dilengkapi perbandingan pagu anggaran vs realisasi serapan dana riil.
- **Monitoring Proyek Fisik:** Pencatatan proyek pembangunan sarana desa dilengkapi pagu biaya, sumber dana (Dana Desa, PADes, Bantuan Provinsi), jadwal pelaksanaan, foto dokumentasi lapangan, dan progress bar fisik ($0\% - 100\%$).
- **Data Bantuan Sosial:** Pencatatan warga penerima bantuan (PKH, BLT, BPNT) untuk memastikan transparansi dan mencegah penyaluran ganda.

### G. Laporan & Rekapitulasi Data
Penyusunan laporan berkala yang dapat difilter berdasarkan level RT, RW, Desa, rentang tanggal, dan jenis transaksi:
- Laporan Kependudukan & Piramida Usia
- Laporan Rekapitulasi Pelayanan Surat Administrasi
- Laporan Penanganan Pengaduan Masyarakat
- Laporan Realisasi Anggaran APBDes
- **Format Output:** Dokumen cetak PDF resmi dan berkas Spreadsheet Excel.

### H. Pengaduan Geotagging & Tombol Darurat SOS
- **Pengaduan Warga Geotagging:** Pelaporan kerusakan sarana fisik (jalan berlubang, lampu penerangan mati, sampah liar) disertai unggah foto bukti lapangan dan titik koordinat GPS presisi (`latitude` & `longitude`).
- **Tombol Darurat Panik (Emergency SOS):** Tombol cepat untuk keadaan bahaya mendesak (**Medis**, **Kebakaran**, **Kriminal**, **Bencana Alam**) yang secara instan menangkap koordinat GPS korban dan memicu banner alarm darurat aktif berkedip di dashboard seluruh pengurus RT, RW, dan kantor desa.

---

### Contoh Kriteria Penerimaan Fitur Kunci (Format Gherkin: Given - When - Then)

```gherkin
Scenario: Registrasi akun warga dengan validasi silang kependudukan desa (Zero Fake Account)
  Given Calon warga mengakses formulir "/register"
  When Menginputkan NIK 16 digit dan Nomor KK 16 digit yang cocok di data kependudukan desa
  Then Sistem membuat akun baru dengan status "pending" menunggu persetujuan RT/Desa
  And Jika NIK/KK tidak terdaftar, sistem membatalkan proses registrasi secara tegas

Scenario: Alur persetujuan surat berjenjang 3-tier hingga terbit ber-barcode TTE
  Given Permohonan surat diajukan warga berstatus "DIAJUKAN_RT"
  When Ketua RT menyetujui, status naik menjadi "DIAJUKAN_RW"
  When Ketua RW menyetujui, status naik menjadi "DIAJUKAN_DESA"
  When Admin Desa menyetujui, status berubah menjadi "DISETUJUI / SELESAI"
  And Sistem menyematkan stempel desa, TTE Kepala Desa, dan QR Code verifikasi dokumen asli
  And Dokumen PDF resmi ter-generate otomatis dan siap diunduh oleh warga
```

---

## 5. Fitur AI (SIWIL AI Engine)

Kecerdasan buatan (*Artificial Intelligence*) menjadi fitur pembeda dan nilai kebaruan (*novelty*) utama dalam SIWIL. SIWIL AI ditenagai oleh Large Language Model **Google Gemini Generative API** (`gemini-2.5-flash` / `gemini-1.5-flash`) yang terhubung langsung dengan *backend* operasional.

### 5.1 AI Assistant (Percakapan Bahasa Alami)
Warga dan pengurus wilayah dapat mengajukan pertanyaan menggunakan bahasa natural sehari-hari melalui widget chat mengambang di dashboard:
- *"Bagaimana syarat dan alur mengajukan Surat Keterangan Usaha?"*
- *"Bagaimana status surat domisili yang saya ajukan kemarin?"*
- *"Berapa jumlah penduduk lansia di wilayah RT 02 saat ini?"*
- *"Tampilkan daftar pengumuman desa terbaru terkait posyandu."*

### 5.2 AI Analisis Wilayah & Bantuan Administrasi
AI mampu menganalisis agregasi data sistem untuk memberikan rekomendasi kebijakan bagi pimpinan wilayah:
- Menganalisis piramida usia penduduk dan mendeteksi tren penuaan warga di lingkungan RW tertentu.
- Membantu merangkum data serapan anggaran pos belanja APBDes.
- Mengidentifikasi anomali data (misal: Kartu Keluarga tanpa penanda Kepala Keluarga).
- Membantu menyusun draf ringkasan laporan bulanan RT/RW.

### 5.3 Katalog 12 Backend Function Calling Tools
AI tidak diperbolehkan mengarang fakta kependudukan (*halusinasi*). AI dibekali 12 *function calling tools* deterministik di backend (`App\Services\AiToolService`):

| No | Nama Function Calling Tool | Deskripsi & Tugas Fungsional | Batasan Hak Akses Data (RBAC) |
| :-: | :--- | :--- | :--- |
| 1 | `fetch_dashboard_data` | Mengambil ringkasan metrik statistik wilayah (total surat, aduan aktif). | Disesuaikan dengan role akun pengguna yang login. |
| 2 | `fetch_residents_and_areas` | Mengambil data kependudukan dan hierarki struktur teritorial wilayah. | **Khusus Pengurus (RT, RW, Desa, Superadmin)**. Ditolak untuk Warga. |
| 3 | `fetch_announcements` | Mengambil artikel pengumuman dan surat edaran desa yang berstatus aktif. | Terbuka untuk seluruh peran pengguna. |
| 4 | `fetch_document_requests` | Mengambil riwayat permohonan surat administrasi beserta status terkininya. | Warga hanya menerima datanya sendiri; Pengurus sesuai wilayah binaan. |
| 5 | `fetch_complaints` | Mengambil daftar pengaduan masyarakat dan status penanganannya. | Warga menerima aduan miliknya; Pengurus sesuai yurisdiksi tugas. |
| 6 | `fetch_polls` | Mengambil daftar polling musyawarah aktif dan status hak pilih pengguna. | Sesuai level wilayah polling (RT / RW / Desa). |
| 7 | `fetch_family_data` | Mengambil rincian data Kartu Keluarga dan anggota keluarga pemohon. | **Wajib sama No. KK-nya**. Warga dilarang melihat KK orang lain. |
| 8 | `fetch_development_and_budgets` | Mengambil data proyek pembangunan fisik dan rincian alokasi APBDes. | Khusus pengurus atau portal keterbukaan informasi. |
| 9 | `fetch_audit_logs` | Mengambil jejak audit aktivitas manipulasi data sensitif sistem. | Khusus Super Administrator dan Pimpinan Wilayah. |
| 10 | `fetch_users_directory` | Mengambil direktori akun pengguna sistem dan status verifikasi akun. | Khusus Super Administrator dan Admin Desa. |
| 11 | `fetch_ai_knowledge` | Mengambil basis data pengetahuan resmi (SOP pelayanan, sejarah desa). | Dapat diakses seluruh peran sebagai referensi aturan resmi. |
| 12 | `fetch_current_user_profile` | Mengambil biodata dan profil lengkap dari akun yang sedang login saat ini. | Mengembalikan profil privat milik akun terotentikasi. |

### 5.4 Penegakan Privasi & Guardrail UU No. 27 Tahun 2022 (UU PDP)
Sesuai amanat Undang-Undang Pelindungan Data Pribadi:
- **Instruction Guardrail:** AI secara tegas dilarang membocorkan Nomor Induk Kependudukan (NIK), Nomor KK, Nomor Telepon/WhatsApp, dan alamat tinggal warga lain kepada akun berstatus warga biasa.
- **Backend Deterministic Scoping:** Di tingkat backend (`AiToolService.php`), jika akun Warga mencoba memanggil tool data kependudukan global, sistem secara otomatis memotong data (*empty payload*) dan mengembalikan pesan penolakan privasi resmi.

### 5.5 Ketahanan Sistem: Multi-Key API Rotation & Smart Fallback
- **Rotasi Multi-API Key:** Kunci API Google Gemini disimpan dalam array JSON di tabel `system_configurations`. Jika kunci utama mengalami limitasi kuota (HTTP 429), sistem otomatis beralih ke kunci cadangan berikutnya.
- **Smart Local Fallback:** Jika koneksi API luar terputus, controller AI mengaktifkan mesin pencocokan pola kata kunci (*regex pattern matching*) lokal untuk menjawab pertanyaan umum persuratan dan nomor kontak darurat secara offline.

---

## 6. Notifikasi Multichannel

Sistem dilengkapi dengan *Notification Hub* terpadu yang menyiarkan pemberitahuan secara paralel:
1. **In-App Notification:** Notifikasi lonceng pada bilah navigasi atas dashboard lengkap dengan penanda belum dibaca (*unread badge counter*) dan aksi tandai telah dibaca.
2. **WhatsApp Gateway Notification:** Pengiriman pesan instan otomatis via microservice Node.js port 3000 langsung ke nomor WhatsApp pengguna.
3. **Telegram Bot Notification:** Notifikasi real-time ke akun Telegram warga yang telah ditautkan melalui fitur *deep linking*.
4. **Email Notification (SMTP):** Notifikasi resmi menggunakan template email HTML responsif.

**Pemicu Notifikasi Otomatis:**
- Pengajuan surat baru masuk ke antrean verifikasi Ketua RT.
- Perubahan status surat (disetujui RT, disetujui RW, disetujui Desa, atau ditolak).
- Surat selesai diterbitkan (mengirimkan link dan berkas PDF ber-barcode langsung).
- Pembaruan status tindak lanjut penanganan aduan warga.
- Peringatan insiden darurat SOS yang memicu alarm visual di dashboard pengurus.
- Publikasi pengumuman atau surat edaran resmi desa baru.
- Penutupan musyawarah dan penyiaran hasil akhir jajak pendapat warga.

---

## 7. Manajemen Pengguna & Keamanan Identitas (IAM)

Super Admin dan Admin Desa mengelola akun pengguna menggunakan kontrol akses berbasis peran (RBAC):
- **Pengelolaan User:** Tambah akun pengurus wilayah, tinjau akun warga berstatus `pending`, setujui (*active*), atau tolak (*rejected*).
- **Multi-Identifier Login:** Pengguna dapat masuk menggunakan **NIK 16 digit**, **Alamat Email**, atau **Nomor WhatsApp terdaftar**.
- **Pemulihan Kata Sandi dengan WhatsApp OTP:**
  - Warga memasukkan NIK atau nomor WhatsApp di halaman lupa kata sandi.
  - Sistem men-generate **kode OTP 6 digit acak** dengan masa berlaku tepat 5 menit di penyimpanan cache.
  - OTP dikirimkan instan ke nomor WhatsApp warga melalui WhatsApp Gateway.
  - Dilengkapi *resend throttle timer* 60 detik dan proteksi anti *brute-force*.
- **Enkripsi Kredensial:** Seluruh kata sandi dienkripsi menggunakan algoritma **Bcrypt** (*cost factor* 12).
- **Tanda Tangan Elektronik & Stempel Pengurus:** Pengurus RT, RW, dan Desa dapat mengunggah berkas transparansi tanda tangan digital (`signature_path`) dan stempel wilayah (`stamp_path`) pada profilnya untuk disematkan otomatis pada dokumen PDF surat resmi.

---

## 8. Audit Log (Jejak Forensik Sistem)

Sistem mencatat seluruh mutasi data sensitif secara otomatis ke dalam tabel `audit_logs` yang bersifat *append-only* (tidak dapat disunting):
- **Informasi yang Dicatat:**
  - Identitas Aktor (`user_id`, nama, peran)
  - Jenis Operasi (`CREATE`, `UPDATE`, `DELETE`, `LOGIN`, `LOGOUT`)
  - Entitas Terdampak (`entity_name`, misal: `DocumentRequest`, `Resident`, `Budget`)
  - ID Entitas (`entity_id`)
  - Detail Payload Data Sebelum dan Sesudah Perubahan (`details`)
  - Waktu Transaksi (*Timestamp*)
- **Fungsi:** Menjamin akuntabilitas aparatur pemerintahan, mencegah manipulasi data sepihak, dan menyediakan bukti jejak audit forensik bagi pengawas tata kelola wilayah.

---

## 9. Struktur Menu Sederhana

```
SIWIL NAVIGATION STRUCTURE
├── Dashboard (Tampilan Adaptif sesuai Role: Warga / RT-RW / Desa / Superadmin)
│
├── Wilayah (Khusus Pengurus & Superadmin)
│   ├── Desa / Kelurahan
│   ├── Rukun Warga (RW)
│   └── Rukun Tetangga (RT)
│
├── Kependudukan
│   ├── Data Penduduk (Residents Master)
│   ├── Data Kartu Keluarga (KK)
│   └── Keluarga Saya (Khusus Warga)
│
├── Pelayanan Administrasi
│   ├── Pengajuan Surat (Katalog Surat Mandiri)
│   ├── Antrean Verifikasi (Khusus RT, RW, Desa)
│   └── Arsip & Unduh Dokumen PDF Ber-Barcode
│
├── Pengaduan & Layanan Darurat
│   ├── Lapor Pengaduan Geotagging GPS & Foto
│   ├── Monitoring & Tindak Lanjut Aduan
│   └── Tombol Darurat SOS (Emergency Panic Button)
│
├── Demokrasi & Informasi
│   ├── Musyawarah & Polling Warga (E-Democracy)
│   └── Papan Pengumuman & Berita Desa
│
├── Pembangunan & Fiskal (Transparansi Publik & Admin)
│   ├── Transparansi Anggaran APBDes
│   └── Monitoring Proyek Pembangunan Fisik (GIS Map)
│
├── SIWIL AI Assistant (Floating Chatbot Widget Interaktif)
├── Notifikasi Sistem (In-App, WhatsApp, Telegram)
├── Manajemen Pengguna & Hak Akses (User Directory & Verification)
├── Pusat Konfigurasi Sistem (AI Provider, WhatsApp, Telegram, SMTP)
└── Audit Log Aktivitas
```

---

## 10. Alur Utama Pengguna (User Flows)

### A. Alur Pengguna Warga (Citizen Flow)
$$	ext{Registrasi Validasi NIK/KK} ightarrow 	ext{Verifikasi RT/Desa} ightarrow 	ext{Login Multi-ID} ightarrow 	ext{Dashboard Warga}$$
- **Pengajuan Surat:** Pilih jenis surat $ightarrow$ Isi keperluan & upload syarat $ightarrow$ Submit $ightarrow$ Pantau status tracking $ightarrow$ Surat disetujui $ightarrow$ Unduh PDF resmi ber-barcode TTE.
- **Pengaduan & Darurat:** Buka form aduan $ightarrow$ Ambil foto & GPS $ightarrow$ Kirim $ightarrow$ Pantau status tindak lanjut petugas; ATAU tekan tombol merah SOS saat bahaya mendesak.
- **Konsultasi AI:** Buka floating widget chat $ightarrow$ Tanyakan prosedur/data $ightarrow$ AI merespons instan sesuai privasi UU PDP.

### B. Alur Pengurus RT (Neighborhood Leader Flow)
$$	ext{Login RT} ightarrow 	ext{Dashboard RT} ightarrow 	ext{Kelola Data Warga & KK RT} ightarrow 	ext{Verifikasi Akun Warga Pending}$$
- **Verifikasi Surat Tingkat 1:** Buka antrean surat masuk $ightarrow$ Periksa lampiran $ightarrow$ Klik "Setujui" (status naik ke `DIAJUKAN_RW`) atau "Tolak" (disertai catatan).
- **Tanggap Darurat & Aduan:** Terima banner kedip darurat SOS $ightarrow$ Lihat koordinat lokasi korban di peta $ightarrow$ Berikan pertolongan lapangan $ightarrow$ Tandai selesai.

### C. Alur Pengurus RW (Sub-district Coordinator Flow)
$$	ext{Login RW} ightarrow 	ext{Dashboard Monitoring RW} ightarrow 	ext{Pantau Demografi RT se-RW} ightarrow 	ext{Verifikasi Surat Tingkat 2}$$
- **Verifikasi Surat Tingkat 2:** Periksa surat yang telah disetujui RT $ightarrow$ Klik "Setujui" (status naik ke `DIAJUKAN_DESA`) $ightarrow$ Fasilitasi musyawarah tingkat RW.

### D. Alur Pemerintah Desa / Kelurahan (Village Admin Flow)
$$	ext{Login Admin Desa} ightarrow 	ext{Dashboard Wilayah Desa} ightarrow 	ext{Verifikasi Akhir & Penerbitan Surat}$$
- **Penerbitan Dokumen Resmi:** Buka surat terverifikasi RT & RW $ightarrow$ Bubuhkan stempel digital & TTE Kepala Desa $ightarrow$ Sistem menerbitkan berkas PDF berkop resmi ber-QR Code validator.
- **Tata Kelola Pembangunan:** Input pagu anggaran dan realisasi APBDes $ightarrow$ Perbarui progres fisik proyek pembangunan beserta dokumentasi foto.

### E. Alur Super Administrator (System Owner Flow)
$$	ext{Login Super Admin} ightarrow 	ext{Pusat Konfigurasi Sistem} ightarrow 	ext{Diagnostik Integrasi}$$
- Atur API Key Google Gemini, URL microservice WhatsApp, token Telegram Bot, dan SMTP Mailer.
- Uji koneksi langsung melalui tombol diagnostik.
- Pantau seluruh mutasi data melalui menu Audit Log forensik.

---

## 11. Non-Functional Requirements (Standar ISO/IEC 25010)

Sistem memenuhi karakteristik kualitas perangkat lunak internasional:
- **Responsif & Mobile-First:** Antarmuka web dibangun menggunakan Tailwind CSS v4, responsif mulai dari layar smartphone (min 360px) hingga monitor desktop 1920px. Mendukung mode Terang dan Gelap (*Dark/Light Mode*).
- **Efisiensi Kinerja (Performance):** Rata-rata waktu pemuatan halaman $< 1.5$ detik; latensi inferensi asisten AI $< 3.0$ detik; penggunaan *Eager Loading* (`with(...)`) untuk mengeliminasi masalah *N+1 query*; paginasi data 10–25 baris per halaman.
- **Keamanan & Kriptografi:** Seluruh password di-hash dengan Bcrypt (*cost factor* 12); seluruh entitas menggunakan kunci primer **UUID v4** untuk mencegah eksploitasi enumerasi URL; proteksi bawaan terhadap CSRF, XSS, dan SQL Injection.
- **Kepatuhan Privasi Data (UU PDP No. 27/2022):** Kueri data kependudukan privat diproteksi oleh *Eloquent Scoped Queries* (`accessibleBy(User $user)`); Asisten AI dibekali aturan sistem keras yang menolak membocorkan NIK, nomor telepon, atau data privat warga lain kepada akun berstatus warga biasa.
- **Keandalan & Toleransi Kesalahan (Reliability):** Target ketersediaan layanan *uptime* 99.5%; panggilan API eksternal (WhatsApp & Telegram) dibungkus blok proteksi *try-catch non-blocking* sehingga kegagalan jaringan luar tidak menggagalkan penyimpanan transaksi utama database; rotasi otomatis multi-key API dan *fallback local pattern matching* pada modul AI.
- **Kemudahan Pemeliharaan & Kualitas Kode (Maintainability):** Mengikuti prinsip *Clean Architecture* dan *SOLID*; pemformatan kode mematuhi standar PSR-12 menggunakan Laravel Pint; dilengkapi **105 skrip pengujian otomatis (PHPUnit Feature & Unit Tests)** dengan status kelulusan 100%.

---

## 12. MVP Prioritas

Pengembangan fitur SIWIL dikelompokkan ke dalam 3 skala prioritas implementasi:

### P0 — Wajib (Core Foundation - 100% Implemented)
1. Autentikasi, IAM & Validasi Silang Registrasi NIK/KK (Zero Fake Account)
2. Manajemen Hirarki Wilayah (Desa $ightarrow$ RW $ightarrow$ RT)
3. Master Data Penduduk (Residents) & Kartu Keluarga (KK)
4. Dashboard Berbasis Peran (Warga, RT, RW, Desa, Superadmin)
5. Alur Pelayanan Persuratan 3-Tier (RT $ightarrow$ RW $ightarrow$ Desa) & TTE QR Code DomPDF
6. Laporan Rekapitulasi Data (PDF & Excel)
7. Audit Log Forensik Sistem
8. Portal Keterbukaan Publik (APBDes & Pembangunan)

### P1 — Fitur Cerdas & Layanan Lapangan (100% Implemented)
9. Asisten Cerdas SIWIL AI (Google Gemini LLM dengan 12 Backend Function Calling Tools)
10. Penegakan Guardrail Privasi UU No. 27 Tahun 2022 (UU PDP) & Smart Fallback
11. Pengaduan Warga Geotagging Koordinat GPS Presisi & Unggah Foto Bukti
12. Tombol Peringatan Bahaya Darurat Cepat (Emergency SOS Panic Button)
13. Musyawarah & Polling Warga Berintegritas (One Citizen One Vote)
14. Sistem Notifikasi Multichannel (In-App, WhatsApp Gateway port 3000, Telegram Bot, Email)
15. Reset Password Mandiri via Kode OTP WhatsApp 6 Digit

### P2 — Pengembangan Lanjutan (Product Roadmap)
16. Aplikasi Mobile PWA (Progressive Web App) dengan Push Notifications (FCM) dan mode offline-first.
17. Integrasi Payment Gateway (QRIS / Midtrans) untuk pembayaran digital kas RT bulanan dan sampah.
18. Integrasi REST API Nasional Dukcapil untuk verifikasi identitas e-KTP dan KIA secara otomatis.
19. Modul Manajemen Ronda Malam & Absensi Pos Kamling berbasis QR Code scanner.

---

## 13. Indikator Keberhasilan (Key Performance Indicators)

Target capaian terukur dari implementasi SIWIL:
- **$\ge 90\%$ data kependudukan dan persuratan wilayah terdigitalisasi**, mengeliminasi kebutuhan berkas fotokopi dan kertas formulir manual.
- **Waktu penerbitan surat administratif turun drastis**, dari 2–5 hari kerja menjadi $< 4$ jam (bahkan dalam hitungan menit begitu diverifikasi).
- **Akurasi identitas akun pengguna 100% sah (Zero Fake Account)** melalui validasi silang NIK dan KK saat registrasi.
- **Waktu tanggap penanganan awal pengaduan warga $< 24$ jam** berkat informasi titik lokasi koordinat GPS yang presisi.
- **Partisipasi hak pilih warga dalam musyawarah lingkungan meningkat hingga $> 70\%$** berkat kemudahan voting via web dan WhatsApp.
- **Asisten AI Gemini mampu merespons pertanyaan dan mengambil data riil wilayah dalam waktu $< 3.0$ detik** dengan akurasi faktual tinggi dan 100% patuh regulasi UU PDP.
- **Pipeline otomatisasi CI/CD pada GitHub Actions berjalan 100% berstatus Centang Hijau (Success)** dengan seluruh 105 skrip pengujian otomatis (540 assertions) lolos uji.

---

## 14. Prinsip Utama Produk

> ### *"Satu Data Wilayah, Satu Sistem Pemerintahan, Dibantu Kecerdasan Buatan (AI)."*

SIWIL tidak sekadar berfungsi sebagai basis data digital administratif, melainkan hadir sebagai **Pusat Informasi Terpadu dan Lapisan Kecerdasan (*Intelligence Layer*)** yang memberdayakan Warga, Ketua RT, Ketua RW, dan Pemerintah Desa untuk memahami dinamika lingkungan, mempercepat pelayanan publik, serta mengambil keputusan tata kelola wilayah yang transparan, akuntabel, dan berbasis data riil.

---
*Product Requirements Document (PRD) ini disusun secara komprehensif oleh Muhammad Al Farel (NIM: 41257124), Kelas TI 1B 2025, sebagai spesifikasi acuan rekayasa perangkat lunak aplikasi SIWIL.*
