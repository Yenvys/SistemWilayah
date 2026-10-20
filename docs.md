# SIWIL - Technical Documentation

Dokumen ini berisi penjelasan teknis mendalam mengenai arsitektur, struktur database, logika *routing*, dan manajemen *role* pada aplikasi SIWIL (Sistem Informasi Wilayah & Pemerintahan).

---

## 1. Arsitektur Role & Permissions

Sistem SIWIL diatur oleh *Role-Based Access Control* (RBAC) dengan *package* `spatie/laravel-permission`. Terdapat beberapa level hak akses hierarkis (dari atas ke bawah):

1. **SUPER_ADMIN**
   - Menguasai seluruh sistem.
   - Dapat mengelola data `Village` (Desa), Area (RT/RW), Manajemen *User*, *Audit Logs*, hingga `AI Knowledge`.
2. **ADMIN_DESA**
   - Mengelola operasional satu desa/kelurahan.
   - Dapat mengelola Area, *Spatial Analytics*, Pembangunan (`Development`), Keuangan (`Budget`), serta menyetujui surat di tingkat desa.
3. **RW**
   - Memverifikasi surat dan laporan dari RT di wilayah RW-nya.
   - Melihat laporan warga pada level RW.
4. **RT**
   - Ujung tombak pendataan.
   - Mengelola data `Resident` (Warga) di lingkungan RT.
   - Verifikator pertama untuk persuratan dan pengaduan.
5. **WARGA** (Implicit Role)
   - Warga yang sudah mendaftar dan *profile complete*.
   - Mengakses layanan pelaporan darurat, pengaduan, pengajuan surat, poling, dan melihat pengumuman.

---

## 2. Struktur Modul & Model (Database)

Aplikasi memiliki beberapa model utama yang merepresentasikan fungsionalitas sistem:

### Modul Wilayah & Data Master
- **`Village`**, **`Rw`**, **`Rt`**: Menggambarkan hierarki wilayah administratif. Warga atau *User* akan di- *assign* ke entitas-entitas ini.
- **`AreaController`**: Digunakan oleh admin untuk menambah atau menghapus RT/RW.

### Modul Kependudukan
- **`User`**: Akun pengguna sistem (untuk autentikasi).
- **`Resident`**: Data demografis detail dari warga. Berelasi 1-to-1 dengan `User`.
- **`Family`**: Data Kartu Keluarga (KK). Warga/`Resident` tergabung ke dalam sebuah `Family`.

### Modul Pelayanan Terpadu
- **`DocumentRequest`**: Permintaan surat oleh warga (seperti Surat Keterangan Tidak Mampu, Pengantar Nikah, dll). 
  - Status berjalan hierarkis: *Pending* -> *Verified by RT* -> *Verified by RW* -> *Approved by Desa*.
- **`Complaint`**: Modul pelaporan infrastruktur atau sosial oleh warga.
- **`Emergency`**: Pelaporan darurat *real-time* (kecelakaan, kebakaran, dll).
- **`Poll`**: Fasilitas *voting* musyawarah digital warga.

### Modul Transparansi
- **`Development`**: Modul laporan pembangunan wilayah.
- **`Budget`**: Transparansi dana/APBDes/Kas.
- **`Announcement`**: Papan pengumuman elektronik yang disiarkan oleh pengurus.

### Modul Cerdas & Log
- **`AiKnowledge`**: Basis pengetahuan untuk sistem Chatbot AI.
- **`AuditLog`**: Mencatat (*tracking*) setiap aktivitas kritikal (*CRUD*) oleh peran *Admin* dan *Verifikator*.
- **Spatial Analytics**: Pemetaan wilayah rawan bencana/kriminalitas berbasis lokasi/geotag.

---

## 3. Struktur Routing & Middleware

Aplikasi menggunakan `routes/web.php` yang dipisahkan melalui beberapa *Middleware* keamanan:

- **Public Routes**: `/` dan portal informasi (`/portal/apbdes`, `/portal/statistik`) tidak membutuhkan autentikasi.
- **Protected Routes (`auth`, `profile.complete`)**: Mayoritas sistem berada di bawah awalan `/dashboard`. Warga baru harus melengkapi profil sebelum bisa masuk.
- **Role-based Routes (`role:X,Y`)**: Akses ke *controller* dibatasi menggunakan _middleware spatie_. Contoh:
  - Hanya `SUPER_ADMIN`, `ADMIN_DESA`, `RW`, `RT` yang dapat memverifikasi *User* (`UserController@verify`).
  - Hanya `SUPER_ADMIN` dan `ADMIN_DESA` yang dapat mengelola Pengumuman Publik (`AnnouncementController@manage`).

---

## 4. Alur Kerja (Workflow) Utama

### Alur Pengajuan Surat
1. **Warga** login dan membuat pengajuan di `/dashboard/documents/create`.
2. **Ketua RT** melihat pengajuan dan melakukan verifikasi tingkat 1.
3. **Ketua RW** (Opsional/Sesuai tipe surat) melakukan verifikasi tingkat 2.
4. **Admin Desa** menyetujui, mencetak, dan membubuhkan Tanda Tangan Elektronik (*TTE*).
5. **Warga** mengunduh surat berformat PDF.

### Alur Pengaduan & Darurat
1. **Warga** mengambil foto kejadian dan mengirim lewat `/dashboard/complaints` atau tombol *Emergency*.
2. Sistem meneruskan notifikasi kepada **RT/RW** setempat.
3. Untuk kejadian darurat, **Admin/Petugas** bisa menekan aksi *Resolve* via `/dashboard/emergencies/{id}/resolve`.

---

## 5. Integrasi Frontend (Tailwind + Vite)

Frontend dibangun dengan antarmuka dinamis dan *responsive*. Proses kompilasi *asset* (*CSS/JS*) diatur sepenuhnya oleh **Vite 8**:
- `npm run dev` untuk pengembangan (*Hot Module Replacement*).
- `npm run build` untuk *deployment* produksi.

Pewarnaan, grid, dan komponen UI mengikuti standar *utility-first* **Tailwind CSS v4** yang diintegrasikan langsung pada *Blade Template Engine* Laravel.

---
*Dokumentasi ini dibuat untuk membantu proses onboarding developer dan panduan arsitektur.*
