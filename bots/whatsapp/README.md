# 🤖 SIWIL WhatsApp Bot (Full Interactive Buttons & Notifications)

Bot WhatsApp notifikasi otomatis dan pelayanan mandiri warga untuk platform **SIWIL** (Sistem Informasi Wilayah) berbasis **Full Interactive Buttons** (Tombol Klik WhatsApp) dan **Real-Time Notification System**.

---

## 🌟 Fitur Utama

### 1. 🔘 Full Interactive WhatsApp Buttons:
- **Menu Utama Berbasis Tombol:** Cukup klik tombol `[📄 Pengajuan Surat]`, `[📋 Status & Unduh Surat]`, `[📢 Pengaduan Warga]`, `[🗳️ Musyawarah & Polling]`, atau `[👤 Profil Akun]`.
- **Daftar Surat Native Sheet:** Membuka lembar pilihan (*single select list*) jenis surat resmi langsung di dalam WhatsApp.
- **Tombol Navigasi Cepat:** Tombol `[➡️ Lewati Catatan]`, `[❌ Batalkan]`, `[🏠 Menu Utama]`, dsb.
- **Unduh Dokumen PDF 1-Klik:** Tombol `[📥 Unduh Surat PDF]` langsung tersedia di riwayat surat yang telah disetujui.
- **Voting Polling 1-Klik:** Setiap pilihan suara langsung berbentuk tombol klik (misal: `[🗳️ SETUJU]`, `[🗳️ TIDAK SETUJU]`).

---

### 2. 🔔 Notifikasi Real-Time di Setiap Proses Layanan:

#### A. Layanan Pengajuan Surat:
- **Saat Diajukan:**
  - Warga pemohon mendapatkan konfirmasi permohonan + tombol cek status.
  - Ketua RT menerima notifikasi bahwa ada surat warga baru yang butuh tindakan.
- **Saat Tahap Verifikasi RT $\rightarrow$ RW:**
  - Warga menerima notifikasi bahwa surat telah disetujui RT dan diteruskan ke RW.
  - Ketua RW menerima notifikasi permohonan masuk.
- **Saat Tahap Verifikasi RW $\rightarrow$ Kantor Desa:**
  - Warga menerima notifikasi bahwa surat telah disetujui RW dan diteruskan ke Desa.
  - Petugas Kantor Desa menerima notifikasi permohonan masuk.
- **Saat Disetujui / Selesai:**
  - Warga menerima notifikasi resmi persetujuan surat.
  - **File PDF Resmi Printable (A4) otomatis dikirimkan langsung** sebagai dokumen lampiran ke chat WhatsApp warga.
  - Dilengkapi tombol: `[📥 Unduh Ulang PDF]`, `[📋 Riwayat Surat]`, `[🏠 Menu Utama]`.
- **Jika Ditolak:**
  - Warga menerima notifikasi penolakan beserta catatan/alasan dari pengurus dan tombol `[📄 Ajukan Surat Baru]`.

#### B. Layanan Pengaduan Warga:
- **Saat Diajukan:**
  - Pelapor menerima konfirmasi laporan dan nomor tiket aduan.
  - Petugas wilayah (RT/RW) menerima notifikasi rincian aduan warga.
- **Saat Status Diproses (`PROCESSING`):**
  - Pelapor menerima notifikasi bahwa laporan sedang ditangani petugas lapangan.
- **Saat Status Selesai (`RESOLVED`):**
  - Pelapor menerima notifikasi bahwa masalah telah selesai ditangani.
- **Saat Status Ditolak (`REJECTED`):**
  - Pelapor menerima notifikasi beserta catatan pengurus.

#### C. Musyawarah & Polling Warga:
- **Saat Polling Baru Dibuat:**
  - Bot otomatis menyiarkan (broadcast) undangan musyawarah ke seluruh warga di wilayah terkait lengkap dengan **tombol pilihan suara**.
- **Saat Warga Memberikan Suara:**
  - Warga menerima konfirmasi tanda terima suara.
- **Saat Polling Ditutup:**
  - Bot otomatis menyiarkan hasil akhir musyawarah dan pemenang suara ke seluruh warga.

## 🔐 Panduan Login Warga (Bisa Pakai NIK, Email, atau No. HP)

Warga dapat menautkan nomor WhatsApp ke akun SIWIL dengan memilih salah satu dari 3 identitas:
- **Pakai NIK KTP:** `login [NIK] [Password]` (Contoh: `login 3201016666666666 rahasia123`)
- **Pakai Email:** `login [Email] [Password]` (Contoh: `login warga@email.com rahasia123`)
- **Pakai Nomor HP:** `login [No_HP] [Password]` (Contoh: `login 081234567890 rahasia123` atau `login 6281234567890 rahasia123`)

---

## 🚀 Cara Menjalankan Bot

1. Buka PowerShell / Terminal:
   ```powershell
   cd C:\Users\alfar\Documents\code\bot
   npm start
   ```

2. Scan **QR Code** yang muncul di terminal menggunakan aplikasi WhatsApp di HP Anda (**Perangkat Tertaut** $\rightarrow$ **Tautkan Perangkat**).
3. Setelah muncul status `[SUCCESS] 🎉 WhatsApp Bot SIWIL Berhasil Terhubung!`, bot siap beroperasi penuh.

