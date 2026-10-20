# 1. Kebutuhan Fungsional
Fungsionalitas sistem dibagi berdasarkan peran pengguna.

## 1.1. Peran Warga
| ID | Fitur | Deskripsi |
|---|---|---|
| W-01 | Informasi Wilayah | Melihat peta digital wilayahnya (batas RT/RW/Desa) dan informasi umum terkait (data statistik, potensi desa, dll.). |
| W-02 | Layanan Mandiri | Mengajukan permohonan surat/administrasi secara online dan melacak status pengajuannya. |
| W-03 | Pengaduan & Darurat | Melaporkan permasalahan lingkungan dengan foto/geotag, serta tombol darurat (kebakaran, medis, kriminal) untuk respons cepat. |
| W-04 | Akses Transparansi | Melihat pengumuman resmi, informasi kegiatan desa, pembangunan, dan APBDes/Keuangan secara transparan. |
| W-05 | Asisten Virtual (AI) | Berinteraksi dengan chatbot untuk konsultasi publik (misal: "Syarat bikin KTP?", "Bantuan sosial?"). |
| W-06 | Partisipasi Digital | Berpartisipasi dalam polling / musyawarah digital warga. |
| W-07 | Kelola Keluarga (KK) | Memperbarui data keluarga (tambah/hapus anggota KK) dan lapor kejadian mutasi (lahir/mati/pindah). |

## 1.2. Peran Ketua RT
| ID | Fitur | Deskripsi |
|---|---|---|
| RT-01 | Dashboard RT | Melihat ringkasan data warganya (jumlah jiwa, KK, status adminduk). |
| RT-02 | Kelola Data Warga | Menambahkan, mengedit, memvalidasi data KK dan kependudukan warga di RT-nya. |
| RT-03 | Verifikasi & Validasi | Memverifikasi (Tingkat 1) permohonan surat dari warga sebelum diteruskan ke RW/Desa. |
| RT-04 | Penanganan Laporan | Menindaklanjuti pengaduan warga serta merespons peringatan darurat di wilayah RT. |
| RT-05 | Sistem AI untuk RT | Mendapatkan rekomendasi dari AI (daftar warga tidak aktif, notifikasi usia KTP). |

## 1.3. Peran Ketua RW
| ID | Fitur | Deskripsi |
|---|---|---|
| RW-01 | Dashboard RW | Memantau data agregat dari seluruh RT di bawah wilayahnya. |
| RW-02 | Verifikasi Lanjutan | Menyetujui pengajuan surat spesifik (Tingkat 2) dari RT sebelum diteruskan ke Desa. |
| RW-03 | Monitoring Layanan | Memantau alur pengaduan lintas RT dan memfasilitasi polling/musyawarah skala RW. |
| RW-04 | Rekomendasi AI | Menerima notifikasi potensi konflik atau prioritas pembangunan dari sistem analitik spasial. |

## 1.4. Peran Pemerintah Desa / Kelurahan
| ID | Fitur | Deskripsi |
|---|---|---|
| DS-01 | Dashboard Desa | Melihat visualisasi data makro kependudukan, wilayah, anggaran, dan layanan desa. |
| DS-02 | Manajemen Wilayah | Mengelola data hierarki wilayah (Desa, RW, RT) dan data master Area. |
| DS-03 | Finalisasi Layanan | Menyetujui dan menerbitkan surat/administrasi final (dibubuhi Tanda Tangan Elektronik/TTE). |
| DS-04 | Analitik & Keterbukaan | Mempublikasikan APBDes, profil desa, dan mengelola pengumuman resmi. |
| DS-05 | Spasial & Darurat | Menangani eskalasi laporan darurat, manajemen proyek pembangunan, dan peta titik rawan. |

## 1.5. Peran SuperAdmin
| ID | Fitur | Deskripsi |
|---|---|---|
| SA-01 | Manajemen Sistem | Mengelola pengaturan dasar, konfigurasi, audit log, dan pencadangan. |
| SA-02 | Manajemen Pengguna | Melakukan verifikasi akun pengguna baru dan mengatur role/hak akses (RBAC). |
| SA-03 | Manajemen Data AI | Mengelola Knowledge Base (AiKnowledge) yang digunakan oleh Chatbot AI. |

# 2. Kebutuhan Non-Fungsional
| Kategori | Deskripsi |
|---|---|
| Keamanan | - **RBAC**: Hak akses berbasis *Role* (Spatie Permission).<br>- **Enkripsi**: Password di-hash (Bcrypt/Argon2) dan jalur komunikasi via HTTPS.<br>- **Validasi**: Proteksi manipulasi ID untuk setiap _request_. |
| Kinerja | - **Kecepatan**: Waktu respons _page load_ dioptimalkan dengan Vite & TailwindCSS.<br>- **Skalabilitas**: Tahan terhadap beban ribuan pengguna dengan dukungan arsitektur Laravel yang optimal. |
| Pengalaman Pengguna | - **Responsif**: Desain UI/UX dapat diakses secara sempurna dari HP (Mobile-first) maupun Desktop.<br>- **Notifikasi**: Sistem pelacakan status _real-time_ untuk kenyamanan warga. |
| Keandalan | - **Uptime**: Layanan sistem 99.9%.<br>- **Audit Log**: Pencatatan aktivitas pengguna penting (CRUD data sensitif) melalui modul `AuditLog`. |

# 3. Fitur AI & Layanan Cerdas

## 3.1. Chatbot & NLP Asisten
**Fungsi**: Mesin tanya-jawab (24/7) berbahasa Indonesia untuk mendampingi warga terkait syarat administrasi, pencarian data desa, dan layanan publik.
**Integrasi**: Dikontrol melalui modul `AiKnowledge` oleh SuperAdmin dan tampil di _dashboard_ pengguna.

## 3.2. Analitik Spasial (Peta Pintar)
**Fungsi**:
- **Pemetaan Laporan**: Memvisualisasikan laporan kejadian (pengaduan/darurat) dari geotag warga secara _real-time_.
- **Prediksi & Clustering**: Mengidentifikasi titik rawan (banjir, kejahatan) berdasarkan agregasi titik-titik laporan.
- **Transparansi Pembangunan**: Memetakan titik proyek/pembangunan (Development) beserta realisasinya.

## 3.3. Otomasi & Sistem Rekomendasi
**Fungsi**:
- **Smart Notification**: Mengingatkan warga dan ketua RT untuk pembaruan data (misal: usia wajib KTP, kematian, kelahiran).
- **Rekomendasi Layanan**: AI menyarankan modul/bantuan apa yang sesuai berdasarkan data profil keluarga (KK) warga terkait.
