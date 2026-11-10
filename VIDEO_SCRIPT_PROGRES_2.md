# NASKAH PRESENTASI VIDEO PROGRES 2 (PERANCANGAN UML, ARSITEKTUR & UI/UX)
## SIWIL — Sistem Informasi Wilayah & Pemerintahan Terpadu
**Mata Kuliah:** Rekayasa Perangkat Lunak (RPL)  
**Dosen Pengampu:** Yudhistira  
**Nama Mahasiswa:** Muhammad Al Farel  
**NIM:** 41257124  
**Kelas:** TI 1B 2025  
**Target Durasi:** 5 – 7 Menit (Optimal: ~6 Menit)

---

### PANDUAN PEREKAMAN BAGI MAHASISWA
- **Gaya Penyampaian:** Runtut, teknis namun mudah dipahami, artikulasi tegas, tempo terkontrol.
- **Tata Letak Layar:** 
  - Kamera wajah (webcam) di pojok bawah layar.
  - Tampilan utama merekam berkas `diagram.drawio` (layar draw.io atau PDF), dokumen `SDD.md`, dan visualisasi mockup antarmuka.
- **Tanda Visual:** Ikuti penanda `[TAMPILKAN PADA LAYAR]` untuk berpindah antar-halaman diagram di draw.io atau bab di dokumen SDD.

---

## TIMELINE & NASKAH KATA-DEMI-KATA (VERBATIM)

### BAGIAN 1: PEMBUKAAN & REKAP TRANSISI PRD KE SDD (00:00 - 00:45)
**Panduan Durasi:** 45 Detik  
**Tampilan Layar:** `[TAMPILKAN PADA LAYAR: Slide Judul berisi Nama: Muhammad Al Farel, NIM: 41257124, Kelas: TI 1B 2025, Dosen: Yudhistira, Judul Presentasi: Progres 2 - Perancangan UML, Arsitektur Sistem (SDD) & UI/UX SIWIL]`

> *"Assalamu’alaikum Warahmatullahi Wabarakatuh,*  
> *Selamat pagi/siang kepada Bapak Yudhistira selaku dosen pengampu mata kuliah Rekayasa Perangkat Lunak, serta rekan-rekan semua.*  
> 
> *Saya Muhammad Al Farel, NIM 41257124, dari kelas TI 1B angkatan 2025.*  
> 
> *Pada video progres kedua ini, saya akan mempresentasikan hasil perancangan sistem perangkat lunak yang telah saya susun dalam dokumen **Software Design Description (SDD)** berstandar **IEEE Std 1016** untuk aplikasi **SIWIL — Sistem Informasi Wilayah Terpadu**.*  
> 
> *Melanjutkan dari dokumen PRD pada tahap sebelumnya, di tahap ini saya telah memodelkan struktur dan perilaku sistem secara utuh melalui **4 Diagram UML Utama**, perancangan **Entity Relationship Diagram (ERD)** basis data 20 entitas, serta rancangan arsitektur antarmuka **UI/UX dan prototipe sistem**."*

---

### BAGIAN 2: DIAGRAM UML 1 — USE CASE DIAGRAM & SKENARIO FORMAL (00:45 - 01:50)
**Panduan Durasi:** 1 Menit 5 Detik  
**Tampilan Layar:** `[TAMPILKAN PADA LAYAR: diagram.drawio halaman 1 — Halaman Use Case Diagram Lengkap dengan Garis Warna Unik Antar-Aktor]`

> *"Mari kita mulai dari diagram UML pertama, yaitu **Use Case Diagram**.*  
> 
> *Bisa kita lihat pada layar, sistem SIWIL memodelkan interaksi antara 5 aktor terdaftar dan 1 pengunjung publik. Agar keterhubungan use case terlihat sangat jelas dan rapi, setiap aktor dibedakan menggunakan jalur koridor dan warna garis penghubung yang unik:*  
> *- **Aktor Warga (Warna Biru):** Memiliki 9 use case mandiri, mulai dari registrasi validasi silang NIK/KK, pengajuan surat online, pemantauan aduan GPS, tombol darurat SOS, hingga berkonsultasi dengan chatbot AI.*  
> *- **Aktor Ketua RT (Warna Hijau):** Berperan sebagai verifikator surat tingkat pertama, pengelola kependudukan lokal, dan penanggap pertama sinyal SOS.*  
> *- **Aktor Ketua RW (Warna Teal):** Bertindak sebagai verifikator surat tingkat kedua dan koordinator musyawarah lintas RT.*  
> *- **Aktor Admin Desa (Warna Oranye Bata):** Memiliki wewenang penerbitan surat resmi berkop desa dengan Tanda Tangan Elektronik ber-QR Code, serta pengelolaan anggaran APBDes dan proyek fisik.*  
> *- **Super Administrator (Warna Ungu):** Mengelola konfigurasi terpusat multi-desa, integrasi API AI, dan log audit sistem.*  
> *- Serta **Publik / Tamu (Warna Abu-abu):** Yang dapat melihat portal transparansi APBDes dan statistik desa secara terbuka tanpa login.*  
> 
> *Seluruh use case krusial ini telah dilengkapi dengan spesifikasi skenario formal di dalam dokumen SDD, mencakup prakondisi, alur normal, hingga alur penolakan dan pengecualian secara terstruktur."*

---

### BAGIAN 3: DIAGRAM UML 2 — ACTIVITY DIAGRAM ALUR BISNIS & AI (01:50 - 02:50)
**Panduan Durasi:** 1 Menit  
**Tampilan Layar:** `[TAMPILKAN PADA LAYAR: diagram.drawio halaman 2 — Activity Diagram Pelayanan Persuratan & TTE, lalu beralih ke SDD Bab 2.2.2 Activity AI]`

> *"Diagram UML kedua adalah **Activity Diagram**, yang memvisualisasikan alur kontrol dinamis dari dua proses vital di SIWIL:*  
> 
> *Pertama, **Alur Pelayanan Persuratan 3-Tier Berbasis TTE**:*  
> *Aktivitas dimulai ketika warga mengisi formulir dan mengunggah lampiran di web. Permohonan masuk ke antrean Ketua RT. Jika disetujui RT, status naik menjadi `DIAJUKAN_RW`. Begitu Ketua RW memverifikasi, status beralih ke `DIAJUKAN_DESA`.*  
> *Pada titik akhir di Admin Desa, sistem mengesahkan surat dengan menyematkan stempel digital, TTE Kepala Desa, dan secara otomatis membangkitkan **QR Code verifikasi keabsahan** berbasis hash kriptografi.*  
> *Mesin DomPDF kemudian mengompilasi surat menjadi file PDF resmi yang dapat diunduh oleh warga secara instan.*  
> 
> *Kedua, **Alur Pemrosesan AI dengan Guardrail UU PDP**:*  
> *Ketika pengguna bertanya pada chatbot, sistem menginjeksi peran dan hak akses pengguna ke dalam system instruction. Jika AI membutuhkan data riil, AI memanggil salah satu dari 12 function calling tools.*  
> *Backend secara ketat memeriksa otorisasi: apabila pengguna adalah warga biasa yang mencoba meminta NIK atau kontak warga lain, sistem memotong akses data dan menginstruksikan AI untuk menolak demi kepatuhan terhadap Undang-Undang Pelindungan Data Pribadi."*

---

### BAGIAN 4: DIAGRAM UML 3 — CLASS DIAGRAM & PENERAPAN PRINSIP SOLID (02:50 - 04:00)
**Panduan Durasi:** 1 Menit 10 Detik  
**Tampilan Layar:** `[TAMPILKAN PADA LAYAR: SDD Bab 2.3 — Mermaid Class Diagram dan Rincian 5 Prinsip SOLID]`

> *"Diagram UML ketiga adalah **Class Diagram Berorientasi Objek** yang dirancang dengan mematuhi **5 Prinsip SOLID**:*  
> 
> *1. **Single Responsibility Principle (SRP):** Logika bisnis tidak dicampuradukkan di Controller. Class `DocumentRequestController` hanya menangani penerimaan HTTP request, sedangkan kalkulasi bisnis persuratan didelegasikan ke `DocumentRequestService`, dan pengiriman pesan diisolasi pada `WhatsAppService` dan `TelegramService`.*  
> *2. **Open/Closed Principle (OCP):** Modul AI dirancang berbasis antarmuka provider, sehingga penambahan model AI baru seperti DeepSeek atau OpenAI dapat dilakukan tanpa mengubah logika pemanggilan tool lokal di `AiToolService`.*  
> *3. **Liskov Substitution Principle (LSP):** Seluruh model kependudukan (`Resident`, `Family`, `User`) merupakan turunan konsisten dari Eloquent Model dengan trait `HasUuids`, memastikan perilaku objek seragam saat kueri data.*  
> *4. **Interface Segregation Principle (ISP):** Kontrak antarmuka bot dipisahkan secara spesifik, sehingga modul pengirim pesan WhatsApp tidak dipaksa mengimplementasikan method yang hanya dimiliki oleh bot Telegram.*  
> *5. **Dependency Inversion Principle (DIP):** Modul tingkat tinggi bergantung pada abstraksi service yang diinjeksi melalui Laravel Service Container menggunakan PHP constructor property promotion.*  
> 
> *Struktur class ini memastikan kode program bersih, modular, mudah diuji secara otomatis, dan minim resiko terjadinya regresi saat sistem dikembangkan lebih lanjut."*

---

### BAGIAN 5: DIAGRAM UML 4 — SEQUENCE DIAGRAM INTERAKSI PESAN (04:00 - 04:50)
**Panduan Durasi:** 50 Detik  
**Tampilan Layar:** `[TAMPILKAN PADA LAYAR: SDD Bab 2.4 — Sequence Diagram Persuratan dan Sequence Diagram AI Function Calling]`

> *"Diagram UML keempat adalah **Sequence Diagram**, yang menggambarkan urutan waktu pertukaran pesan antar-komponen secara kronologis.*  
> 
> *Pada layar, dapat kita perhatikan alur interaksi **Chatbot AI dengan 12 Backend Tools**:*  
> *1. Pengguna mengirimkan pesan melalui antarmuka widget chat.*  
> *2. Controller menyuntikkan identitas dan batas akses ke Google Gemini API.*  
> *3. Model Gemini merespons dengan deklarasi pemanggilan fungsi, misalnya `fetch_document_requests`.*  
> *4. Controller mengeksekusi function tool pada `AiToolService`, yang melakukan kueri ter-scope ke basis data.*  
> *5. Data JSON yang bersih dan aman dikembalikan ke model Gemini.*  
> *6. Gemini menyintesis data tersebut menjadi bahasa manusia yang ramah, dan menampilkannya di layar pengguna dalam waktu kurang dari 3 detik."*

---

### BAGIAN 6: BASIS DATA (ERD) & PERANCANGAN UI/UX (04:50 - 05:45)
**Panduan Durasi:** 55 Detik  
**Tampilan Layar:** `[TAMPILKAN PADA LAYAR: SDD Bab 3 ERD Diagram 20 Entitas, lalu beralih ke Bab 4 Desain Antarmuka UI/UX]`

> *"Selanjutnya pada Bab Perancangan Basis Data, SIWIL memodelkan **Entity Relationship Diagram (ERD)** yang terdiri dari **20 entitas/tabel**.*  
> 
> *Seluruh tabel menggunakan **UUID v4** sebagai primary key untuk mencegah eksploitasi enumerasi URL dan mendukung skalabilitas multi-desa.*  
> *Relasi kewilayahan disusun secara terstruktur dari `villages` menaungi `rws`, `rws` menaungi `rts`, `rts` menaungi `families` atau Kartu Keluarga, dan setiap KK menaungi data kependudukan `residents`.*  
> 
> *Di sisi **Perancangan Antarmuka UI/UX**, SIWIL mengusung gaya modern, bersih, dan aksesibel:*  
> *- Menggunakan palet warna Indigo dan Emerald yang elegan, serta warna Crimson Red untuk tombol panik darurat SOS.*  
> *- Tipografi menggunakan Google Font **Outfit** yang sangat jelas dibaca di layar ponsel maupun desktop.*  
> *- Dan tata letak berbasis **mobile-first**, dilengkapi navigasi bilah bawah pada smartphone dan widget asisten AI mengambang yang responsif."*

---

### BAGIAN 7: PENUTUP & KESIAPAN TAHAP 3 (05:45 - 06:15)
**Panduan Durasi:** 30 Detik  
**Tampilan Layar:** `[TAMPILKAN PADA LAYAR: Slide Penutup berisi Rangkuman Pencapaian Tahap 2, Kesiapan Tahap 3 (CI/CD & Testing), serta Identitas Mahasiswa]`

> *"Bapak dosen dan rekan-rekan sekalian, seluruh rancangan UML, ERD, dan spesifikasi arsitektur UI/UX ini telah dituangkan secara lengkap dan mendalam di dalam berkas **SDD.md** dan diagram grafis **diagram.drawio**.*  
> 
> *Dengan tuntasnya perancangan tahap 2 ini, arsitektur sistem SIWIL telah siap secara teknis untuk diimplementasikan ke dalam **Tahap 3**, yaitu konstruksi kode bersih, penulisan automated unit tests, serta konfigurasi pipeline otomatisasi **CI/CD pada GitHub Actions**.*  
> 
> *Sekian presentasi progres tahap 2 dari saya. Terima kasih banyak atas perhatian Bapak Yudhistira dan rekan-rekan semua.*  
> *Wassalamu’alaikum Warahmatullahi Wabarakatuh."*

---
*Naskah ini dirancang tepat untuk durasi 5–7 menit, menyajikan penjelasan UML, arsitektur SOLID, ERD, dan antarmuka UI/UX secara profesional, sistematis, dan meyakinkan.*

