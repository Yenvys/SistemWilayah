# SIWIL (Sistem Informasi Wilayah & Pemerintahan)

SIWIL adalah sebuah aplikasi berbasis web yang dirancang untuk mendigitalisasi dan mempermudah tata kelola administrasi tingkat wilayah desa, RW, dan RT. Aplikasi ini menjadi jembatan informasi, administrasi, dan pelaporan antara warga dengan pemangku wilayah (Ketua RT, Ketua RW, dan Admin Desa/Kelurahan).

## 🌟 Fitur Utama (Modules)

1. **Autentikasi & Profil Pengguna**
   - Registrasi dan Login (Multi-Role: Warga, RT, RW, Admin).
   - Manajemen Profil dan Data Keluarga (KK).
2. **Administrasi & Persuratan**
   - Pengajuan berbagai macam surat (Surat Pengantar RT, Domisili, SKTM, Pindah/Datang, dsb).
   - *Tracking* status surat secara *real-time* (RT -> RW -> Kelurahan).
   - Pencetakan surat digital (TTE/Barcode).
3. **Pengaduan & Partisipasi Warga**
   - Pelaporan pengaduan infrastruktur atau masalah wilayah dengan foto & *geotagging*.
   - Pelaporan Keadaan Darurat (Emergency) untuk respons cepat.
   - Polling / Musyawarah Digital untuk pengambilan keputusan warga.
   - Usulan program pembangunan.
4. **Informasi & Transparansi Publik**
   - Papan Pengumuman interaktif.
   - Transparansi Anggaran dan Keuangan (APBDes / Kas RT).
   - Informasi dan Progres Pembangunan Wilayah.
5. **AI Knowledge & Support (Layanan Cerdas)**
   - Terintegrasi dengan modul AI (*AiKnowledge*) untuk membantu operasional sistem.

## 👥 Aktor / Role

Sistem ini didukung oleh *Role-Based Access Control* (RBAC) menggunakan *Spatie Laravel Permission*:
1. **Warga**: Pengguna utama (Main Actor) yang menikmati layanan administrasi dan pelaporan.
2. **Ketua RT**: Verifikator tingkat 1, pengelola data warga di tingkat RT, dan merespons aduan lokal.
3. **Ketua RW**: Verifikator tingkat 2, pemantau laporan lintas RT.
4. **Admin (Desa/Kelurahan)**: Penerbit surat resmi, pengelola data master (Wilayah, Pengguna, Sistem), dan *Superadmin*.

## 🛠️ Tech Stack

Project ini dibangun menggunakan teknologi modern:
- **Backend Framework**: [Laravel 11+ / 13.x](https://laravel.com) (PHP ^8.3)
- **Frontend Stack**: 
  - [Tailwind CSS v4](https://tailwindcss.com) 
  - [Vite 8](https://vitejs.dev/)
  - Blade Templates
- **Role Management**: [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission)
- **Database**: MySQL / SQLite
- **Testing**: Pest / PHPUnit

## 🚀 Cara Instalasi & Menjalankan Project

Ikuti langkah-langkah di bawah ini untuk menjalankan project di komputer lokal Anda:

1. **Clone repositori**
   ```bash
   git clone <url-repo-anda>
   cd siwil
   ```

2. **Install Dependensi Composer (PHP)**
   ```bash
   composer install
   ```

3. **Install Dependensi NPM (Node.js)**
   ```bash
   npm install
   ```

4. **Konfigurasi Environment**
   Gandakan file `.env.example` menjadi `.env` dan atur konfigurasi database Anda.
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Atur konfigurasi `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` di file `.env`.*

5. **Jalankan Migrasi & Seeder Database**
   ```bash
   php artisan migrate --seed
   ```

6. **Jalankan Aplikasi**
   Jalankan server PHP dan *Vite dev server* secara bersamaan. Disarankan membuka 2 terminal:
   
   **Terminal 1 (Backend):**
   ```bash
   php artisan serve
   ```
   
   **Terminal 2 (Frontend):**
   ```bash
   npm run dev
   ```

7. **Akses Aplikasi**
   Buka browser Anda dan akses `http://localhost:8000`.

## 📂 Struktur Penting (Berdasarkan Model)
- `App\Models\Resident`: Data Penduduk/Warga
- `App\Models\Family`: Data Kartu Keluarga
- `App\Models\Rt` & `App\Models\Rw` & `App\Models\Village`: Data Master Wilayah
- `App\Models\DocumentRequest`: Transaksi Pengajuan Surat Warga
- `App\Models\Complaint`: Laporan Pengaduan
- `App\Models\Emergency`: Laporan Darurat
- `App\Models\Development`: Proyek Pembangunan
- `App\Models\Budget`: Anggaran & Keuangan
- `App\Models\Poll`: Voting Warga

---
*Dokumentasi ini *auto-generated* berdasarkan struktur sistem.*
