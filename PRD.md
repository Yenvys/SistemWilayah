# DOKUMEN SPESIFIKASI KEBUTUHAN PERANGKAT LUNAK (SRS / PRD)
## SIWIL — Sistem Informasi Wilayah & Pemerintahan Terpadu
**Standar Acuan: IEEE Std 830-1998 / ISO/IEC/IEEE 29148:2018 & ISO/IEC 25010**

---

### Informasi Metadata Dokumen & Pengembang
| Atribut | Keterangan Dokumen |
| :--- | :--- |
| **Judul Produk** | **SIWIL** (*Sistem Informasi Wilayah Terpadu*) |
| **Mata Kuliah** | **Rekayasa Perangkat Lunak (RPL)** |
| **Dosen Pengampu**| **Yudhistira** |
| **Nama Pengembang**| **Muhammad Al Farel** |
| **NIM** | **41257124** |
| **Kelas** | **TI 1B 2025** |
| **Program Studi** | **Teknik Informatika** |
| **Tema / Topik** | **Smart Governance & E-Government Wilayah Desa, RW, dan RT Berbasis AI & Omnichannel Bot** |
| **Versi Dokumen** | **v2.5.0 (Final - Sesuai Implementasi / As-Built)** |
| **Status Dokumen** | **Disetujui untuk Konstruksi & Evaluasi Akademik** |
| **Tanggal Terbit** | **Maret 2026** |

---

## DAFTAR ISI
1. [BAB I: PENDAHULUAN](#bab-i-pendahuluan)
   - 1.1 Tujuan Dokumen (Purpose)
   - 1.2 Ruang Lingkup Produk (Scope of the System)
   - 1.3 Definisi, Istilah, dan Akronim (Definitions & Acronyms)
   - 1.4 Referensi Dokumen & Standar Acuan (References)
   - 1.5 Gambaran Umum Dokumen (Document Overview)
2. [BAB II: DESKRIPSI UMUM SISTEM](#bab-ii-deskripsi-umum-sistem)
   - 2.1 Latar Belakang Masalah Riil
   - 2.2 Visi & Nilai Strategis Produk
   - 2.3 Perspektif Produk & Arsitektur Konseptual
   - 2.4 Karakteristik Pengguna & Matriks Hak Akses (RBAC)
   - 2.5 Batasan Desain & Implementasi (Design Constraints)
   - 2.6 Asumsi dan Ketergantungan Sistem
3. [BAB III: SPESIFIKASI KEBUTUHAN FUNGSIONAL](#bab-iii-spesifikasi-kebutuhan-fungsional)
   - 3.1 Format User Story & Acceptance Criteria (Gherkin)
   - 3.2 Modul 1: Portal Transparansi Publik
   - 3.3 Modul 2: Autentikasi, IAM, & Verifikasi Warga
   - 3.4 Modul 3: Manajemen Master Data Kependudukan & Teritorial
   - 3.5 Modul 4: Layanan Administrasi & Persuratan Digital 3-Tier
   - 3.6 Modul 5: Pengaduan Warga Berbasis Geotagging & Foto Bukti
   - 3.7 Modul 6: Sistem Tanggap Darurat Cepat (SOS Button)
   - 3.8 Modul 7: Musyawarah Warga & Polling Demokrasi Digital
   - 3.9 Modul 8: Papan Informasi & Pengumuman Desa Omnichannel
   - 3.10 Modul 9: Tata Kelola Anggaran APBDes & Proyek Pembangunan GIS
   - 3.11 Modul 10: Pusat Konfigurasi Dinamis & Pengujian Sistem
   - 3.12 Modul 11: Layanan Mandiri Omnichannel (WhatsApp & Telegram Gateway)
   - 3.13 Modul 12: Audit Trail, Keamanan & Log Aktivitas Sistem
4. [BAB IV: SPESIFIKASI KEBUTUHAN NON-FUNGSIONAL (ISO/IEC 25010)](#bab-iv-spesifikasi-kebutuhan-non-fungsional-isoiec-25010)
   - 4.1 Functional Suitability (Kesesuaian Fungsional)
   - 4.2 Performance Efficiency (Efisiensi Kinerja)
   - 4.3 Compatibility & Interoperability (Kompatibilitas)
   - 4.4 Usability & Accessibility (Kebergunaan & Aksesibilitas)
   - 4.5 Reliability & Fault Tolerance (Keandalan & Ketahanan)
   - 4.6 Security & UU PDP Compliance (Keamanan & Regulasi Privasi)
   - 4.7 Maintainability (Kemudahan Pemeliharaan & Clean Code)
   - 4.8 Portability (Portabilitas)
5. [BAB V: SPESIFIKASI KHUSUS FITUR KECERDASAN BUATAN (AI ENGINE)](#bab-v-spesifikasi-khusus-fitur-kecerdasan-buatan-ai-engine)
   - 5.1 Arsitektur AI Engine (Google Gemini & Multi-Model Orchestration)
   - 5.2 Spesifikasi 12 Backend AI Function Calling Tools
   - 5.3 Knowledge Base RAG & Injeksi Konteks Dinamis
   - 5.4 Privacy Guardrails & Penegakan Regulasi UU PDP
   - 5.5 Ketahanan Sistem: Multi-Key Rotation & Rule-Based Fallback
6. [BAB VI: ANTARMUKA SISTEM EKSTERNAL](#bab-vi-antarmuka-sistem-eksternal)
   - 6.1 Antarmuka Pengguna (UI/UX)
   - 6.2 Antarmuka Perangkat Lunak (APIs & Integrasi Eksternal)
   - 6.3 Antarmuka Komunikasi Jaringan
7. [BAB VII: MATRIKS KETERTELUSURAN KEBUTUHAN (RTM)](#bab-vii-matriks-ketertelusuran-kebutuhan-rtm)

---

## BAB I: PENDAHULUAN

### 1.1 Tujuan Dokumen (Purpose)
Dokumen **Spesifikasi Kebutuhan Perangkat Lunak (Software Requirements Specification - SRS / PRD)** ini disusun untuk menetapkan seluruh spesifikasi fungsional, non-fungsional, arsitektural, dan spesifikasi integrasi kecerdasan buatan (*Artificial Intelligence*) untuk aplikasi web **SIWIL (Sistem Informasi Wilayah Terpadu)**. Dokumen ini menjadi acuan formal tunggal dalam perancangan arsitektur perangkat lunak (SDD), konstruksi kode sumber, pengujian terotomatisasi (*unit & feature testing*), implementasi CI/CD, hingga demonstrasi akhir sistem dalam mata kuliah **Rekayasa Perangkat Lunak (RPL)**.

### 1.2 Ruang Lingkup Produk (Scope of the System)
Aplikasi SIWIL adalah sistem perangkat lunak berbasis web terintegrasi yang mendigitalisasi tata kelola birokrasi pemerintahan desa dan rukun warga/tetangga secara *end-to-end*. Ruang lingkup sistem mencakup:
1. **Layanan Mandiri Warga (Citizen Self-Service):** Pengajuan persuratan administratif digital, pengecekan data Kartu Keluarga mandiri, pelaporan aduan infrastruktur dengan koordinat GPS dan foto bukti, partisipasi musyawarah/polling digital, serta tombol panik darurat (*Emergency SOS*).
2. **Otomasi Birokrasi Berjenjang (Hierarchical Workflow):** Persetujuan surat administratif bertingkat (Ketua RT $ightarrow$ Ketua RW $ightarrow$ Pemerintah Desa/Kelurahan) yang dilengkapi verifikasi Tanda Tangan Elektronik (TTE) berbasis QR Code Validator berstandar hukum.
3. **Transparansi Fiskal & Pembangunan (Public Governance):** Portal transparansi APBDes interaktif dan pemantauan progres proyek fisik desa berbasis peta geospasial (GIS).
4. **Kecerdasan Buatan Terintegrasi (SIWIL AI Engine):** Chatbot asisten cerdas berbasis Large Language Model (Google Gemini) yang dilengkapi 12 *function calling tools* untuk mengeksekusi *read-only retrieval* data riil wilayah secara aman tanpa melanggar privasi data.
5. **Keterhubungan Lintas Kanal (Omnichannel Gateway):** Integrasi pesan instan melalui microservice WhatsApp Bot dan Telegram Bot resmi untuk memfasilitasi warga yang tidak terbiasa menggunakan peramban web komputer.

### 1.3 Definisi, Istilah, dan Akronim (Definitions & Acronyms)
- **SIWIL:** Sistem Informasi Wilayah Terpadu.
- **SRS / PRD:** *Software Requirements Specification* / *Product Requirements Document*.
- **RT / RW:** Rukun Tetangga / Rukun Warga (Lembaga Kemasyarakatan Desa berjenjang).
- **NIK:** Nomor Induk Kependudukan (16 digit angka identitas penduduk Indonesia).
- **KK:** Kartu Keluarga (16 digit nomor kartu keluarga kependudukan).
- **TTE:** Tanda Tangan Elektronik yang disahkan dengan hash kriptografi dan QR Code verifikasi.
- **UU PDP:** Undang-Undang Republik Indonesia Nomor 27 Tahun 2022 tentang Pelindungan Data Pribadi.
- **RBAC:** *Role-Based Access Control* (Pengendalian akses berbasis peran pengguna).
- **LLM:** *Large Language Model* (Model bahasa kecerdasan buatan skala besar).
- **Function Calling:** Mekanisme di mana model LLM dapat memanggil fungsi/tools lokal di backend untuk mengambil data sistem secara terstruktur.
- **Gherkin:** Format bahasa terstruktur (*Given-When-Then*) untuk mendefinisikan kriteria penerimaan (*Acceptance Criteria*) pada pengujian perangkat lunak.
- **APBDes:** Anggaran Pendapatan dan Belanja Desa.

### 1.4 Referensi Dokumen & Standar Acuan (References)
1. **IEEE Std 830-1998:** *IEEE Recommended Practice for Software Requirements Specifications*.
2. **ISO/IEC/IEEE 29148:2018:** *Systems and software engineering — Life cycle processes — Requirements engineering*.
3. **ISO/IEC 25010:2011 / 2023:** *Systems and software Quality Requirements and Evaluation (SQuaRE) — System and software quality models*.
4. **Undang-Undang RI Nomor 27 Tahun 2022:** *Tentang Pelindungan Data Pribadi (UU PDP)*.
5. **Undang-Undang RI Nomor 6 Tahun 2014:** *Tentang Desa*.
6. **Laravel Documentation v11/v13:** *Official Framework & Architecture Guidelines*.

### 1.5 Gambaran Umum Dokumen (Document Overview)
Dokumen ini disusun ke dalam 7 bab logis: Bab I memuat latar belakang formal dan referensi acuan; Bab II mendeskripsikan perspektif sistem, pengguna, dan batasan umum; Bab III merinci seluruh kebutuhan fungsional dalam format User Story dan kriteria penerimaan Gherkin; Bab IV menjabarkan 8 karakteristik mutu non-fungsional berdasarkan ISO 25010; Bab V menguraikan spesifikasi arsitektur AI, function calling tools, dan mitigasi kebocoran data; Bab VI mendefinisikan antarmuka eksternal; dan Bab VII menyajikan matriks ketertelusuran kebutuhan (*Traceability Matrix*).

---

## BAB II: DESKRIPSI UMUM SISTEM

### 2.1 Latar Belakang Masalah Riil
Pelayanan publik dan administrasi kependudukan di tingkat rukun tetangga, rukun warga, hingga kantor kelurahan/desa di Indonesia hingga saat ini masih menghadapi berbagai hambatan fundamental:
1. **Rantai Birokrasi Manual yang Lambat & Melelahkan:** Untuk memperoleh selembar surat keterangan resmi (misal: SKTM, Domisili, Surat Pengantar Nikah), seorang warga harus meminta surat pengantar basah dari Ketua RT, membawanya ke rumah Ketua RW untuk meminta tanda tangan dan stempel kedua, lalu mengantre di loket kantor desa/kelurahan pada jam kerja dinas. Proses ini memakan waktu antara 2 hingga 5 hari kerja.
2. **Asimetri Waktu & Keterbatasan Pengurus Wilayah:** Ketua RT dan RW adalah tokoh masyarakat sukarela yang memiliki pekerjaan utama dan kesibukan pribadi, sehingga warga kerap kali harus berkunjung berulang kali hanya untuk menemui pengurus di kediamannya.
3. **Penyimpanan Terfragmentasi & Kerentanan Privasi (UU PDP):** Buku register kependudukan manual rawan robek, tercecer, dan sering kali dibiarkan terbuka sehingga NIK, nomor KK, dan nomor telepon warga dapat dilihat oleh pihak yang tidak berhak, bertentangan dengan amanat UU PDP No. 27 Tahun 2022.
4. **Ketiadaan Saluran Aspirasi & Pemantauan Anggaran Dua Arah:** Warga jarang dilibatkan dalam jajak pendapat (*polling*) pengambilan keputusan lingkungan, dan transparansi alokasi dana desa (APBDes) maupun progres fisik proyek infrastruktur masih minim tersosialisasikan.
5. **Kesenjangan Literasi Digital:** Banyak warga usia lanjut atau masyarakat umum yang enggan membuka aplikasi web desktop yang rumit, melainkan lebih terbiasa berkomunikasi melalui platform pesan instan seperti WhatsApp atau Telegram.

### 2.2 Visi & Nilai Strategis Produk
**SIWIL** dibangun sebagai platform *Smart Neighborhood & Village Governance Ecosystem* yang menjembatani seluruh pemangku kepentingan. Nilai strategis produk meliputi:
- **Zero Physical Paperwork:** Digitalisasi pengajuan berkas, verifikasi berjenjang nirkertas, dan legalitas surat via Tanda Tangan Elektronik ber-QR Code.
- **Amanah Regulasi UU PDP:** Isolasi data privat kependudukan secara deterministik dengan pembatasan hak akses berbasis peran (RBAC) dan *database multi-tenancy query scope*.
- **Omnichannel Service Inclusivity:** Pelayanan administrasi dan musyawarah yang dapat diakses tidak hanya melalui Web Portal, melainkan juga melalui Bot WhatsApp dan Bot Telegram.
- **Kecerdasan Buatan Terkelola:** Integrasi LLM Google Gemini dengan 12 *function calling tools* untuk memberikan layanan konsultasi prosedural dan *data retrieval* instan 24 jam sehari tanpa melanggar privasi.

### 2.3 Perspektif Produk & Arsitektur Konseptual
SIWIL merupakan sistem terdistribusi mandiri (*self-contained application*) berbasis *Clean Architecture* dan prinsip *SOLID*, yang terbagi ke dalam 4 lapisan:
1. **User Access Layer:** Web Responsive Browser (Desktop/Mobile), WhatsApp Messenger, dan Telegram Messenger.
2. **Gateway & Security Layer:** Web HTTP Middleware, Rate Limiting, Spatie RBAC Middleware, OTP WhatsApp Generator, dan UU PDP Guardrail.
3. **Core Application & Service Layer:** Logika bisnis terisolasi pada Service Classes (`ResidentService`, `DocumentRequestService`, `AiToolService`, `TelegramService`, `WhatsAppService`, `EmailNotificationService`).
4. **Data Access & External Engine Layer:** Eloquent ORM (20 Models dengan UUID v4), MySQL/SQLite, DomPDF Engine, dan AI Provider (Google Gemini API).

### 2.4 Karakteristik Pengguna & Matriks Hak Akses (RBAC)
Sistem memiliki 5 aktor terdaftar dan 1 entitas publik dengan spesifikasi wewenang sebagai berikut:

| Peran (Role) | Karakteristik & Deskripsi Pengguna | Kewenangan Utama |
| :--- | :--- | :--- |
| **Publik (Tamu)** | Pengunjung internet umum tanpa autentikasi. | Melihat profil desa, transparansi APBDes, statistik agregat kependudukan, dan proyek pembangunan. |
| **Warga** | Penduduk resmi terdaftar pada database desa dan terverifikasi memiliki NIK & No. KK. | Mengajukan surat, cek status permohonan, lapor aduan GPS/foto, picu tombol darurat SOS, voting musyawarah, konsultasi AI, unduh surat digital PDF. |
| **Ketua RT** | Pengurus Rukun Tetangga (Verifikator Tingkat 1). | Verifikasi permohonan surat warga RT binaan, kelola kependudukan RT, verifikasi registrasi warga baru, tangani aduan warga lokal, respons insiden darurat SOS, buat musyawarah tingkat RT. |
| **Ketua RW** | Pengurus Rukun Warga (Verifikator Tingkat 2). | Verifikasi surat tingkat RW, pantau statistik warga lintas RT di RW binaan, disposisi aduan lingkungan, buat musyawarah tingkat RW. |
| **Admin Desa** | Perangkat Desa / Kelurahan (Verifikator Akhir & Eksekutor). | Verifikasi akhir & penerbitan surat resmi ber-TTE stempel desa, manajemen proyek APBDes & pembangunan fisik, buat pengumuman resmi desa, kelola master wilayah. |
| **Super Admin** | Administrator Sistem & Infrastruktur IT. | Konfigurasi sistem global (AI Key, Bot WA/TG, SMTP Mailer), kelola multi-desa, akses penuh seluruh data, inspeksi log audit forensik. |

### 2.5 Batasan Desain & Implementasi (Design Constraints)
1. **Lingkungan Server & Runtime:** Berjalan pada PHP 8.3/8.4, Node.js v18+ (untuk bot microservice), web server Apache/Nginx, dan basis data MySQL 8.0 / SQLite 3.
2. **Kepatuhan Privasi Data:** NIK, Nomor KK, Nomor WhatsApp, dan data alamat warga tidak boleh diekspos melalui API publik atau respons AI kepada sesama warga.
3. **Integritas TTE:** QR Code verifikasi dokumen harus mengarah ke URL validasi publik resmi yang memeriksa tanda tangan digital tanpa menampilkan data privat sensitif.
4. **Keamanan Kredensial:** Seluruh kata sandi di-hash dengan algoritma Bcrypt (cost factor 12); API Keys AI dan token bot disamarkan (*masked*) pada antarmuka admin.

### 2.6 Asumsi dan Ketergantungan Sistem
- Pengguna diasumsikan memiliki perangkat yang terhubung ke jaringan internet (peramban modern atau aplikasi WhatsApp/Telegram).
- Layanan AI bergantung pada ketersediaan koneksi internet ke API Google Gemini (dengan fallback fail-safe otomatis ke aturan pencocokan lokal jika sambungan terputus).
- Pengiriman OTP pemulihan sandi bergantung pada aktifnya microservice WhatsApp Gateway port 3000.

---

## BAB III: SPESIFIKASI KEBUTUHAN FUNGSIONAL

### 3.1 Format User Story & Acceptance Criteria (Gherkin)
Setiap kebutuhan fungsional didefinisikan menggunakan format *User Story* standar industri:
> **Sebagai** `[Peran Pengguna]`, **saya ingin** `[Tindakan / Fitur]`, **sehingga** `[Nilai Bisnis / Manfaat]`.

Dan divalidasi dengan *Acceptance Criteria* berstandar format **Gherkin**:
> **Given** `[Kondisi Awal / Prekondisi]`
> **When** `[Tindakan / Pemicu Kejadian]`
> **Then** `[Hasil yang Diharapkan / Postkondisi]`

---

### 3.2 Modul 1: Portal Transparansi Publik (Public Portal)

#### US-01: Akses Transparansi APBDes & Statistik
- **User Story:** Sebagai warga masyarakat atau pengunjung publik, saya ingin mengakses informasi realisasi APBDes dan statistik demografi kependudukan secara terbuka tanpa perlu login, sehingga tercipta transparansi anggaran dan akuntabilitas pemerintah desa.
- **Acceptance Criteria (Gherkin):**
  ```gherkin
  Scenario: Pengunjung publik membuka halaman transparansi APBDes
    Given Pengguna membuka peramban web dan mengakses URL "/portal/apbdes"
    When Pengguna memilih tahun anggaran tertentu dari dropdown filter
    Then Sistem menampilkan breakdown 3 pilar: Pendapatan, Belanja, dan Pembiayaan
    And Sistem menghitung persentase serapan dana riil terhadap pagu anggaran
    And Sistem tidak menampilkan data keuangan yang berstatus draf rahasia
  ```

#### US-02: Pemantauan Proyek Pembangunan Desa
- **User Story:** Sebagai warga, saya ingin memantau daftar proyek pembangunan fisik desa beserta nilai anggaran, sumber dana, dan persentase progres fisiknya, sehingga saya dapat mengawasi pembangunan di lingkungan saya.
- **Acceptance Criteria (Gherkin):**
  ```gherkin
  Scenario: Pengunjung melihat progres proyek pembangunan
    Given Pengguna membuka halaman "/portal/pembangunan"
    When Halaman selesai dimuat
    Then Sistem menampilkan daftar kartu proyek dengan judul, sumber dana, pagu biaya, dan tanggal pelaksanaan
    And Sistem menampilkan visual progress bar (0% - 100%) dan dokumentasi foto riil proyek
  ```

---

### 3.3 Modul 2: Autentikasi, IAM, & Verifikasi Warga

#### US-03: Pendaftaran Mandiri Warga Berbasis Validasi Silang (Zero Fake Account)
- **User Story:** Sebagai warga baru, saya ingin mendaftarkan akun SIWIL mandiri menggunakan NIK dan No. KK saya, sehingga akun saya terverifikasi secara sah dan mencegah pembuatan akun palsu.
- **Acceptance Criteria (Gherkin):**
  ```gherkin
  Scenario: Registrasi warga dengan NIK dan KK yang valid di database kependudukan
    Given Calon pengguna berada di halaman "/register"
    When Pengguna menginput NIK 16 digit, No. KK 16 digit, nama lengkap, dan kata sandi
    And NIK dan No. KK tersebut cocok dengan data kependudukan resmi desa
    Then Sistem membuat akun baru dengan status "pending"
    And Sistem menampilkan pesan bahwa akun sedang menunggu verifikasi oleh Ketua RT atau Admin Desa
    And Pengguna belum dapat login hingga diverifikasi
  ```
  ```gherkin
  Scenario: Registrasi ditolak jika NIK tidak terdaftar di master kependudukan
    Given Calon pengguna memasukkan NIK fiktif yang tidak ada di tabel "residents"
    When Pengguna menekan tombol "Daftar Sekarang"
    Then Sistem menolak proses registrasi dan menampilkan pesan kesalahan validasi "NIK tidak ditemukan dalam basis data desa"
  ```

#### US-04: Pemulihan Kata Sandi dengan WhatsApp OTP
- **User Story:** Sebagai pengguna yang lupa kata sandi, saya ingin menerima kode OTP 6 digit melalui nomor WhatsApp terdaftar saya, sehingga saya dapat memulihkan akses akun secara aman dan cepat.
- **Acceptance Criteria (Gherkin):**
  ```gherkin
  Scenario: Permintaan kode OTP pemulihan kata sandi
    Given Pengguna berada di halaman "/forgot-password"
    When Pengguna menginput NIK atau nomor WhatsApp terdaftar
    Then Sistem men-generate 6 digit kode OTP acak dan menyimpannya di cache selama 5 menit
    And Sistem mengirimkan pesan OTP secara instan ke nomor WhatsApp pengguna via WhatsApp Gateway
    And Sistem membatasi pengiriman ulang OTP dengan throttle waktu tunggu 60 detik
  ```
  ```gherkin
  Scenario: Verifikasi kode OTP dan pembaruan kata sandi
    Given Pengguna memasukkan kode OTP pada halaman "/forgot-password/verify"
    When Kode OTP cocok dan belum melewati batas waktu 5 menit
    Then Pengguna diarahkan ke form penetapan kata sandi baru
    And Setelah kata sandi diubah, hash sandi baru tersimpan dengan Bcrypt
  ```

---

### 3.4 Modul 3: Manajemen Master Data Kependudukan & Teritorial

#### US-05: Pengelolaan Data Kartu Keluarga & Anggota Keluarga
- **User Story:** Sebagai Ketua RT, saya ingin mencatat dan memperbarui data Kartu Keluarga serta biodata warga di lingkungan RT saya, sehingga data kependudukan selalu mutakhir.
- **Acceptance Criteria (Gherkin):**
  ```gherkin
  Scenario: Ketua RT menambahkan data warga baru di RT binaannya
    Given Ketua RT telah berhasil login dan berada di menu "/dashboard/residents/create"
    When Ketua RT mengisi formulir NIK, Nama Lengkap, Tanggal Lahir, Jenis Kelamin, Agama, dan Hubungan Keluarga
    And Menekan tombol simpan
    Then Data warga baru tersimpan dengan relasi otomatis ke RT dan RW dari Ketua RT tersebut
    And Warga dengan NIK tersebut kini berhak melakukan registrasi akun mandiri
  ```

#### US-06: Tinjauan Mandiri "Keluarga Saya" oleh Warga
- **User Story:** Sebagai warga, saya ingin melihat daftar seluruh anggota keluarga yang terdaftar dalam Kartu Keluarga saya, sehingga saya dapat memastikan kebenaran data keluarga tanpa melihat data keluarga orang lain.
- **Acceptance Criteria (Gherkin):**
  ```gherkin
  Scenario: Warga membuka menu Data Keluarga Saya
    Given Warga telah login dan mengakses "/dashboard/my-family"
    When Sistem membaca No. KK dari profil pengguna
    Then Sistem menampilkan Nomor KK, Alamat Domisili, Nama Kepala Keluarga, dan tabel seluruh anggota keluarga
    And Sistem membatasi data hanya untuk anggota yang memiliki No. KK yang sama persis
  ```

---

### 3.5 Modul 4: Layanan Administrasi & Persuratan Digital 3-Tier

#### US-07: Pengajuan Permohonan Surat Digital Mandiri
- **User Story:** Sebagai warga, saya ingin mengajukan surat permohonan administratif (SKTM, Domisili, SKU, dll.) secara online disertai lampiran berkas pendukung, sehingga saya tidak perlu membawa berkas fisik ke rumah RT.
- **Acceptance Criteria (Gherkin):**
  ```gherkin
  Scenario: Warga mengajukan Surat Keterangan Usaha (SKU)
    Given Warga telah login dan berada di menu "/dashboard/documents/create"
    When Warga memilih jenis surat "Surat Keterangan Usaha"
    And Mengisi keperluan surat dan mengunggah foto berkas pendukung
    Then Sistem men-generate nomor permohonan unik (format: "DOC-YYYYMMDD-XXXX")
    And Sistem menetapkan status awal permohonan menjadi "DIAJUKAN_RT"
    And Sistem mengirimkan notifikasi internal dan pesan instan ke Ketua RT terkait
  ```

#### US-08: Alur Persetujuan Bertingkat (RT -> RW -> Desa) & TTE QR Code
- **User Story:** Sebagai aparatur wilayah (RT, RW, dan Desa), saya ingin memverifikasi, menyetujui, atau menolak permohonan surat sesuai jenjang kewenangan saya, dan menerbitkan surat PDF bertanda tangan digital resmi.
- **Acceptance Criteria (Gherkin):**
  ```gherkin
  Scenario: Alur persetujuan surat 3-tier hingga terbit
    Given Sebuah permohonan surat berstatus "DIAJUKAN_RT"
    When Ketua RT menyetujui surat tersebut
    Then Status berubah menjadi "DIAJUKAN_RW" dan tercatat di riwayat verifikasi
    When Ketua RW menyetujui surat tersebut
    Then Status berubah menjadi "DIAJUKAN_DESA"
    When Admin Desa menyetujui surat tersebut
    Then Status berubah menjadi "DISETUJUI" / "SELESAI"
    And Sistem menyematkan stempel desa dan TTE digital
    And Sistem men-generate dokumen PDF resmi lengkap dengan QR Code verifikasi keabsahan
    And Warga menerima notifikasi otomatis bahwa surat siap diunduh
  ```

---

### 3.6 Modul 5: Pengaduan Warga Berbasis Geotagging & Foto Bukti

#### US-09: Pelaporan Masalah Lingkungan dengan Koordinat GPS
- **User Story:** Sebagai warga, saya ingin melaporkan masalah fasilitas publik (misal: jalan rusak, tumpukan sampah liar, lampu mati) disertai foto bukti dan titik koordinat GPS otomatis, sehingga petugas dapat menemukan lokasi dengan akurat.
- **Acceptance Criteria (Gherkin):**
  ```gherkin
  Scenario: Warga mengirimkan laporan aduan dengan lokasi GPS
    Given Warga membuka formulir "/dashboard/complaints/create"
    When Warga mengisikan judul, deskripsi aduan, mengunggah foto bukti, dan mengizinkan akses geolocation browser
    Then Sistem menyimpan koordinat lintang (latitude) dan bujur (longitude) secara presisi
    And Sistem menetapkan status aduan menjadi "PENDING"
    And Aduan otomatis terdisposisi ke dashboard Ketua RT dan RW tempat insiden terjadi
  ```

#### US-10: Respons dan Penanganan Pengaduan oleh Pengurus Wilayah
- **User Story:** Sebagai Ketua RT/RW atau Desa, saya ingin memperbarui progres penanganan aduan warga dari status diterima, diproses, hingga selesai ditangani, sehingga warga mengetahui tindak lanjut laporannya.
- **Acceptance Criteria (Gherkin):**
  ```gherkin
  Scenario: Petugas mengubah status penanganan aduan
    Given Petugas membuka rincian aduan warga yang berstatus "PENDING"
    When Petugas mengubah status menjadi "PROCESSING" dan menginput catatan penanganan
    Then Warga pelapor menerima notifikasi real-time via WhatsApp, Telegram, dan In-App
    When Petugas menuntaskan penanganan dan mengubah status menjadi "RESOLVED"
    Then Siklus pengaduan ditutup dengan timestamp penyelesaian yang tercatat
  ```

---

### 3.7 Modul 6: Sistem Tanggap Darurat Cepat (SOS Button)

#### US-11: Pemicu Tombol Darurat (Emergency SOS)
- **User Story:** Sebagai warga yang mengalami keadaan darurat (Medis, Kebakaran, Kriminal, Bencana), saya ingin menekan tombol darurat SOS yang langsung menyebarkan peringatan bahaya beserta titik lokasi saya ke seluruh pengurus wilayah, sehingga bantuan segera datang.
- **Acceptance Criteria (Gherkin):**
  ```gherkin
  Scenario: Warga menekan tombol darurat SOS
    Given Warga berada di dashboard dan menekan tombol merah "EMERGENCY SOS"
    When Warga mengonfirmasi kategori darurat ("MEDIS" / "KEBAKARAN" / "KRIMINAL")
    Then Sistem mengambil titik koordinat GPS terkini pengguna
    And Sistem menyimpan record di tabel "emergencies" dengan status "ACTIVE"
    And Banner merah kedip peringatan darurat seketika muncul di dashboard Ketua RT, Ketua RW, dan Admin Desa
    And Petugas dapat melihat rute ke titik lokasi koordinat di peta digital
  ```

---

### 3.8 Modul 7: Musyawarah Warga & Polling Demokrasi Digital

#### US-12: Pelaksanaan Polling Lingkungan Berintegritas (One Citizen One Vote)
- **User Story:** Sebagai warga yang berhak pilih, saya ingin memberikan satu suara pada jajak pendapat musyawarah lingkungan dan melihat hasilnya secara transparan setelah polling ditutup.
- **Acceptance Criteria (Gherkin):**
  ```gherkin
  Scenario: Warga memberikan suara pada musyawarah aktif
    Given Terdapat polling musyawarah yang berstatus aktif di wilayah RT/RW warga
    When Warga memilih salah satu opsi dan menekan tombol "Kirim Suara"
    Then Suara tercatat di tabel "poll_votes" dengan kunci unik (poll_id, user_id)
    And Sistem memblokir warga tersebut agar tidak dapat memilih untuk kedua kalinya
  ```
  ```gherkin
  Scenario: Penutupan polling dan penetapan pemenang otomatis
    Given Waktu batas aktif polling ("expires_at") telah terlewati
    When Sistem atau pengurus menutup polling menjadi "CLOSED"
    Then Sistem secara otomatis menghitung opsi dengan suara terbanyak ("winning_option")
    And Hasil akhir musyawarah di-broadcast ke seluruh warga melalui WhatsApp dan Telegram
  ```

---

### 3.9 Modul 8: Papan Informasi & Pengumuman Desa Omnichannel

#### US-13: Penerbitan dan Penyiaran Pengumuman Wilayah
- **User Story:** Sebagai Admin Desa atau Ketua RT, saya ingin mempublikasikan surat edaran atau berita pengumuman resmi yang langsung terdistribusi ke seluruh saluran warga (In-App, WhatsApp, Telegram, Email).
- **Acceptance Criteria (Gherkin):**
  ```gherkin
  Scenario: Admin Desa menerbitkan pengumuman resmi baru
    Given Admin Desa berada di form "/dashboard/announcements/create"
    When Admin mengisi judul, konten berita edaran, dan mencentang opsi publish
    Then Pengumuman tampil di beranda portal publik dan dashboard back-office
    And Sistem secara simultan mengirimkan pesan ringkasan edaran ke WhatsApp, Telegram, dan Email seluruh warga
  ```

---

### 3.10 Modul 9: Tata Kelola Anggaran APBDes & Proyek Pembangunan GIS

#### US-14: Pencatatan Pagu Anggaran & Realisasi Fiskal
- **User Story:** Sebagai Admin Desa, saya ingin mencatat pos anggaran APBDes per tahun anggaran dan memperbarui realisasi serapan anggarannya, sehingga data laporan keuangan desa akurat dan transparan.
- **Acceptance Criteria (Gherkin):**
  ```gherkin
  Scenario: Admin Desa menambah pos belanja APBDes
    Given Admin Desa berada di menu "/dashboard/developments" tab APBDes
    When Admin memasukkan tahun anggaran, kategori "Belanja", uraian kegiatan, pagu anggaran, dan realisasi
    Then Data tersimpan di tabel "budgets"
    And Portal publik "/portal/apbdes" otomatis memperbarui kalkulasi total pendapatan, belanja, dan sisa lebih pembiayaan anggaran (SiLPA)
  ```

---

### 3.11 Modul 10: Pusat Konfigurasi Dinamis & Pengujian Sistem

#### US-15: Manajemen Konfigurasi Tanpa Restart Server
- **User Story:** Sebagai Super Administrator, saya ingin mengatur kredensial API AI (Google Gemini, OpenAI, Groq), token WhatsApp, token Telegram, dan konfigurasi SMTP Mail langsung dari antarmuka web serta melakukan diagnostik koneksi secara instan.
- **Acceptance Criteria (Gherkin):**
  ```gherkin
  Scenario: Super Admin menguji konektivitas integrasi eksternal
    Given Super Admin berada di menu "/dashboard/configuration"
    When Menekan tombol "Test AI Connection"
    Then Sistem melakukan panggilan tes ke model LLM dan menampilkan status keberhasilan serta latensi respons
    When Menekan tombol "Test WhatsApp Gateway"
    Then Sistem memverifikasi koneksi socket/HTTP ke microservice port 3000
    And Kredensial sensitif tetap disamarkan (masked) di layar
  ```

---

### 3.12 Modul 11: Layanan Mandiri Omnichannel (WhatsApp & Telegram Gateway)

#### US-16: Pengajuan Surat dan Pengecekan Berkas via WhatsApp & Telegram
- **User Story:** Sebagai warga yang tidak membuka komputer, saya ingin mengajukan surat dan mengunduh berkas PDF surat resmi langsung dari obrolan WhatsApp atau Telegram saya.
- **Acceptance Criteria (Gherkin):**
  ```gherkin
  Scenario: Warga mengunduh PDF surat resmi melalui pesan WhatsApp
    Given Nomor WhatsApp warga telah tertaut dengan akun SIWIL
    When Warga mengirimkan pesan menu surat dan memilih nomor surat yang telah disetujui
    Then Bot WhatsApp mengirimkan berkas dokumen PDF resmi ber-barcode langsung ke ruang obrolan warga
  ```

---

### 3.13 Modul 12: Audit Trail, Keamanan & Log Aktivitas Sistem

#### US-17: Perekaman Jejak Aktivitas Forensik (Audit Logs)
- **User Story:** Sebagai Super Administrator atau Auditor Sistem, saya ingin setiap tindakan penambahan, pengubahan, dan penghapusan data penting terekam otomatis beserta identitas pengguna, IP address, dan detail perubahan, sehingga integritas sistem terjamin.
- **Acceptance Criteria (Gherkin):**
  ```gherkin
  Scenario: Pencatatan mutasi data ke tabel audit_logs
    Given Pengguna melakukan aksi persetujuan surat atau pengubahan data kependudukan
    When Transaksi database berhasil di-commit
    Then Sistem secara otomatis menuliskan baris baru pada tabel "audit_logs" memuat aksi (CREATE/UPDATE/DELETE), ID pengguna, waktu, dan rekaman perubahan atribut
    And Catatan audit log bersifat append-only dan tidak dapat dimanipulasi melalui antarmuka
  ```

---

## BAB IV: SPESIFIKASI KEBUTUHAN NON-FUNGSIONAL (ISO/IEC 25010)

Kebutuhan non-fungsional aplikasi SIWIL disusun secara komprehensif mengacu pada 8 pilar standar kualitas perangkat lunak internasional **ISO/IEC 25010**:

```
                                ISO/IEC 25010
                          Karakteristik Mutu Perangkat Lunak
                                       │
     ┌────────────────┬────────────────┼────────────────┬────────────────┐
     ▼                ▼                ▼                ▼                ▼
Functional       Performance      Compatibility      Usability      Reliability
Suitability      Efficiency       & Interoperability & Access       & Resilience
     │                │                │                │                │
     ▼                ▼                ▼                ▼                ▼
  Security       Maintainability   Portability
  & UU PDP       & Clean Code      & Standards
```

### 4.1 Functional Suitability (Kesesuaian Fungsional)
- **Kelengkapan Fungsional:** Sistem mencakup 100% kebutuhan administrasi persuratan sipil 3-tier, manajemen master kependudukan, pengaduan masyarakat, sistem darurat SOS, keterbukaan APBDes, serta integrasi AI cerdas.
- **Kebenaran Fungsional:** Perhitungan pagu APBDes, persentase serapan, agregasi suara polling (*one citizen one vote*), dan penentuan status berjenjang surat dijamin akurat secara matematis dan prosedural.

### 4.2 Performance Efficiency (Efisiensi Kinerja)
- **Waktu Respons Halaman:** Waktu pemuatan halaman web rata-rata tidak boleh melebihi **1.5 detik** pada koneksi internet pita lebar standar (4G/Fiber).
- **Efisiensi Kueri Basis Data:** Seluruh pengambilan data jamak (misal: daftar warga, riwayat surat) wajib menggunakan relasi *Eager Loading* (`with(...)`) untuk mengeliminasi *N+1 Query Problem*.
- **Paginasi Data:** Tabel data master dibatasi maksimum 10–25 rekaman per halaman untuk menjaga konsumsi memori backend tetap rendah.
- **Waktu Respons AI:** Layanan konsultasi asisten cerdas AI Gemini merespons pertanyaan pengguna dalam waktu kurang dari **3.0 detik** untuk query teks standar dan kurang dari **5.0 detik** untuk query yang melibatkan *function calling tools*.

### 4.3 Compatibility & Interoperability (Kompatibilitas)
- **Kompatibilitas Peramban (Cross-Browser):** Aplikasi wajib berfungsi sempurna dan konsisten pada seluruh peramban modern utama (Google Chrome, Mozilla Firefox, Microsoft Edge, Safari, Opera).
- **Interoperabilitas Pesan Instan:** Sistem mampu berkomunikasi dua arah secara mulus dengan WhatsApp Business Microservice (Port 3000) dan Telegram Bot API resmi melalui format JSON RESTful API.

### 4.4 Usability & Accessibility (Kebergunaan & Aksesibilitas)
- **Desain Adaptif (Mobile-First Responsive):** Tata letak antarmuka dirancang menyesuaikan resolusi layar ponsel cerdas (min. 360px), tablet, hingga layar desktop Full HD (1920x1080).
- **Kontras & Keterbacaan Antarmuka:** Tipografi menggunakan Google Font *Outfit* dengan skala hierarki teks yang jelas serta memenuhi rasio kontras warna standar WCAG 2.1 AA.
- **Dukungan Mode Terang & Gelap (Dark/Light Mode):** Sistem menyediakan sakelar tema instan yang menyimpan preferensi visual pengguna secara persisten di penyimpanan peramban (*localStorage*).

### 4.5 Reliability & Fault Tolerance (Keandalan & Ketahanan)
- **Ketersediaan Sistem (Uptime):** Target ketersediaan layanan sistem adalah **99.5%** selama masa operasional aktif.
- **Toleransi Kegagalan Komunikasi Luar (Safe Third-Party Fail-Safe):** Jika server microservice WhatsApp atau API Telegram mengalami *downtime*, transaksi penyimpanan data internal aplikasi (surat, aduan, polling) tetap berhasil dieksekusi tanpa memicu *fatal error* HTTP 500 (*non-blocking asynchronous try-catch*).
- **Rotasi API Key Otomatis:** Apabila kuota API Key utama Google Gemini habis (*rate limit HTTP 429*), sistem secara otomatis beralih ke deretan kunci API cadangan yang tersimpan dalam sistem konfigurasi.

### 4.6 Security & UU PDP Compliance (Keamanan & Regulasi Privasi)
- **Kepatuhan UU No. 27 Tahun 2022 (UU PDP):**
  - Data Pribadi Spesifik (Nomor Induk Kependudukan / NIK, Nomor Kartu Keluarga / No. KK, data identitas keluarga) dilindungi secara ketat.
  - Akses data dibatasi oleh *query scoping* di level model (`accessibleBy(User $user)`).
  - Warga dilarang keras dapat melihat biodata warga lain di luar Kartu Keluarganya sendiri.
  - Asisten AI dibekali aturan sistem keras (*guardrails*) untuk menolak membocorkan NIK, nomor HP, atau alamat warga kepada akun warga biasa.
- **Kriptografi & Enkripsi Kredensial:** Seluruh kata sandi di-hash menggunakan algoritma **Bcrypt** dengan *cost factor* 12. Kode OTP verifikasi WhatsApp disimpan dalam cache dengan masa berlaku tepat 5 menit.
- **Pencegahan Eksploitasi OWASP Top 10:**
  - *SQL Injection Prevention:* 100% kueri basis data menggunakan Eloquent ORM atau *prepared PDO parameter binding*.
  - *Cross-Site Scripting (XSS):* Seluruh keluaran Blade template di-escape secara otomatis menggunakan kurung kurawal ganda `{{ ... }}`.
  - *Cross-Site Request Forgery (CSRF):* Setiap formulir mutasi data web dilindungi oleh token `@csrf`.

### 4.7 Maintainability (Kemudahan Pemeliharaan & Clean Code)
- **Kepatuhan Gaya Kode (PSR-12 & Pint):** Seluruh kode sumber PHP mengikuti standar PSR-12 dan diformat secara otomatis menggunakan Laravel Pint.
- **Penerapan Prinsip SOLID & Clean Architecture:** Logika bisnis dipisahkan secara tegas dari Controller ke Service Classes independen, memudahkan pengujian unit terisolasi.
- **Otomatisasi Pengujian (Automated Test Suite):** Memiliki lebih dari 100 kasus pengujian unit dan fitur terotomatisasi (*PHPUnit*) dengan cakupan menyeluruh pada alur autentikasi, persuratan, polling, dan keamanan AI.

### 4.8 Portability (Portabilitas)
- **Portabilitas Lingkungan:** Konfigurasi aplikasi diisolasi melalui variabel lingkungan (`.env`) dan tabel dinamis (`system_configurations`), memungkinkan aplikasi dijalankan secara identik di lingkungan Local (Laragon/XAMPP), VPS Linux (Ubuntu), maupun platform Cloud PaaS/Docker.

---

## BAB V: SPESIFIKASI KHUSUS FITUR KECERDASAN BUATAN (AI ENGINE)

```
┌─────────────────────────────────────────────────────────────────────────────────────────┐
│                                   SIWIL AI ARCHITECTURE                                 │
│                                                                                         │
│  [User Question] ──► [System Prompt + User Context + UU PDP Guardrail]                 │
│                                     │                                                   │
│                                     ▼                                                   │
│                     ┌───────────────────────────────┐                                   │
│                     │      Google Gemini LLM        │                                   │
│                     │  (Primary: Gemini 2.5 Flash)  │                                   │
│                     └───────────────┬───────────────┘                                   │
│                                     │                                                   │
│                  ┌──────────────────┴──────────────────┐                                │
│                  ▼                                     ▼                                │
│         [Direct Text Answer]               [Function Calling Request]                   │
│                                                        │                                │
│                                                        ▼                                │
│                                            ┌───────────────────────┐                    │
│                                            │     AiToolService     │                    │
│                                            │ (12 Backend RBAC Tools│                    │
│                                            └───────────┬───────────┘                    │
│                                                        │                                │
│                                                        ▼                                │
│                                            [Eloquent Scoped Query]                      │
│                                                        │                                │
│                                                        ▼                                │
│                                            [Safe Filtered JSON]                         │
│                                                        │                                │
│                                                        ▼                                │
│  [Final Answer to User] ◄────────────────── [Gemini Synthesizer]                        │
└─────────────────────────────────────────────────────────────────────────────────────────┘
```

### 5.1 Arsitektur AI Engine (Google Gemini & Multi-Model Orchestration)
SIWIL mengintegrasikan mesin kecerdasan buatan berbasis *Large Language Model* dengan **Google Gemini** sebagai provider utama (didukung model `gemini-2.5-flash` dan `gemini-1.5-flash`). Arsitektur ini dirancang untuk mampu beralih ke provider OpenAI (`gpt-4o`), Groq (`llama-3`), atau LLM Lokal (*Ollama*) secara dinamis melalui konfigurasi basis data `system_configurations`.

### 5.2 Spesifikasi 12 Backend AI Function Calling Tools
AI tidak diperbolehkan mengarang bebas (*halusinasi*) terhadap fakta kependudukan dan administrasi wilayah. AI dibekali 12 *function calling tools* deterministik yang terisolasi di class `App\Services\AiToolService`:

| No | Nama Function Tool | Deskripsi Fungsional Tool | Batasan Otorisasi & Filter Data |
| :-: | :--- | :--- | :--- |
| 1 | `fetch_dashboard_data` | Mengambil ringkasan metrik statistik ringkas dashboard (total surat, aduan aktif, dsb). | Data disesuaikan dengan scope hak akses pengguna yang sedang login. |
| 2 | `fetch_residents_and_areas` | Mengambil data demografi kependudukan dan struktur hierarki wilayah. | **Khusus Pengurus (RT, RW, Desa, Superadmin)**. Diblokir total untuk akun Warga. |
| 3 | `fetch_announcements` | Mengambil daftar artikel pengumuman dan surat edaran resmi desa. | Mengambil pengumuman yang berstatus aktif/terbit. |
| 4 | `fetch_document_requests` | Mengambil daftar permohonan surat administrasi warga beserta status terkininya. | Warga hanya menerima data surat miliknya sendiri; Pengurus menerima sesuai wilayahnya. |
| 5 | `fetch_complaints` | Mengambil daftar laporan pengaduan masyarakat dan progres penanganannya. | Warga hanya menerima aduan miliknya; Pengurus menerima aduan di yurisdiksinya. |
| 6 | `fetch_polls` | Mengambil daftar musyawarah/polling aktif, daftar opsi pilihan, dan status vote pengguna. | Sesuai level jangkauan wilayah polling (RT / RW / Desa). |
| 7 | `fetch_family_data` | Mengambil data rincian Kartu Keluarga dan biodata anggota keluarga pemohon. | **Wajib sama No. KK-nya**. Warga dilarang melihat KK keluarga lain. |
| 8 | `fetch_development_and_budgets` | Mengambil data proyek fisik pembangunan desa dan alokasi pagu APBDes. | Khusus pengurus atau portal keterbukaan informasi. |
| 9 | `fetch_audit_logs` | Mengambil catatan jejak audit aktivitas dan manipulasi sistem. | **Khusus Super Administrator & Pimpinan Wilayah**. |
| 10 | `fetch_users_directory` | Mengambil data akun pengguna sistem dan status verifikasi akun. | **Khusus Super Admin dan Admin Desa**. |
| 11 | `fetch_ai_knowledge` | Mengambil data basis pengetahuan resmi (SOP pelayanan, sejarah desa, kontak darurat). | Dapat diakses seluruh peran sebagai referensi aturan wilayah. |
| 12 | `fetch_current_user_profile` | Mengambil data biodata dan profil lengkap dari akun yang sedang login saat ini. | Mengembalikan profil privat milik pengguna terotentikasi. |

### 5.3 Knowledge Base RAG & Injeksi Konteks Dinamis
Sistem menyediakan modul `ai_knowledge` di mana administrator dapat mendaftarkan materi pengetahuan lokal (misal: syarat penerbitan surat domisili, jadwal posyandu desa, batas waktu penerimaan tamu menginap di RT). Setiap kali pengguna berinteraksi dengan AI:
1. Konteks identitas pengguna (Nama, Peran, ID Wilayah RT/RW/Desa) disuntikkan secara dinamis ke dalam *system prompt*.
2. AI secara otomatis memanggil tool `fetch_ai_knowledge` untuk mengambil klausul SOP resmi desa yang relevan.
3. Respons akhir AI dirajut (*synthesized*) berbasis data faktual pengetahuan resmi tersebut, mencegah terjadinya kesalahan informasi prosedural kepada warga.

### 5.4 Privacy Guardrails & Penegakan Regulasi UU PDP
Sesuai amanat **Undang-Undang Nomor 27 Tahun 2022 tentang Pelindungan Data Pribadi**, AI Engine SIWIL ditanamkan *guardrails* berlapis pada *system instruction*:
> *"PERINGATAN PRIVASI (UU PDP): Jika pengguna memiliki peran WARGA, Anda DILARANG KERAS memberikan informasi NIK, Nomor KK, Nomor Telepon/WhatsApp, dan Alamat Lengkap milik warga lain. Jika pengguna meminta data pribadi orang lain, tolak dengan sopan dan jelaskan bahwa data kependudukan dilindungi oleh Undang-Undang Pelindungan Data Pribadi."*

Selain di tingkat *prompt*, keamanan diperkuat di level kode backend (`AiToolService.php`): jika akun peran `WARGA` mencoba mengeksekusi tool `fetch_residents_and_areas`, backend secara deterministik memotong data kembalian (*empty payload*) dan mengembalikan pesan penolakan otorisasi.

### 5.5 Ketahanan Sistem: Multi-Key Rotation & Rule-Based Fallback
Untuk menjamin asisten AI tetap beroperasi secara prima tanpa interupsi:
1. **Rotasi Multi-API Key:** Kunci API Google Gemini disimpan dalam format array JSON di database (`system_configurations.ai_api_keys`). Jika panggilan API mengalami *HTTP 429 (Quota Exceeded)*, sistem secara otomatis merotasi pemanggilan ke kunci API berikutnya.
2. **Deterministic Smart Fallback:** Apabila seluruh kunci API luar mengalami gangguan jaringan atau kuota habis, controller AI tidak akan memicu galat rusak ke pengguna, melainkan secara otomatis mengaktifkan *Internal Pattern Matching Engine* berbasis regex kata kunci Bahasa Indonesia untuk menjawab pertanyaan umum kependudukan, status persuratan, dan nomor kontak darurat secara lokal.

---

## BAB VI: ANTARMUKA SISTEM EKSTERNAL

### 6.1 Antarmuka Pengguna (UI/UX)
- Antarmuka web dibangun menggunakan arsitektur Blade Components dipadukan dengan **Tailwind CSS v4** berestetika modern (*clean slate palette*, radius sudut halus, dan efek elevasi visual).
- Widget SIWIL AI disajikan dalam bentuk *Floating Chatbot Button* di sudut kanan bawah antarmuka pengguna yang dapat dibuka dan ditutup secara fleksibel tanpa memuat ulang halaman (*Single Page App experience*).

### 6.2 Antarmuka Perangkat Lunak (APIs & Integrasi Eksternal)
1. **Google Gemini Generative Language API:** Protokol komunikasi HTTPS RESTful JSON untuk pengiriman payload percakapan dan penerimaan schema deklarasi *function calling tools*.
2. **WhatsApp Bot Microservice Gateway:** Berkomunikasi melalui HTTP port 3000 (`/api/whatsapp/*`) untuk pertukaran pesan webhook, tombol interaktif, pengiriman token OTP, dan pengiriman berkas dokumen PDF.
3. **Telegram Bot API:** Berkomunikasi melalui endpoint `https://api.telegram.org/bot<TOKEN>` untuk pengelolaan pesan bot, validasi token tautan akun (*deep linking*), dan pengiriman notifikasi instan.
4. **DomPDF Engine:** Modul kompilasi dokumen HTML/CSS menjadi berkas biner PDF berstandar cetak A4 dengan sematan kode verifikasi QR Code 2 dimensi.

### 6.3 Antarmuka Komunikasi Jaringan
- Protokol komunikasi klien-ke-server menggunakan **HTTPS** terenkripsi (TLS 1.3).
- Notifikasi surat elektronik dikirimkan melalui protokol **SMTP** terenkripsi TLS/SSL port 587/465.

---

## BAB VII: MATRIKS KETERTELUSURAN KEBUTUHAN (RTM)

Matriks Ketertelusuran Kebutuhan (*Requirements Traceability Matrix - RTM*) memetakan hubungan antara kebutuhan fungsional (FR), implementasi modul kode, skema basis data, dan berkas pengujian otomatis (*automated test*):

| ID Kebutuhan | Nama Kebutuhan Fungsional | File Controller / Service Utama | Tabel Basis Data Terkait | Berkas Uji Otomatis (PHPUnit) |
| :---: | :--- | :--- | :--- | :--- |
| **FR-01** | Transparansi Publik & APBDes | `PublicPortalController.php` | `budgets`, `developments`, `villages` | `DevelopmentHubTest.php` |
| **FR-02** | Registrasi Warga Validasi NIK/KK | `RegisterController.php`, `ResidentService.php` | `users`, `residents`, `families` | `AuthWorkflowTest.php` |
| **FR-03** | Reset Password WhatsApp OTP | `ForgotPasswordController.php`, `WhatsAppService.php` | `users`, Cache OTP | `AuthWorkflowTest.php` |
| **FR-04** | Master Kependudukan & Teritorial | `ResidentController.php`, `AreaController.php` | `residents`, `families`, `rts`, `rws` | `AreaValidationTest.php` |
| **FR-05** | Pengajuan & Persetujuan Surat 3-Tier | `DocumentRequestController.php`, `DocumentRequestService.php` | `document_requests`, `document_request_histories` | `TelegramDocumentNotificationTest.php` |
| **FR-06** | Pengaduan Warga Foto & GPS | `ComplaintController.php` | `complaints`, `villages`, `rws`, `rts` | `TelegramComplaintNotificationTest.php` |
| **FR-07** | Peringatan Darurat SOS Cepat | `EmergencyController.php` | `emergencies`, `users` | Feature Testing Suite |
| **FR-08** | Musyawarah & Polling Satu Suara | `PollController.php` | `polls`, `poll_options`, `poll_votes` | `PollWorkflowTest.php` |
| **FR-09** | Pengumuman Desa Omnichannel | `AnnouncementController.php` | `announcements`, `app_notifications` | `NotificationWorkflowTest.php` |
| **FR-10** | Asisten AI Gemini 12 Tools | `AiChatController.php`, `AiToolService.php` | `ai_knowledge`, `system_configurations` | `AiChatTest.php`, `AiToolServiceTest.php` |
| **FR-11** | Pusat Konfigurasi & Diagnostik | `SystemConfigurationController.php` | `system_configurations` | `SystemConfigurationTest.php` |
| **FR-12** | Integrasi Gateway WhatsApp & TG | `WhatsAppController.php`, `TelegramController.php` | `users`, Token Deep Link Cache | `TelegramAuthTest.php` |
| **FR-13** | Audit Trail Log Sistem | `AuditLogController.php` | `audit_logs` | Feature Testing Suite |

---

*Dokumen PRD/SRS Mandiri ini disusun secara lengkap dan mendalam oleh Muhammad Al Farel (NIM: 41257124) guna memenuhi seluruh persyaratan evaluasi Tahap 1 mata kuliah Rekayasa Perangkat Lunak (RPL).*
