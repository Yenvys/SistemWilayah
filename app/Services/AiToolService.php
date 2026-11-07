<?php

namespace App\Services;

use App\Models\AiKnowledge;
use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\Budget;
use App\Models\Complaint;
use App\Models\Development;
use App\Models\DocumentRequest;
use App\Models\Emergency;
use App\Models\Family;
use App\Models\Poll;
use App\Models\Resident;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\User;
use App\Models\Village;
use Illuminate\Support\Facades\Auth;

class AiToolService
{
    /**
     * Build dynamic system prompt injecting current user name, role, area, and RBAC boundaries.
     */
    public function buildDynamicSystemPrompt(User $user, ?string $extraContext = null): string
    {
        $role = $user->role;
        $profile = $this->getUserProfileSummary($user);

        $prompt = "Kamu adalah SIWIL AI, Asisten Cerdas dan Customer Service Sistem Informasi Wilayah Terpadu (SIWIL).\n";
        $prompt .= "Bersikaplah profesional, ramah, solutif, ringkas, dan akurat.\n\n";

        $prompt .= "=== IDENTITAS & SESI PENGGUNA SAAT INI (AUTHENTICATED SESSION) ===\n";
        $prompt .= "- Nama Pengguna : {$profile['name']}\n";
        $prompt .= "- Username / ID : {$user->username}\n";
        $prompt .= "- Peran / Role  : {$profile['role_label']} ({$role})\n";
        $prompt .= "- Wilayah Resmi : RT {$profile['rt']} / RW {$profile['rw']}, {$profile['village']}\n";
        $prompt .= "- No. Telepon   : {$profile['phone']}\n";
        $prompt .= "- Alamat        : {$profile['address']}\n\n";

        $prompt .= "=== BATASAN HAK AKSES DATA & KEWENANGAN (RBAC SYSTEM) ===\n";
        switch ($role) {
            case 'WARGA':
                $prompt .= "- Pengguna adalah WARGA BIASA.\n";
                $prompt .= "- HAK AKSES RESMI: Melihat data diri sendiri, anggota keluarga dalam KK miliknya, status pengajuan surat miliknya, riwayat aduan miliknya, pengumuman desa yang aktif, polling musyawarah publik, dan kontak pengurus wilayah (Ketua RT/RW/Kantor Desa).\n";
                $prompt .= "- DILARANG KERAS (KEPATUHAN UU PDP): Mengakses nomor kontak pribadi, NIK, alamat, atau biodata warga lain di luar KK miliknya. Dilarang mengakses pembukuan kas/APBDes internal, audit log sistem, maupun daftar user lain.\n";
                $prompt .= "- Jika pengguna menanyakan data warga lain atau hal yang dilarang, TOLAK DENGAN RAMAH dan jelaskan bahwa data tersebut dilindungi undang-undang privasi kependudukan.\n";
                break;

            case 'RT':
                $prompt .= "- Pengguna adalah PENGURUS RT ({$profile['name']}, RT {$profile['rt']}).\n";
                $prompt .= "- HAK AKSES RESMI: Berwenang penuh atas data kependudukan (warga & KK), verifikasi pengajuan surat, dan penanganan pengaduan warga di wilayah RT {$profile['rt']}. Berhak mengetahui kontak pengurus RT lain dan Ketua RW untuk koordinasi wilayah.\n";
                $prompt .= "- DILARANG: Mengakses data kependudukan warga di RT lain, database menyeluruh tingkat RW, atau database kas/APBDes tingkat Desa.\n";
                break;

            case 'RW':
                $prompt .= "- Pengguna adalah PENGURUS RW ({$profile['name']}, RW {$profile['rw']}).\n";
                $prompt .= "- HAK AKSES RESMI: Berwenang atas statistik wilayah RW {$profile['rw']}, RT-RT binaan, verifikasi surat tingkat RW, kontak pengurus RT binaan, dan laporan aduan di lingkup RW.\n";
                $prompt .= "- DILARANG: Mengakses database menyeluruh desa di luar batas kewenangan RW atau data wilayah RW lain.\n";
                break;

            case 'ADMIN_DESA':
            case 'DESA':
                $prompt .= "- Pengguna adalah PERANGKAT / ADMINISTRATOR DESA ({$profile['village']}).\n";
                $prompt .= "- HAK AKSES RESMI: Berwenang penuh atas administrasi kependudukan desa (seluruh RW dan RT), persetujuan akhir surat, penanganan pengaduan, pengumuman desa, pembangunan fisik & APBDes desa.\n";
                $prompt .= "- DILARANG: Mengakses data teknis kredensial server atau audit log teknis level Super Admin.\n";
                break;

            case 'SUPER_ADMIN':
                $prompt .= "- Pengguna adalah SUPER ADMINISTRATOR SIWIL.\n";
                $prompt .= "- HAK AKSES RESMI: Otoritas tertinggi dengan akses penuh ke seluruh tabel, database, wilayah kependudukan, manajemen akun pengguna, pembangunan, keuangan desa, dan audit log riwayat aktivitas sistem.\n";
                $prompt .= "- KEAMANAN KATA SANDI: Seluruh password di-hash satu arah (Bcrypt) dan tidak dapat dilihat oleh AI maupun database.\n";
                break;

            default:
                $prompt .= "- Pengguna memiliki peran: {$role}. Akses disesuaikan dengan batasan standar sistem.\n";
                break;
        }
        $prompt .= "\n";

        $prompt .= "=== PEDOMAN FUNCTION CALLING / AI TOOLS ===\n";
        $prompt .= "1. Kamu dilengkapi dengan function calling (AI Tools) yang terhubung langsung ke database backend SIWIL.\n";
        $prompt .= "2. JANGAN PERNAH mengarang atau berspekulasi tentang data kependudukan, status surat, aduan, statistik wilayah, atau keuangan desa jika ada tool yang bisa mengambil data tersebut secara live.\n";
        $prompt .= "3. Setiap pemanggilan tool AKAN SELALU divalidasi dan di-filter secara ketat oleh backend sesuai session dan role pengguna di atas (RBAC enforcement).\n";
        $prompt .= "4. Jika tool mengembalikan pesan 'Akses Ditolak', sampaikan kepada pengguna dengan sopan bahwa perannya tidak memiliki hak akses untuk data tersebut.\n";
        $prompt .= "5. Jawab pertanyaan pengguna secara proporsional sesuai yang ditanyakan, tidak bertele-tele, dan berbasis data faktual dari hasil pemanggilan tool.\n\n";

        if ($extraContext) {
            $prompt .= "=== KONTEKS TAMBAHAN WILAYAH ===\n{$extraContext}\n\n";
        }

        return $prompt;
    }

    /**
     * Get tool definitions for Gemini API (functionDeclarations format).
     */
    public function getGeminiTools(User $user): array
    {
        $rawTools = $this->getToolsDefinitions($user);
        $declarations = [];

        foreach ($rawTools as $tool) {
            $declarations[] = [
                'name' => $tool['name'],
                'description' => $tool['description'],
                'parameters' => $tool['parameters'],
            ];
        }

        return $declarations;
    }

    /**
     * Get tool definitions for OpenAI-compatible APIs.
     */
    public function getOpenAiTools(User $user): array
    {
        $rawTools = $this->getToolsDefinitions($user);
        $tools = [];

        foreach ($rawTools as $tool) {
            $tools[] = [
                'type' => 'function',
                'function' => [
                    'name' => $tool['name'],
                    'description' => $tool['description'],
                    'parameters' => $tool['parameters'],
                ],
            ];
        }

        return $tools;
    }

    /**
     * Master schema definition of all 12 sidebar menu tools.
     */
    public function getToolsDefinitions(User $user): array
    {
        $role = $user->role;
        $tools = [];

        // 1. Menu: Dashboard
        $tools[] = [
            'name' => 'fetch_dashboard_data',
            'description' => 'Mengambil ringkasan data metrik statistik dashboard (total penduduk, KK, surat pending, pengaduan pending, aktivitas terkini) yang telah difilter sesuai wilayah dan hak akses role pengguna saat ini.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'category' => [
                        'type' => 'string',
                        'description' => 'Kategori metrik yang ingin diambil secara spesifik: "all", "residents", "documents", "complaints", "announcements". Default: "all".',
                    ],
                ],
            ],
        ];

        // 2. Menu: Wilayah & Penduduk (Warga DILARANG / tidak ditawarkan)
        if ($role !== 'WARGA') {
            $tools[] = [
                'name' => 'fetch_residents_and_areas',
                'description' => 'Mengambil data kependudukan (daftar warga, NIK, jenis kelamin, pekerjaan, alamat) dan struktur wilayah (Desa, RW, RT). Otomatis dibatasi oleh backend: RT hanya wilayah RT-nya, RW hanya wilayah RW-nya, Desa hanya desanya, Super Admin semua.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'search' => [
                            'type' => 'string',
                            'description' => 'Kata kunci pencarian nama warga atau NIK.',
                        ],
                        'rt_number' => [
                            'type' => 'string',
                            'description' => 'Nomor RT yang ingin dicari (contoh: "01", "02").',
                        ],
                        'rw_number' => [
                            'type' => 'string',
                            'description' => 'Nomor RW yang ingin dicari (contoh: "01").',
                        ],
                        'limit' => [
                            'type' => 'integer',
                            'description' => 'Jumlah maksimal data yang diambil (default: 10, maks: 25).',
                        ],
                    ],
                ],
            ];
        }

        // 3. Menu: Pengumuman Desa
        $tools[] = [
            'name' => 'fetch_announcements',
            'description' => 'Mengambil daftar pengumuman resmi dari kantor desa atau wilayah.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'search' => [
                        'type' => 'string',
                        'description' => 'Kata kunci pencarian judul atau isi pengumuman.',
                    ],
                    'limit' => [
                        'type' => 'integer',
                        'description' => 'Jumlah pengumuman yang diambil (default: 5).',
                    ],
                ],
            ],
        ];

        // 4. Menu: Layanan Surat
        $tools[] = [
            'name' => 'fetch_document_requests',
            'description' => 'Mengambil daftar permohonan surat administrasi warga (SKTM, Domisili, SKU, dll). Warga hanya melihat surat miliknya; Pengurus RT/RW/Desa melihat surat sesuai yurisdiksi verifikasinya.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'status' => [
                        'type' => 'string',
                        'description' => 'Filter status pengajuan: "PENDING", "APPROVED_RT", "APPROVED_RW", "APPROVED_DESA", "REJECTED_RT", "REJECTED_RW", "REJECTED_DESA", "COMPLETED", atau "all".',
                    ],
                    'type' => [
                        'type' => 'string',
                        'description' => 'Jenis surat: "SKTM", "DOMISILI", "SURAT_PENGANTAR_NIKAH", "IZIN_RENOVASI_RUMAH", dll.',
                    ],
                    'limit' => [
                        'type' => 'integer',
                        'description' => 'Jumlah data yang diambil (default: 5, maks: 20).',
                    ],
                ],
            ],
        ];

        // 5. Menu: Pengaduan Warga
        $tools[] = [
            'name' => 'fetch_complaints',
            'description' => 'Mengambil daftar pengaduan, aspirasi, atau keluhan warga. Warga hanya dapat melihat aduan miliknya; Pengurus melihat aduan warga di wilayah tugasnya.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'status' => [
                        'type' => 'string',
                        'description' => 'Filter status pengaduan: "PENDING", "IN_PROGRESS", "RESOLVED", "REJECTED", atau "all".',
                    ],
                    'limit' => [
                        'type' => 'integer',
                        'description' => 'Jumlah aduan yang diambil (default: 5).',
                    ],
                ],
            ],
        ];

        // 6. Menu: Musyawarah & Polling
        $tools[] = [
            'name' => 'fetch_polls',
            'description' => 'Mengambil daftar musyawarah, jajak pendapat, atau polling warga yang sedang aktif, opsi pilihan, batas waktu, dan status apakah pengguna saat ini sudah memilih.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'status' => [
                        'type' => 'string',
                        'description' => 'Status musyawarah: "ACTIVE", "CLOSED", atau "all". Default: "ACTIVE".',
                    ],
                    'limit' => [
                        'type' => 'integer',
                        'description' => 'Jumlah polling yang diambil (default: 5).',
                    ],
                ],
            ],
        ];

        // 7. Menu: Data Keluarga (Khusus Warga atau Pengurus)
        $tools[] = [
            'name' => 'fetch_family_data',
            'description' => 'Mengambil data Kartu Keluarga (KK), nomor KK, alamat, dan daftar anggota keluarga (nama, NIK, hubungan status keluarga, pekerjaan). Warga hanya melihat KK miliknya sendiri.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'kk_number' => [
                        'type' => 'string',
                        'description' => 'Nomor KK yang dicari (opsional untuk Warga karena otomatis mengambil miliknya).',
                    ],
                ],
            ],
        ];

        // 8. Menu: Pembangunan & APBDes (Hanya Pengurus Desa / Super Admin / RT-RW)
        if (in_array($role, ['SUPER_ADMIN', 'ADMIN_DESA', 'DESA', 'RW', 'RT'])) {
            $tools[] = [
                'name' => 'fetch_development_and_budgets',
                'description' => 'Mengambil data proyek pembangunan fisik desa dan rincian alokasi anggaran APBDes desa. Hanya dapat diakses oleh Perangkat Desa, RT/RW, dan Super Admin.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'type' => [
                            'type' => 'string',
                            'description' => 'Kategori data: "development" (proyek fisik), "budget" (anggaran APBDes), atau "all".',
                        ],
                        'fiscal_year' => [
                            'type' => 'string',
                            'description' => 'Tahun anggaran (contoh: "2026").',
                        ],
                        'limit' => [
                            'type' => 'integer',
                            'description' => 'Jumlah data yang diambil (default: 5).',
                        ],
                    ],
                ],
            ];
        }

        // 9. Menu: Laporan & Audit Log (Hanya Pengurus / Super Admin)
        if (in_array($role, ['SUPER_ADMIN', 'ADMIN_DESA', 'DESA', 'RW', 'RT'])) {
            $tools[] = [
                'name' => 'fetch_audit_logs',
                'description' => 'Mengambil riwayat log audit aktivitas sistem (penambahan, perubahan, penghapusan data kependudukan). Warga dilarang mengakses audit log.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'action' => [
                            'type' => 'string',
                            'description' => 'Jenis aksi yang difilter: "CREATE", "UPDATE", "DELETE", "LOGIN", atau "all".',
                        ],
                        'limit' => [
                            'type' => 'integer',
                            'description' => 'Jumlah catatan log terbaru (default: 10).',
                        ],
                    ],
                ],
            ];
        }

        // 10. Menu: Manajemen User (Hanya Admin Desa / Super Admin)
        if (in_array($role, ['SUPER_ADMIN', 'ADMIN_DESA', 'DESA'])) {
            $tools[] = [
                'name' => 'fetch_users_directory',
                'description' => 'Mengambil direktori akun pengguna terdaftar di aplikasi SIWIL (username, nama, peran, email, nomor HP, status aktif). Password tidak pernah ditampilkan.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'role_filter' => [
                            'type' => 'string',
                            'description' => 'Filter peran akun: "WARGA", "RT", "RW", "ADMIN_DESA", "SUPER_ADMIN", atau "all".',
                        ],
                        'search' => [
                            'type' => 'string',
                            'description' => 'Kata kunci nama, username, atau email pengguna.',
                        ],
                        'limit' => [
                            'type' => 'integer',
                            'description' => 'Jumlah akun yang diambil (default: 10, maks: 25).',
                        ],
                    ],
                ],
            ];
        }

        // 11. Menu: Kelola AI Knowledge
        $tools[] = [
            'name' => 'fetch_ai_knowledge',
            'description' => 'Mengambil basis pengetahuan resmi SIWIL terkait profil wilayah, SOP layanan surat, pengaduan, dan peraturan desa.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'topic' => [
                        'type' => 'string',
                        'description' => 'Topik pengetahuan yang dicari (contoh: "profil desa", "alur surat", "sensus").',
                    ],
                ],
            ],
        ];

        // 12. Menu: Pengaturan Akun & Profil Pribadi
        $tools[] = [
            'name' => 'fetch_current_user_profile',
            'description' => 'Mengambil biodata dan profil lengkap dari akun pengguna yang sedang login saat ini di session (Nama, NIK, Peran, Alamat, Wilayah RT/RW/Desa, Kontak HP & Email, Status integrasi bot).',
            'parameters' => [
                'type' => 'object',
                'properties' => new \stdClass,
            ],
        ];

        return $tools;
    }

    /**
     * Centralized backend executor with strict RBAC checking based on Auth session user.
     */
    public function executeTool(string $toolName, array $arguments, User $user): array
    {
        // Enforce backend session user authentication
        $authUser = Auth::user() ?? $user;

        return match ($toolName) {
            'fetch_dashboard_data' => $this->handleDashboardData($authUser, $arguments),
            'fetch_residents_and_areas' => $this->handleResidentsAndAreas($authUser, $arguments),
            'fetch_announcements' => $this->handleAnnouncements($authUser, $arguments),
            'fetch_document_requests' => $this->handleDocumentRequests($authUser, $arguments),
            'fetch_complaints' => $this->handleComplaints($authUser, $arguments),
            'fetch_polls' => $this->handlePolls($authUser, $arguments),
            'fetch_family_data' => $this->handleFamilyData($authUser, $arguments),
            'fetch_development_and_budgets' => $this->handleDevelopmentAndBudgets($authUser, $arguments),
            'fetch_audit_logs' => $this->handleAuditLogs($authUser, $arguments),
            'fetch_users_directory' => $this->handleUsersDirectory($authUser, $arguments),
            'fetch_ai_knowledge' => $this->handleAiKnowledge($authUser, $arguments),
            'fetch_current_user_profile' => $this->handleCurrentUserProfile($authUser),
            default => [
                'error' => "Tool '{$toolName}' tidak dikenali oleh sistem SIWIL.",
            ],
        };
    }

    // =========================================================================
    // TOOL HANDLERS WITH STRICT RBAC ENFORCEMENT
    // =========================================================================

    /**
     * 1. Dashboard Handler
     */
    private function handleDashboardData(User $user, array $args): array
    {
        $role = $user->role;

        if ($role === 'WARGA') {
            $resident = $this->getResidentForUser($user);
            $pendingDocs = $resident ? DocumentRequest::where('resident_id', $resident->id)->whereIn('status', ['PENDING', 'APPROVED_RT', 'APPROVED_RW'])->count() : 0;
            $myComplaints = Complaint::where('user_id', $user->id)->count();
            $activePolls = Poll::where('status', 'ACTIVE')->count();
            $activeAnnouncements = Announcement::where('is_active', true)->count();
            $familyCount = ($resident && $resident->family_id) ? Resident::where('family_id', $resident->family_id)->count() : 1;

            return [
                'role' => 'WARGA',
                'menu' => 'Dashboard Warga',
                'summary' => [
                    'surat_sedang_diproses' => $pendingDocs,
                    'total_pengaduan_saya' => $myComplaints,
                    'pengumuman_desa_aktif' => $activeAnnouncements,
                    'polling_musyawarah_aktif' => $activePolls,
                    'jumlah_anggota_keluarga_di_kk' => $familyCount,
                ],
                'note' => 'Statistik dibatasi khusus untuk aktivitas akun warga Anda.',
            ];
        }

        if ($role === 'RT') {
            $rt = $this->getRtForUser($user);
            if (! $rt) {
                return ['error' => 'Data wilayah RT Anda belum terkonfigurasi.'];
            }

            $totalKk = Family::where('rt_id', $rt->id)->count();
            $totalWarga = Resident::where('rt_id', $rt->id)->count();
            $totalPria = Resident::where('rt_id', $rt->id)->whereIn('gender', ['LAKI_LAKI', 'L', 'Laki-laki'])->count();
            $totalWanita = Resident::where('rt_id', $rt->id)->whereIn('gender', ['PEREMPUAN', 'P', 'Perempuan'])->count();
            $pendingDocs = DocumentRequest::where('status', 'PENDING')->whereHas('resident', fn ($q) => $q->where('rt_id', $rt->id))->count();
            $pendingComplaints = Complaint::where('rt_id', $rt->id)->where('status', 'PENDING')->count();

            return [
                'role' => 'RT',
                'menu' => 'Dashboard RT',
                'wilayah' => "RT {$rt->number} / RW ".($rt->rw->number ?? '-'),
                'summary' => [
                    'total_warga_rt' => $totalWarga,
                    'laki_laki' => $totalPria,
                    'perempuan' => $totalWanita,
                    'total_kartu_keluarga' => $totalKk,
                    'surat_menunggu_verifikasi_rt' => $pendingDocs,
                    'pengaduan_warga_pending' => $pendingComplaints,
                ],
            ];
        }

        if ($role === 'RW') {
            $rw = $this->getRwForUser($user);
            if (! $rw) {
                return ['error' => 'Data wilayah RW Anda belum terkonfigurasi.'];
            }

            $totalRts = $rw->rts()->count();
            $totalWarga = Resident::whereHas('rt', fn ($q) => $q->where('rw_id', $rw->id))->count();
            $totalKk = Family::whereHas('rt', fn ($q) => $q->where('rw_id', $rw->id))->count();
            $pendingDocs = DocumentRequest::where('status', 'APPROVED_RT')->whereHas('resident.rt', fn ($q) => $q->where('rw_id', $rw->id))->count();
            $pendingComplaints = Complaint::where('rw_id', $rw->id)->where('status', 'PENDING')->count();

            return [
                'role' => 'RW',
                'menu' => 'Dashboard RW',
                'wilayah' => "RW {$rw->number}",
                'summary' => [
                    'total_rt_binaan' => $totalRts,
                    'total_warga_rw' => $totalWarga,
                    'total_kartu_keluarga' => $totalKk,
                    'surat_menunggu_verifikasi_rw' => $pendingDocs,
                    'pengaduan_lingkungan_pending' => $pendingComplaints,
                ],
            ];
        }

        if (in_array($role, ['ADMIN_DESA', 'DESA'])) {
            $village = $this->getVillageForUser($user);
            $vId = $village?->id;

            $totalRw = Rw::where('village_id', $vId)->count();
            $totalRt = Rt::whereHas('rw', fn ($q) => $q->where('village_id', $vId))->count();
            $totalWarga = Resident::whereHas('rt.rw', fn ($q) => $q->where('village_id', $vId))->count();
            $totalKk = Family::whereHas('rt.rw', fn ($q) => $q->where('village_id', $vId))->count();
            $pendingDocs = DocumentRequest::where('status', 'APPROVED_RW')->whereHas('resident.rt.rw', fn ($q) => $q->where('village_id', $vId))->count();
            $pendingComplaints = Complaint::whereHas('rw', fn ($q) => $q->where('village_id', $vId))->where('status', 'PENDING')->count();

            return [
                'role' => 'ADMIN_DESA',
                'menu' => 'Dashboard Desa',
                'desa' => $village?->name ?? 'Desa',
                'summary' => [
                    'total_rw' => $totalRw,
                    'total_rt' => $totalRt,
                    'total_penduduk_desa' => $totalWarga,
                    'total_kartu_keluarga' => $totalKk,
                    'surat_menunggu_verifikasi_desa' => $pendingDocs,
                    'pengaduan_masuk' => $pendingComplaints,
                ],
            ];
        }

        // SUPER_ADMIN
        return [
            'role' => 'SUPER_ADMIN',
            'menu' => 'Dashboard Super Admin (Sistem Penuh)',
            'summary' => [
                'total_desa' => Village::count(),
                'total_rw' => Rw::count(),
                'total_rt' => Rt::count(),
                'total_penduduk' => Resident::count(),
                'total_kartu_keluarga' => Family::count(),
                'total_akun_pengguna' => User::count(),
                'total_permohonan_surat' => DocumentRequest::count(),
                'total_pengaduan' => Complaint::count(),
                'panggilan_darurat_sos_aktif' => Emergency::where('status', 'ACTIVE')->count(),
            ],
        ];
    }

    /**
     * 2. Wilayah & Penduduk Handler
     */
    private function handleResidentsAndAreas(User $user, array $args): array
    {
        $role = $user->role;

        // Strict RBAC: Warga DILARANG mengakses direktori kependudukan warga lain
        if ($role === 'WARGA') {
            return [
                'status' => 'DENIED',
                'error' => 'Akses Ditolak: Sesuai UU Perlindungan Data Pribadi (UU PDP), akun Warga tidak memiliki izin mengakses direktori kependudukan warga lain. Anda hanya dapat melihat data anggota keluarga Anda sendiri melalui menu Data Keluarga.',
            ];
        }

        $query = Resident::with(['family', 'rt.rw.village', 'user']);
        $limit = min(max((int) ($args['limit'] ?? 10), 1), 25);

        // RBAC Scoping by Role
        if ($role === 'RT') {
            $rt = $this->getRtForUser($user);
            if (! $rt) {
                return ['error' => 'Data RT Anda tidak ditemukan.'];
            }

            // Prohibit querying other RT
            if (! empty($args['rt_number'])) {
                $targetRt = ltrim($args['rt_number'], '0');
                $myRt = ltrim($rt->number, '0');
                if ($targetRt !== $myRt) {
                    return [
                        'status' => 'DENIED',
                        'error' => "Akses Ditolak: Anda adalah Pengurus RT {$rt->number}. Kewenangan Anda terbatas pada RT {$rt->number} dan dilarang mengakses data warga RT {$args['rt_number']}.",
                    ];
                }
            }

            $query->where('rt_id', $rt->id);
        } elseif ($role === 'RW') {
            $rw = $this->getRwForUser($user);
            if (! $rw) {
                return ['error' => 'Data RW Anda tidak ditemukan.'];
            }

            if (! empty($args['rw_number'])) {
                $targetRw = ltrim($args['rw_number'], '0');
                $myRw = ltrim($rw->number, '0');
                if ($targetRw !== $myRw) {
                    return [
                        'status' => 'DENIED',
                        'error' => "Akses Ditolak: Anda adalah Pengurus RW {$rw->number}. Kewenangan Anda terbatas pada RW {$rw->number} dan dilarang mengakses data warga RW {$args['rw_number']}.",
                    ];
                }
            }

            $query->whereHas('rt', fn ($q) => $q->where('rw_id', $rw->id));
        } elseif (in_array($role, ['ADMIN_DESA', 'DESA'])) {
            $village = $this->getVillageForUser($user);
            if ($village) {
                $query->whereHas('rt.rw', fn ($q) => $q->where('village_id', $village->id));
            }
        }
        // SUPER_ADMIN has global query access

        // Optional search filters
        if (! empty($args['search'])) {
            $s = trim($args['search']);
            $query->where(function ($q) use ($s) {
                $q->where('full_name', 'like', "%{$s}%")
                    ->orWhere('nik', 'like', "%{$s}%");
            });
        }

        if (! empty($args['rt_number']) && $role !== 'RT') {
            $rtNum = str_pad(ltrim($args['rt_number'], '0'), 2, '0', STR_PAD_LEFT);
            $query->whereHas('rt', fn ($q) => $q->where('number', $rtNum));
        }

        $residents = $query->take($limit)->get();

        $data = $residents->map(function ($r) {
            return [
                'nik' => $r->nik,
                'nama_lengkap' => $r->full_name,
                'jenis_kelamin' => $r->gender,
                'status_keluarga' => $r->status,
                'pekerjaan' => $r->occupation ?? '-',
                'no_kk' => $r->family?->kk_number ?? '-',
                'alamat' => $r->family?->address ?? '-',
                'rt' => $r->rt?->number ?? '-',
                'rw' => $r->rt?->rw?->number ?? '-',
                'desa' => $r->rt?->rw?->village?->name ?? '-',
                'telepon' => $r->user?->phone_number ?? 'Belum ada No HP',
            ];
        });

        return [
            'menu' => 'Wilayah & Penduduk',
            'total_ditemukan' => $data->count(),
            'daftar_warga' => $data,
        ];
    }

    /**
     * 3. Pengumuman Desa Handler
     */
    private function handleAnnouncements(User $user, array $args): array
    {
        $limit = min(max((int) ($args['limit'] ?? 5), 1), 15);
        $query = Announcement::latest();

        // Warga, RT, RW only view active announcements
        if (in_array($user->role, ['WARGA', 'RT', 'RW'])) {
            $query->where('is_active', true);
        }

        if (! empty($args['search'])) {
            $s = trim($args['search']);
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                    ->orWhere('content', 'like', "%{$s}%");
            });
        }

        $items = $query->take($limit)->get()->map(function ($a) {
            return [
                'id' => $a->id,
                'judul' => $a->title,
                'isi' => $a->content,
                'aktif' => (bool) $a->is_active,
                'tanggal' => $a->created_at->format('d M Y H:i'),
            ];
        });

        return [
            'menu' => 'Pengumuman Desa',
            'total' => $items->count(),
            'pengumuman' => $items,
        ];
    }

    /**
     * 4. Layanan Surat Handler
     */
    private function handleDocumentRequests(User $user, array $args): array
    {
        $role = $user->role;
        $limit = min(max((int) ($args['limit'] ?? 5), 1), 20);
        $query = DocumentRequest::with(['resident.rt.rw.village'])->latest();

        // RBAC Scoping
        if ($role === 'WARGA') {
            $resident = $this->getResidentForUser($user);
            if (! $resident) {
                return ['menu' => 'Layanan Surat', 'total' => 0, 'surat' => [], 'note' => 'Data kependudukan Anda belum terdaftar.'];
            }
            $query->where('resident_id', $resident->id);
        } elseif ($role === 'RT') {
            $rt = $this->getRtForUser($user);
            if (! $rt) {
                return ['error' => 'Data RT Anda tidak ditemukan.'];
            }
            $query->whereHas('resident', fn ($q) => $q->where('rt_id', $rt->id));
        } elseif ($role === 'RW') {
            $rw = $this->getRwForUser($user);
            if (! $rw) {
                return ['error' => 'Data RW Anda tidak ditemukan.'];
            }
            $query->whereHas('resident.rt', fn ($q) => $q->where('rw_id', $rw->id));
        } elseif (in_array($role, ['ADMIN_DESA', 'DESA'])) {
            $village = $this->getVillageForUser($user);
            if ($village) {
                $query->whereHas('resident.rt.rw', fn ($q) => $q->where('village_id', $village->id));
            }
        }

        if (! empty($args['status']) && $args['status'] !== 'all') {
            $query->where('status', strtoupper($args['status']));
        }

        if (! empty($args['type'])) {
            $query->where('type', 'like', "%{$args['type']}%");
        }

        $docs = $query->take($limit)->get()->map(function ($doc) {
            return [
                'nomor_pengajuan' => $doc->request_number ?? $doc->id,
                'jenis_surat' => $doc->type,
                'status' => $doc->status,
                'pemohon' => $doc->resident?->full_name ?? 'Warga',
                'nik_pemohon' => $doc->resident?->nik ?? '-',
                'wilayah' => 'RT '.($doc->resident?->rt?->number ?? '-').' / RW '.($doc->resident?->rt?->rw?->number ?? '-'),
                'keperluan' => $doc->notes ?? '-',
                'tanggal_pengajuan' => $doc->created_at->format('d M Y H:i'),
            ];
        });

        return [
            'menu' => 'Layanan Surat',
            'total' => $docs->count(),
            'daftar_surat' => $docs,
        ];
    }

    /**
     * 5. Pengaduan Warga Handler
     */
    private function handleComplaints(User $user, array $args): array
    {
        $role = $user->role;
        $limit = min(max((int) ($args['limit'] ?? 5), 1), 15);
        $query = Complaint::with(['user.resident', 'rt', 'rw'])->latest();

        // RBAC Scoping
        if ($role === 'WARGA') {
            $query->where('user_id', $user->id);
        } elseif ($role === 'RT') {
            $rt = $this->getRtForUser($user);
            if (! $rt) {
                return ['error' => 'Data RT Anda tidak ditemukan.'];
            }
            $query->where('rt_id', $rt->id);
        } elseif ($role === 'RW') {
            $rw = $this->getRwForUser($user);
            if (! $rw) {
                return ['error' => 'Data RW Anda tidak ditemukan.'];
            }
            $query->where('rw_id', $rw->id);
        } elseif (in_array($role, ['ADMIN_DESA', 'DESA'])) {
            $village = $this->getVillageForUser($user);
            if ($village) {
                $query->whereHas('rw', fn ($q) => $q->where('village_id', $village->id));
            }
        }

        if (! empty($args['status']) && $args['status'] !== 'all') {
            $query->where('status', strtoupper($args['status']));
        }

        $items = $query->take($limit)->get()->map(function ($c) {
            return [
                'id' => $c->id,
                'judul' => $c->title,
                'deskripsi' => $c->description,
                'status' => $c->status,
                'pelapor' => $c->user?->resident?->full_name ?? ($c->user?->name ?? 'Warga'),
                'lokasi' => 'RT '.($c->rt?->number ?? '-').' / RW '.($c->rw?->number ?? '-'),
                'tanggapan' => $c->response ?? 'Belum ada tanggapan',
                'tanggal' => $c->created_at->format('d M Y H:i'),
            ];
        });

        return [
            'menu' => 'Pengaduan Warga',
            'total' => $items->count(),
            'daftar_pengaduan' => $items,
        ];
    }

    /**
     * 6. Musyawarah & Polling Handler
     */
    private function handlePolls(User $user, array $args): array
    {
        $limit = min(max((int) ($args['limit'] ?? 5), 1), 15);
        $query = Poll::with(['options.votes'])->latest();

        if (! empty($args['status']) && $args['status'] !== 'all') {
            $query->where('status', strtoupper($args['status']));
        } else {
            $query->where('status', 'ACTIVE');
        }

        $items = $query->take($limit)->get()->map(function ($poll) use ($user) {
            $hasVoted = $poll->votes()->where('user_id', $user->id)->exists();

            $options = $poll->options->map(function ($opt) {
                return [
                    'id' => $opt->id,
                    'teks_opsi' => $opt->option_text,
                    'jumlah_suara' => $opt->votes()->count(),
                ];
            });

            return [
                'id' => $poll->id,
                'judul' => $poll->title,
                'deskripsi' => $poll->description ?? '-',
                'status' => $poll->status,
                'sudah_voting' => $hasVoted,
                'batas_waktu' => $poll->expires_at ? $poll->expires_at->format('d M Y H:i') : 'Tanpa Batas',
                'opsi_pilihan' => $options,
            ];
        });

        return [
            'menu' => 'Musyawarah & Polling',
            'total' => $items->count(),
            'daftar_polling' => $items,
        ];
    }

    /**
     * 7. Data Keluarga Handler
     */
    private function handleFamilyData(User $user, array $args): array
    {
        $role = $user->role;

        if ($role === 'WARGA') {
            $resident = $this->getResidentForUser($user);
            if (! $resident || ! $resident->family_id) {
                return [
                    'menu' => 'Data Keluarga',
                    'error' => 'Data Kartu Keluarga Anda belum terdaftar di sistem kependudukan.',
                ];
            }

            $family = Family::with(['rt.rw.village'])->find($resident->family_id);
            $members = Resident::where('family_id', $resident->family_id)->get()->map(function ($m) {
                return [
                    'nama_lengkap' => $m->full_name,
                    'nik' => $m->nik,
                    'hubungan_keluarga' => $m->status,
                    'jenis_kelamin' => $m->gender,
                    'tempat_lahir' => $m->birth_place ?? '-',
                    'tanggal_lahir' => $m->birth_date ? $m->birth_date->format('d/m/Y') : '-',
                    'agama' => $m->religion ?? '-',
                    'pekerjaan' => $m->occupation ?? '-',
                ];
            });

            return [
                'menu' => 'Data Keluarga',
                'nomor_kk' => $family->kk_number,
                'alamat' => $family->address,
                'wilayah' => 'RT '.($family->rt?->number ?? '-').' / RW '.($family->rt?->rw?->number ?? '-').', Desa '.($family->rt?->rw?->village?->name ?? '-'),
                'total_anggota' => $members->count(),
                'anggota_keluarga' => $members,
            ];
        }

        // Pengurus / Admin querying by KK number
        if (empty($args['kk_number'])) {
            return [
                'menu' => 'Data Keluarga',
                'error' => 'Harap sertakan parameter "kk_number" untuk mencari data Kartu Keluarga.',
            ];
        }

        $kk = trim($args['kk_number']);
        $family = Family::with(['members', 'rt.rw.village'])->where('kk_number', $kk)->first();

        if (! $family) {
            return [
                'menu' => 'Data Keluarga',
                'error' => "Kartu Keluarga dengan nomor '{$kk}' tidak ditemukan.",
            ];
        }

        // Scoping check for RT
        if ($role === 'RT') {
            $rt = $this->getRtForUser($user);
            if ($family->rt_id !== $rt?->id) {
                return [
                    'status' => 'DENIED',
                    'error' => "Akses Ditolak: Kartu Keluarga {$kk} berada di luar wilayah RT {$rt?->number} Anda.",
                ];
            }
        }

        $members = $family->members->map(function ($m) {
            return [
                'nama_lengkap' => $m->full_name,
                'nik' => $m->nik,
                'hubungan' => $m->status,
                'jenis_kelamin' => $m->gender,
                'pekerjaan' => $m->occupation,
            ];
        });

        return [
            'menu' => 'Data Keluarga',
            'nomor_kk' => $family->kk_number,
            'alamat' => $family->address,
            'wilayah' => 'RT '.($family->rt?->number ?? '-').' / RW '.($family->rt?->rw?->number ?? '-'),
            'anggota_keluarga' => $members,
        ];
    }

    /**
     * 8. Pembangunan & APBDes Handler
     */
    private function handleDevelopmentAndBudgets(User $user, array $args): array
    {
        $role = $user->role;

        // Strict RBAC check
        if ($role === 'WARGA') {
            return [
                'status' => 'DENIED',
                'error' => 'Akses Ditolak: Rincian pembukuan kas dan pagu APBDes internal desa dibatasi untuk Perangkat Desa dan Super Admin. Informasi publik dapat diakses melalui Musyawarah Desa terbuka.',
            ];
        }

        $limit = min(max((int) ($args['limit'] ?? 5), 1), 15);
        $type = $args['type'] ?? 'all';
        $result = ['menu' => 'Pembangunan & APBDes'];

        if ($type === 'development' || $type === 'all') {
            $devs = Development::latest()->take($limit)->get()->map(function ($d) {
                return [
                    'nama_proyek' => $d->name,
                    'lokasi' => $d->location ?? '-',
                    'anggaran' => 'Rp '.number_format($d->budget ?? 0, 0, ',', '.'),
                    'status' => $d->status ?? 'BERJALAN',
                    'progres' => ($d->progress ?? 0).'%',
                    'tahun' => $d->year ?? date('Y'),
                ];
            });
            $result['proyek_pembangunan'] = $devs;
        }

        if ($type === 'budget' || $type === 'all') {
            $query = Budget::latest();
            if (! empty($args['fiscal_year'])) {
                $query->where('fiscal_year', $args['fiscal_year']);
            }
            $budgets = $query->take($limit)->get()->map(function ($b) {
                return [
                    'kategori' => $b->category ?? 'APBDes',
                    'nominal' => 'Rp '.number_format($b->amount ?? 0, 0, ',', '.'),
                    'keterangan' => $b->description ?? '-',
                    'tahun_anggaran' => $b->fiscal_year ?? date('Y'),
                ];
            });
            $result['anggaran_apbdes'] = $budgets;
        }

        return $result;
    }

    /**
     * 9. Laporan & Audit Log Handler
     */
    private function handleAuditLogs(User $user, array $args): array
    {
        $role = $user->role;

        // Strict RBAC: Warga DILARANG
        if ($role === 'WARGA') {
            return [
                'status' => 'DENIED',
                'error' => 'Akses Ditolak: Akun Warga tidak memiliki hak akses ke Audit Log sistem.',
            ];
        }

        $limit = min(max((int) ($args['limit'] ?? 10), 1), 25);
        $query = AuditLog::with('user')->latest();

        if (! empty($args['action']) && $args['action'] !== 'all') {
            $query->where('action', strtoupper($args['action']));
        }

        if ($role === 'RT') {
            $rt = $this->getRtForUser($user);
            $query->where('entity_name', 'RT')->where('entity_id', $rt?->id);
        } elseif ($role === 'RW') {
            $rw = $this->getRwForUser($user);
            $query->where('entity_name', 'RW')->where('entity_id', $rw?->id);
        }

        $logs = $query->take($limit)->get()->map(function ($log) {
            return [
                'waktu' => $log->created_at->format('d/m/Y H:i'),
                'aktor' => $log->user?->name ?? 'System',
                'aksi' => $log->action,
                'entitas' => $log->entity_name,
                'rincian' => $log->details ?? [],
            ];
        });

        return [
            'menu' => 'Laporan & Audit Log',
            'total' => $logs->count(),
            'riwayat_aktivitas' => $logs,
        ];
    }

    /**
     * 10. Manajemen User Handler
     */
    private function handleUsersDirectory(User $user, array $args): array
    {
        $role = $user->role;

        // Strict RBAC: Warga dan RT/RW dibatasi
        if ($role === 'WARGA') {
            return [
                'status' => 'DENIED',
                'error' => 'Akses Ditolak: Akun Warga dilarang mengakses direktori akun pengguna lain.',
            ];
        }

        if (in_array($role, ['RT', 'RW'])) {
            // RT/RW can only view coordinating contact accounts, not sensitive credentials
            return [
                'status' => 'DENIED',
                'error' => 'Akses Ditolak: Menu Manajemen User hanya dapat dikelola oleh Administrator Desa dan Super Admin.',
            ];
        }

        $limit = min(max((int) ($args['limit'] ?? 10), 1), 25);
        $query = User::with('resident')->orderBy('role')->latest();

        if (in_array($role, ['ADMIN_DESA', 'DESA'])) {
            $village = $this->getVillageForUser($user);
            if ($village) {
                $query->where('village_id', $village->id)->where('role', '!=', 'SUPER_ADMIN');
            }
        }

        if (! empty($args['role_filter']) && $args['role_filter'] !== 'all') {
            $query->where('role', strtoupper($args['role_filter']));
        }

        if (! empty($args['search'])) {
            $s = trim($args['search']);
            $query->where(function ($q) use ($s) {
                $q->where('username', 'like', "%{$s}%")
                    ->orWhere('name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%");
            });
        }

        $users = $query->take($limit)->get()->map(function ($u) {
            return [
                'username' => $u->username,
                'nama' => $u->resident?->full_name ?: ($u->name ?: $u->username),
                'role' => $u->role,
                'status' => $u->status ?? 'ACTIVE',
                'email' => $u->email ?? '-',
                'telepon' => $u->phone_number ?? '-',
                'keamanan_password' => 'Terenkripsi Bcrypt Hash (Aman)',
            ];
        });

        return [
            'menu' => 'Manajemen User',
            'total' => $users->count(),
            'daftar_pengguna' => $users,
        ];
    }

    /**
     * 11. Kelola AI Knowledge Handler
     */
    private function handleAiKnowledge(User $user, array $args): array
    {
        $query = AiKnowledge::query();

        if (! empty($args['topic'])) {
            $t = trim($args['topic']);
            $query->where('topic', 'like', "%{$t}%")
                ->orWhere('content', 'like', "%{$t}%");
        }

        $items = $query->take(5)->get()->map(function ($k) {
            return [
                'topik' => $k->topic,
                'konten' => $k->content,
            ];
        });

        return [
            'menu' => 'Kelola AI Knowledge',
            'total' => $items->count(),
            'basis_pengetahuan' => $items,
        ];
    }

    /**
     * 12. Profil Pengguna Saat Ini Handler
     */
    private function handleCurrentUserProfile(User $user): array
    {
        $profile = $this->getUserProfileSummary($user);

        return [
            'menu' => 'Pengaturan Akun / Profil',
            'profil' => $profile,
            'koneksi_bot' => [
                'whatsapp_terhubung' => ! empty($user->whatsapp_number) || ! empty($user->whatsapp_lid),
                'telegram_terhubung' => ! empty($user->telegram_id),
            ],
        ];
    }

    // =========================================================================
    // HELPER METHODS FOR RBAC ENTITY RESOLUTION
    // =========================================================================

    private function getUserProfileSummary(User $user): array
    {
        $resident = $this->getResidentForUser($user);
        $rt = $user->rt ?? ($resident?->rt ?? ($resident?->family?->rt ?? null));
        $rw = $user->rw ?? ($rt?->rw ?? null);
        $village = $user->village ?? ($rw?->village ?? null);

        $roleLabel = match ($user->role) {
            'WARGA' => 'Warga',
            'RT' => 'Pengurus RT '.($rt?->number ?? ''),
            'RW' => 'Pengurus RW '.($rw?->number ?? ''),
            'ADMIN_DESA', 'DESA' => 'Administrator Desa '.($village?->name ?? ''),
            'SUPER_ADMIN' => 'Super Administrator',
            default => str_replace('_', ' ', $user->role),
        };

        return [
            'name' => $resident?->full_name ?? ($user->name ?? $user->username),
            'username' => $user->username,
            'nik' => $resident?->nik ?? $user->username,
            'role' => $user->role,
            'role_label' => trim($roleLabel),
            'rt' => $rt?->number ?? '-',
            'rw' => $rw?->number ?? '-',
            'village' => $village?->name ?? 'Desa',
            'address' => $user->address ?: ($resident?->family?->address ?? '-'),
            'phone' => $user->phone_number ?? 'Belum ada No HP',
            'email' => $user->email ?? 'Belum ada Email',
        ];
    }

    private function getResidentForUser(User $user): ?Resident
    {
        if ($user->resident) {
            return $user->resident;
        }

        return Resident::with(['family', 'rt.rw.village'])->where('nik', $user->username)->first();
    }

    private function getRtForUser(User $user): ?Rt
    {
        if ($user->rt_id) {
            return Rt::with('rw.village')->find($user->rt_id);
        }

        $res = $this->getResidentForUser($user);

        return $res?->rt ?? ($res?->family?->rt ?? null);
    }

    private function getRwForUser(User $user): ?Rw
    {
        if ($user->rw_id) {
            return Rw::with(['rts', 'village'])->find($user->rw_id);
        }

        $rt = $this->getRtForUser($user);

        return $rt?->rw ?? null;
    }

    private function getVillageForUser(User $user): ?Village
    {
        if ($user->village_id) {
            return Village::find($user->village_id);
        }

        $rw = $this->getRwForUser($user);

        return $rw?->village ?? Village::first();
    }
}
