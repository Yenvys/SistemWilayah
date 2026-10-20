# Analisis Konsistensi Kode Proyek (Siwil)

Berikut adalah hasil analisis menyeluruh terhadap konsistensi, *readability* (keterbacaan), modularitas, kohesi, dan ketergantungan antar komponen (coupling) pada kode proyek Siwil (fokus pada lapisan Controller seperti `ResidentController.php` dan `DocumentRequestController.php`).

## 1. Analisis Readability (Keterbacaan Kode)

Secara umum, kode menggunakan *naming convention* (penamaan) yang cukup baik dan sesuai standar Laravel (menggunakan bahasa Inggris untuk nama kelas dan metode, *camelCase* untuk variabel). Namun, ada beberapa hal yang mengurangi *readability*.

**Kelebihan (+):**
*   **Penamaan Variabel Jelas:** Variabel seperti `$docRequest`, `$user`, dan `$validated` sangat representatif terhadap isinya.
*   **Penggunaan Eloquent Relasi yang Baik:** Pemanggilan relasi seperti `Resident::with(['family.rt.rw.village'])` menunjukkan pemahaman ORM yang membuat kueri mudah dibaca dibandingkan SQL mentah.

**Kekurangan (-):**
*   **Blok Kondisional yang Panjang dan Berulang (Nested/Long If-Else):** Terlihat duplikasi logika pengecekan peran (Role) yang sangat panjang di setiap metode `index()`, `create()`, dan `store()`.
    *   *Bukti:* Pada `DocumentRequestController::index()` baris 23-48 dan `ResidentController::index()` baris 21-37, terdapat banyak `elseif ($user->role === 'RT')` untuk menentukan *scope* data. Ini membuat ukuran metode menjadi sangat panjang ("Fat Controller") sehingga sulit dibaca dalam satu pandangan.

## 2. Analisis Modularitas & Konsistensi Sistem

Modularitas merujuk pada sejauh mana aplikasi dibagi menjadi bagian-bagian yang independen. Saat ini, sistem **kurang konsisten** dalam menerapkan modularitas karena hampir seluruh logika diletakkan di dalam Controller.

**Kekurangan (-):**
*   **Tidak Adanya Lapisan Service (Service Pattern):** Controller tidak hanya menerima *request* HTTP, tetapi juga memproses logika bisnis.
    *   *Bukti:* Di `ResidentController::update()` (baris 166-198), proses untuk memperbarui data Resident sekaligus logika "Promote to User" (mengecek apakah user ada, jika ada di-update, jika tidak di-create, beserta *mapping* id wilayahnya) dilakukan langsung di dalam controller.
*   **Validasi Campur Aduk:** Meski menggunakan `$request->validate()`, validasinya ditulis langsung (inline) di controller alih-alih menggunakan *Form Request Validation* yang merupakan praktik standar modular di Laravel.

## 3. Analisis Kohesi (Cohesion)

Kohesi adalah tingkat keterikatan fungsionalitas di dalam satu kelas. Kelas yang baik harus memiliki "Kohesi Tinggi" (High Cohesion), artinya kelas tersebut hanya melakukan satu tugas yang spesifik.

**Evaluasi: Rendah (Low Cohesion)**
*   *Bukti:* Kelas `DocumentRequestController` mengelola terlalu banyak hal:
    1.  Mengecek Authorization/Role permissions.
    2.  Mengelola Query Builder (scoping berdasarkan wilayah RT/RW/Desa).
    3.  Mengunggah *file* (Attachment) di method `store()`.
    4.  Mencetak dokumen dan menentukan *view* spesifik di method `print()`.
*   Idealnya, Controller hanya bertanggung jawab mengarahkan HTTP request dan response. Tugas scoping query harusnya berada di **Model (Local/Global Scopes)** atau **Repository**, dan validasi akses (Authorization) seharusnya dikelola oleh **Laravel Policies**.

## 4. Analisis Ketergantungan (Coupling)

Coupling adalah tingkat ketergantungan antara satu komponen dengan komponen lain. Kode yang baik harus memiliki *Loose Coupling* (ketergantungan rendah).

**Evaluasi: Tinggi (Tight Coupling)**
*   **Ketergantungan Langsung pada Struktur Database Wilayah:**
    *   *Bukti:* Pada `DocumentRequestController`, untuk mendapatkan `rtId`, kode menggunakan *chaining* yang sangat panjang dan rawan error (Null Pointer Exception jika tidak hati-hati): `$user->rt_id ?? ($user->resident?->rt_id ?? ($user->resident?->family?->rt_id ?? null))`.
    *   Ketergantungan ini membuat sistem rapuh. Jika suatu saat struktur relasi antara `User`, `Resident`, dan `Family` berubah, semua controller yang memiliki logika *chaining* ini akan *error* dan harus direfaktor secara manual satu per satu.
*   **Hardcode Strings:**
    *   *Bukti:* Penggunaan *string literal* untuk status role dan surat seperti `'DIAJUKAN_RT'`, `'DISETUJUI'`, `'ADMIN_DESA'`, `'RW'`. Jika suatu saat ada perubahan nama role atau status, tidak ada satu titik (*single point of truth*) seperti `Enum` atau konstanta kelas yang bisa diubah, sehingga akan rawan *typo* dan *bug*.

---

### Kesimpulan & Rekomendasi Solusi

Sistem saat ini berfungsi (*working*), namun secara arsitektur **belum ideal** dari sisi modularitas, kohesi, dan coupling.

**Langkah Perbaikan yang Direkomendasikan:**
1.  **Ekstrak Logika Authorization:** Pindahkan semua logika `if ($user->role === ...)` ke dalam **Laravel Policies**.
2.  **Gunakan Query Scopes:** Buat local scope di model (misalnya `scopeForRole($query, $user)`) agar Controller tetap bersih.
3.  **Gunakan Form Requests:** Pindahkan `$request->validate([...])` ke kelas terpisah (misal `StoreResidentRequest`).
4.  **Implementasikan Service Pattern:** Logika kompleks seperti pembuatan `DocumentRequest` beserta `DocumentRequestHistory`, serta pembuatan akun user baru dari resident, dipindahkan ke kelas Service (contoh: `ResidentService->promoteToUser()`).
5.  **Gunakan Enums:** Ganti *hardcoded string* seperti `'DIAJUKAN_RT'` dengan PHP 8.1 Enums (contoh: `DocumentStatus::DIAJUKAN_RT`).
