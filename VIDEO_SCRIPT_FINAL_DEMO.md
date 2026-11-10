# NASKAH PRESENTASI VIDEO FINAL & LIVE DEMO SISTEM LENGKAP
## SIWIL — Sistem Informasi Wilayah & Pemerintahan Terpadu
**Mata Kuliah:** Rekayasa Perangkat Lunak (RPL)  
**Dosen Pengampu:** Yudhistira  
**Nama Mahasiswa:** Muhammad Al Farel  
**NIM:** 41257124  
**Kelas:** TI 1B 2025  
**Target Durasi:** 8 – 12 Menit (Optimal: ~10 Menit 30 Detik)

---

### PANDUAN PEREKAMAN LIVE DEMO BAGI MAHASISWA
- **Persiapan Sebelum Rekaman:**
  1. Jalankan web server Laravel lokal (`php artisan serve` di `http://127.0.0.1:8000`).
  2. Buka peramban (Chrome/Edge) dengan beberapa tab siap pakai:
     - Tab 1: Portal Publik (`http://127.0.0.1:8000/`)
     - Tab 2: Dashboard Warga (login sebagai Warga: username NIK `3201010101010001`, password `password`)
     - Tab 3: Dashboard Ketua RT (login akun RT: `rt01@siwil.desa.id`, password `password`)
     - Tab 4: Dashboard Admin Desa (login akun Desa: `admin@siwil.desa.id`, password `password`)
     - Tab 5: Terminal / VS Code menampilkan berkas `.github/workflows/ci.yml` dan terminal hasil test `php artisan test`.
  3. Posisikan kamera wajah (webcam) di pojok layar agar wajah Anda tetap terlihat jelas selama presentasi.
- **Tanda Panduan:**
  - `[AKSI LAYAR]`: Instruksi klik mouse, perpindahan tab, atau pengetikan di layar peramban.
  - Tanda kutip `> "..."`: Kata-demi-kata (verbatim) yang harus Anda ucapkan secara jelas dan percaya diri.

---

## TIMELINE & NASKAH KATA-DEMI-KATA (VERBATIM)

### SEGMEN 1: PEMBUKAAN & OVERVIEW PROYEK (00:00 - 01:00)
**Panduan Durasi:** 1 Menit  
**Aksi Layar:** `[AKSI LAYAR: Buka Halaman Utama Portal Publik SIWIL di localhost:8000. Sorot header dan logo SIWIL]`

> *"Assalamu’alaikum Warahmatullahi Wabarakatuh,*  
> *Selamat pagi/siang kepada yang terhormat Bapak Yudhistira selaku dosen pengampu mata kuliah Rekayasa Perangkat Lunak, serta rekan-rekan mahasiswa yang saya banggakan.*  
> 
> *Perkenalkan, nama saya Muhammad Al Farel, NIM 41257124, mahasiswa kelas TI 1B angkatan 2025 program studi Teknik Informatika.*  
> 
> *Pada video final ini, saya akan menyajikan **Laporan Akhir dan Live Demonstration Utuh** dari aplikasi web yang saya bangun secara mandiri, yaitu **SIWIL — Sistem Informasi Wilayah dan Pemerintahan Terpadu**.*  
> 
> *Proyek ini merupakan kulminasi dari seluruh siklus Rekayasa Perangkat Lunak yang telah saya lalui secara terstruktur:*  
> *- Mulai dari **Tahap 1:** Penyusunan dokumen PRD/SRS standar IEEE 830 dan ISO 29148;*  
> *- **Tahap 2:** Perancangan arsitektur SDD standar IEEE 1016 dengan 4 Diagram UML, ERD 20 entitas basis data, dan UI/UX mobile-first;*  
> *- Hingga **Tahap 3 & 4:** Konstruksi kode bersih, integrasi kecerdasan buatan Google Gemini dengan 12 function calling tools, 105 automated unit tests berstatus 100% lulus, serta implementasi CI/CD pipeline pada GitHub Actions.*  
> 
> *Tanpa berlama-lama, mari kita mulai demonstrasi langsung dari fitur-fitur utama SIWIL."*

---

### SEGMEN 2: LIVE DEMO 1 — PORTAL TRANSPARANSI PUBLIK (01:00 - 02:30)
**Panduan Durasi:** 1 Menit 30 Detik  
**Aksi Layar:**  
1. `[AKSI LAYAR: Scroll perlahan ke bawah di beranda /portal: tunjukkan statistik ringkas penduduk, sambutan kades, dan kartu pengumuman]`  
2. `[AKSI LAYAR: Klik menu navbar "Transparansi APBDes" (/portal/apbdes)]`  
3. `[AKSI LAYAR: Tunjukkan breakdown 3 pilar: Pendapatan, Belanja, dan Pembiayaan, serta persentase serapan anggaran]`  
4. `[AKSI LAYAR: Klik menu navbar "Pembangunan Desa" (/portal/pembangunan), tunjukkan kartu proyek dengan foto dan progress bar]`

> *"Bapak dosen dan rekan-rekan, kita mulai dari gerbang depan sistem, yaitu **Portal Transparansi Publik**.*  
> *Sesuai dengan prinsip *Good Governance* dan keterbukaan informasi publik, halaman ini dapat diakses secara bebas oleh warga maupun masyarakat luas tanpa perlu login.*  
> 
> *Pada halaman beranda, masyarakat dapat langsung melihat profil desa, kontak darurat, serta statistik demografi kependudukan agregat yang selalu mutakhir.*  
> 
> *Selanjutnya, saya beralih ke menu **Transparansi APBDes**.*  
> *Di sini, sistem menampilkan struktur anggaran desa per tahun anggaran yang terbagi secara akuntabel ke dalam 3 klasifikasi fiskal: **Pendapatan Desa**, **Belanja Desa**, dan **Pembiayaan**.*  
> *Sistem secara otomatis menghitung perbandingan antara Pagu Anggaran yang dialokasikan dengan Realisasi Dana riil, lengkap dengan persentase serapan anggarannya.*  
> 
> *Lalu pada menu **Monitoring Pembangunan**, warga dapat memantau setiap proyek fisik yang sedang berjalan atau telah rampung.*  
> *Setiap proyek dilengkapi dengan dokumentasi foto lapangan, sumber dana (seperti Dana Desa atau PADes), nilai pagu, serta progress bar persentase pengerjaan fisik.*  
> *Dengan portal ini, potensi kecurigaan anggaran dapat dicegah dan kepercayaan masyarakat terhadap pemerintah desa meningkat drastis."*

---

### SEGMEN 3: LIVE DEMO 2 — AUTENTIKASI, VALIDASI NIK/KK & OTP WHATSAPP (02:30 - 04:00)
**Panduan Durasi:** 1 Menit 30 Detik  
**Aksi Layar:**  
1. `[AKSI LAYAR: Klik tombol "Masuk", lalu klik tautan "Daftar Akun Baru" (/register)]`  
2. `[AKSI LAYAR: Tunjukkan kolom input NIK 16 digit dan Nomor KK 16 digit]`  
3. `[AKSI LAYAR: Buka halaman Lupa Kata Sandi (/forgot-password), ketikkan NIK/nomor WA, klik "Kirim OTP"]`  
4. `[AKSI LAYAR: Tunjukkan halaman verifikasi OTP 6 digit dengan throttle timer 60 detik]`

> *"Sekarang kita beralih ke aspek keamanan identitas dan manajemen akses pengguna (*IAM*).*  
> 
> *SIWIL menerapkan prinsip **Zero Fake Account Registration**.*  
> *Saat warga mendaftar mandiri di halaman register, sistem mewajibkan penginputan **NIK 16 digit** dan **Nomor Kartu Keluarga 16 digit**.*  
> *Backend sistem secara deterministik memvalidasi silang ke basis data kependudukan resmi desa. Jika NIK atau Nomor KK tidak ditemukan, pendaftaran ditolak seketika. Hal ini menjamin bahwa seluruh pengguna terdaftar adalah warga riil yang berhak.*  
> 
> *Setelah terdaftar, akun masuk ke status `pending` dan harus diverifikasi terlebih dahulu oleh Ketua RT atau Admin Desa sebelum dapat login.*  
> 
> *Untuk kemudahan login, sistem mendukung **Multi-Identifier Login**, di mana warga dapat masuk menggunakan NIK, Alamat Email, maupun Nomor WhatsApp terdaftar.*  
> 
> *Dan jika warga lupa kata sandi, SIWIL dilengkapi dengan **Password Reset Engine berbasis WhatsApp OTP**.*  
> *Sistem men-generate kode OTP 6 digit acak yang dikirimkan secara instan ke nomor WhatsApp warga melalui microservice WhatsApp Gateway, lengkap dengan masa berlaku 5 menit dan proteksi anti-spam timer 60 detik."*

---

### SEGMEN 4: LIVE DEMO 3 — ASISTEN CERDAS AI & GUARDRAIL UU PDP (04:00 - 06:00)
**Panduan Durasi:** 2 Menit  
**Aksi Layar:**  
1. `[AKSI LAYAR: Login sebagai Warga, masuk ke /dashboard]`  
2. `[AKSI LAYAR: Klik tombol mengambang AI Chatbot di pojok kanan bawah, jendela chat terbuka]`  
3. `[AKSI LAYAR: Ketik: "Halo, apa saja syarat mengajukan Surat Keterangan Usaha?" -> Tunjukkan respons AI yang cepat dan relevan]`  
4. `[AKSI LAYAR: Ketik: "Bagaimana status pengajuan surat saya saat ini?" -> Tunjukkan AI memanggil tool fetch_document_requests dan menjawab status dokumen miliknya]`  
5. `[AKSI LAYAR: Ketik: "Berapa nomor HP dan NIK pak Budi tetangga saya?" -> Tunjukkan AI secara tegas menolak karena dilindungi UU PDP]`

> *"Sekarang saya akan memperagakan salah satu inovasi teknologi paling mutakhir di SIWIL, yaitu **SIWIL AI Assistant**.*  
> 
> *Di pojok kanan bawah layar dashboard, terdapat widget chatbot mengambang yang responsif dan siap membantu warga 24 jam sehari.*  
> *Asisten cerdas ini ditenagai oleh model **Google Gemini API** yang terhubung langsung dengan logika bisnis backend kami.*  
> 
> *Mari kita uji skenario pertama: Pertanyaan Prosedural.*  
> *Saya ketik: 'Halo, apa saja syarat mengajukan Surat Keterangan Usaha?'.*  
> *Bisa kita lihat, dalam hitungan detik, AI memanggil tool `fetch_ai_knowledge` dan memberikan panduan syarat berkas secara tepat dan ramah.*  
> 
> *Sekarang mari kita uji skenario kedua: Pengambilan Data Riil via Function Calling.*  
> *Saya ketik: 'Bagaimana status pengajuan surat saya saat ini?'.*  
> *Perhatikan bahwa AI tidak mengarang bebas atau berhalusinasi. AI secara otomatis mengeksekusi tool backend `fetch_document_requests`, mengambil data surat milik akun saya yang sedang login, lalu merangkum bahwa surat saya saat ini sedang dalam proses verifikasi.*  
> 
> *Dan yang paling krusial, mari kita uji **Guardrail Keamanan Privasi UU PDP**.*  
> *Saya ketik: 'Bisa minta nomor HP dan NIK pak Budi tetangga saya?'.*  
> *Perhatikan respons AI: AI secara tegas menolak permintaan ini dan menyatakan bahwa data kependudukan dilindungi oleh **Undang-Undang Nomor 27 Tahun 2022 tentang Pelindungan Data Pribadi**.*  
> *Penolakan ini ditegakkan di dua lapis: di tingkat prompt instruction dan di tingkat kueri backend Eloquent model yang membatasi hak akses data kependudukan khusus bagi perangkat RT/Desa saja."*

---

### SEGMEN 5: LIVE DEMO 4 — ALUR PERSURATAN 3-TIER & BARCODE TTE RESMI (06:00 - 08:30)
**Panduan Durasi:** 2 Menit 30 Detik  
**Aksi Layar:**  
1. `[AKSI LAYAR: Tab Warga -> Masuk ke /dashboard/documents/create, pilih Surat Domisili, ketik keperluan, upload bukti, klik Kirim]`  
2. `[AKSI LAYAR: Tunjukkan status surat menjadi "DIAJUKAN_RT" dengan nomor registrasi unik]`  
3. `[AKSI LAYAR: Beralih ke Tab Ketua RT -> Refresh menu surat, buka detail permohonan, klik tombol hijau "Setujui" -> Status naik ke "DIAJUKAN_RW"]`  
4. `[AKSI LAYAR: Beralih ke Tab Admin Desa -> Refresh menu surat, buka permohonan, klik tombol "Setujui & Terbitkan"]`  
5. `[AKSI LAYAR: Status berubah "DISETUJUI / SELESAI" -> Klik tombol "Unduh PDF" -> Dokumen PDF resmi terbuka di layar]`  
6. `[AKSI LAYAR: Sorot kop resmi desa, nomor surat, tanda tangan elektronik, stempel desa, dan QR Code verifikasi dokumen]`

> *"Sekarang kita masuk ke alur bisnis utama (*core workflow*) dari SIWIL, yaitu **Pelayanan Administrasi Persuratan Digital 3-Tier Berbasis TTE**.*  
> 
> *Langkah 1: Pengajuan oleh Warga.*  
> *Pada akun warga, saya masuk ke menu 'Pengajuan Surat', memilih jenis 'Surat Keterangan Domisili', mengisikan keperluan, mengunggah lampiran foto KTP, dan menekan tombol kirim.*  
> *Sistem men-generate nomor surat unik dan menetapkan status awal menjadi `DIAJUKAN_RT`.*  
> 
> *Langkah 2: Verifikasi Tingkat 1 oleh Ketua RT.*  
> *Saya berpindah ke tab peramban Ketua RT. Di dashboard Ketua RT, permohonan warga tadi langsung muncul di antrean verifikasi.*  
> *Ketua RT meninjau kelengkapan berkas, lalu menekan tombol hijau 'Setujui'. Seketika, status tereskalasi menjadi `DIAJUKAN_RW`.*  
> *(Untuk efisiensi demo, peran persetujuan tingkat RW juga dapat diteruskan secara hierarkis menuju tingkat Desa).*  
> 
> *Langkah 3: Pengesahan Akhir oleh Admin Desa.*  
> *Sekarang saya beralih ke tab Admin Desa. Admin Desa membuka permohonan tersebut, memverifikasi keabsahan final, lalu menekan 'Setujui & Terbitkan'.*  
> 
> *Langkah 4: Penerbitan Dokumen Resmi & Tanda Tangan Elektronik.*  
> *Saat status berubah menjadi `SELESAI`, sistem secara cerdas menjalankan DomPDF renderer.*  
> *Mari kita klik 'Unduh PDF'.*  
> *Bisa kita lihat di layar, dokumen PDF resmi berkop kelurahan telah ter-generate sempurna!*  
> *Di bagian bawah surat, tertera stempel resmi desa, tanda tangan digital, serta **QR Code Verifikasi Keabsahan Surat**.*  
> *QR Code ini dapat dipindai oleh kamera smartphone atau pihak luar seperti perbankan dan kepolisian untuk membuktikan bahwa dokumen ini asli dan terdaftar di server resmi SIWIL. Tidak ada lagi kebutuhan cap basah manual!"*

---

### SEGMEN 6: LIVE DEMO 5 — PENGADUAN GEOTAGGING GPS & TOMBOL DARURAT SOS (08:30 - 10:00)
**Panduan Durasi:** 1 Menit 30 Detik  
**Aksi Layar:**  
1. `[AKSI LAYAR: Kembali ke tab Warga -> Buka menu /dashboard/complaints/create]`  
2. `[AKSI LAYAR: Isi judul aduan "Lampu Jalan Mati di Gang Melati", upload foto, dan tunjukkan koordinat GPS latitude/longitude terisi otomatis]`  
3. `[AKSI LAYAR: Klik Kirim Aduan -> Tunjukkan status aduan PENDING]`  
4. `[AKSI LAYAR: Kembali ke dashboard Warga -> Sorot tombol merah "EMERGENCY SOS"]`  
5. `[AKSI LAYAR: Klik tombol SOS -> Modal darurat muncul -> Pilih "MEDIS" -> Klik Kirim]`  
6. `[AKSI LAYAR: Pindah ke tab RT/Desa -> Tunjukkan banner merah kedip darurat aktif dengan koordinat GPS pelapor]`

> *"Fitur berikutnya yang sangat vital bagi warga adalah **Pengaduan Masyarakat Berbasis Geotagging** dan **Tombol Darurat SOS**.*  
> 
> *Pada menu Pengaduan Warga, warga dapat melaporkan fasilitas umum yang rusak.*  
> *Cukup mengisikan judul keluhan, mengunggah foto bukti fisik lapangan, dan peramban secara otomatis menangkap titik koordinat GPS presisi perangkat.*  
> *Laporan ini otomatis masuk ke dashboard Ketua RT dan RW setempat sehingga tindak lanjut perbaikan dapat dilakukan dengan lokasi yang tepat tanpa tersesat.*  
> 
> *Selain itu, di situasi genting, warga memiliki akses ke **Tombol Darurat SOS**.*  
> *Jika terjadi kondisi kritis seperti serangan jantung, kebakaran, atau tindak kriminal, warga cukup menekan satu tombol merah ini.*  
> *Seketika, sistem menangkap koordinat GPS korban dan memicu banner alarm darurat aktif di layar seluruh pengurus RT, RW, dan kantor desa secara bersamaan, memungkinkan bantuan datang dalam hitungan menit."*

---

### SEGMEN 7: TESTING OTOMATIS (105 TESTS PASSED) & CI/CD GITHUB ACTIONS (10:00 - 11:15)
**Panduan Durasi:** 1 Menit 15 Detik  
**Aksi Layar:**  
1. `[AKSI LAYAR: Buka terminal / Visual Studio Code]`  
2. `[AKSI LAYAR: Tunjukkan hasil perintah: php artisan test --compact]`  
3. `[AKSI LAYAR: Sorot tulisan hijau: Tests: 105 passed, Assertions: 540]`  
4. `[AKSI LAYAR: Tunjukkan berkas .github/workflows/ci.yml]`

> *"Bapak dosen dan rekan-rekan, kualitas sebuah perangkat lunak diukur dari keandalan arsitektur dan kebersihan kodenya.*  
> 
> *Pada aspek Penjaminan Mutu (*Quality Assurance*), proyek SIWIL telah dilengkapi dengan rangkaian pengujian otomatis (*Automated Feature & Unit Testing*) yang sangat ketat menggunakan framework PHPUnit.*  
> 
> *Bisa kita lihat pada layar terminal:*  
> *Sebanyak **105 skrip pengujian (105 tests)** yang memuat **540 assertions** berjalan dengan hasil **100% PASSED** tanpa ada satu pun kegagalan atau galat.*  
> *Pengujian ini mencakup alur autentikasi, registrasi validasi NIK/KK, kalkulasi APBDes, alur surat berjenjang 3-tier, serta pengujian isolasi guardrail keamanan AI.*  
> 
> *Seluruh kode sumber juga telah diformat secara ketat mematuhi standar PSR-12 menggunakan **Laravel Pint**.*  
> 
> *Dan untuk menjamin integrasi berkelanjutan, saya telah mengonfigurasikan **CI/CD Pipeline pada GitHub Actions** melalui berkas `.github/workflows/ci.yml`.*  
> *Setiap kali ada commit baru yang di-push ke repositori GitHub, pipeline secara otomatis menjalankan instalasi dependensi, pemeriksaan gaya kode Pint, migrasi basis data SQLite, eksekusi test suite PHPUnit, dan build aset Vite di lingkungan Ubuntu Linux secara otomatis."*

---

### SEGMEN 8: REFLEKSI CAPAIAN & PENUTUP (11:15 - 12:00)
**Panduan Durasi:** 45 Detik  
**Aksi Layar:** `[AKSI LAYAR: Beralih kembali ke Slide Penutup memuat Identitas Mahasiswa: Muhammad Al Farel, NIM 41257124, Kelas TI 1B 2025, RPL]`

> *"Bapak dosen yang saya hormati dan rekan-rekan sekalian,*  
> *Dari demonstrasi utuh tadi, dapat kita simpulkan bahwa SIWIL berhasil membuktikan bahwa proses birokrasi pemerintahan desa dan rukun warga yang selama ini lambat, manual, dan rentan kebocoran data, dapat ditransformasikan secara menyeluruh menjadi sistem digital yang cepat, transparan, aman, dan cerdas.*  
> 
> *Seluruh target capaian dari Tahap 1 hingga Tahap 4 mata kuliah Rekayasa Perangkat Lunak telah berhasil saya selesaikan dengan tuntas dan dapat dipertanggungjawabkan secara akademik maupun teknis.*  
> 
> *Terima kasih yang sebesar-besarnya kepada Bapak Yudhistira atas bimbingan dan arahannya selama perkuliahan ini.*  
> *Semoga inovasi SIWIL ini dapat memberikan manfaat nyata bagi digitalisasi tata kelola masyarakat di Indonesia.*  
> 
> *Sekian presentasi dan live demo dari saya. Mohon maaf atas segala kekurangan.*  
> *Wabillahi taufiq wal hidayah, Wassalamu’alaikum Warahmatullahi Wabarakatuh."*

---
*Naskah ini dirancang secara presisi untuk rekaman 8–12 menit (optimal 10:30) dengan alur runtut, bahasa Indonesia akademis yang percaya diri, dan instruksi live demo klik-per-klik yang mudah dipraktikkan langsung di depan layar.*
