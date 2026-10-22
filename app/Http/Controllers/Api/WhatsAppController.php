<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\DocumentRequest;
use App\Models\DocumentRequestHistory;
use App\Models\Poll;
use App\Models\Resident;
use App\Models\User;
use App\Services\TelegramService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class WhatsAppController extends Controller
{
    protected WhatsAppService $whatsAppService;

    protected TelegramService $telegramService;

    public function __construct(WhatsAppService $whatsAppService, TelegramService $telegramService)
    {
        $this->whatsAppService = $whatsAppService;
        $this->telegramService = $telegramService;
    }

    /**
     * Check if a given string is a WhatsApp LID (Linked ID)
     */
    private function isLid(?string $number): bool
    {
        if (! $number) {
            return false;
        }
        $clean = preg_replace('/[^0-9]/', '', $number);

        return strlen($clean) >= 12 && ! str_starts_with($clean, '62') && ! str_starts_with($clean, '0');
    }

    /**
     * Find user by whatsapp number or LID (checking whatsapp_lid, whatsapp_number, and phone_number)
     */
    private function getUserByWhatsApp(string $whatsappNumber): ?User
    {
        $rawClean = preg_replace('/[^0-9]/', '', $whatsappNumber);
        $normalized = WhatsAppService::normalizePhoneNumber($whatsappNumber);

        // 1. If it's an LID, check match on whatsapp_lid
        if ($this->isLid($rawClean)) {
            $user = User::with(['resident.rt.rw.village', 'resident.family.rt.rw.village'])
                ->where('whatsapp_lid', $rawClean)
                ->first();

            if ($user) {
                return $user;
            }
        }

        // 2. Check exact match on whatsapp_number
        if ($normalized) {
            $user = User::with(['resident.rt.rw.village', 'resident.family.rt.rw.village'])
                ->where('whatsapp_number', $normalized)
                ->first();

            if ($user) {
                return $user;
            }

            // 3. Check match on phone_number (08... or 628...)
            $altPhone0 = '0'.substr($normalized, 2);
            $user = User::with(['resident.rt.rw.village', 'resident.family.rt.rw.village'])
                ->where(function ($q) use ($normalized, $altPhone0) {
                    $q->where('phone_number', $normalized)
                        ->orWhere('phone_number', $altPhone0);
                })
                ->first();

            if ($user) {
                // Auto-link whatsapp_number
                $user->whatsapp_number = $normalized;
                $user->save();

                return $user;
            }
        }

        return null;
    }

    /**
     * Check if WhatsApp number is linked or recognizable
     */
    public function checkUser(Request $request)
    {
        $request->validate([
            'whatsapp_number' => 'required|string',
        ]);

        $user = $this->getUserByWhatsApp($request->input('whatsapp_number'));

        if (! $user) {
            return response()->json([
                'linked' => false,
                'message' => 'Nomor WhatsApp belum terdaftar di sistem SIWIL.',
            ], 404);
        }

        return response()->json([
            'linked' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->resident?->full_name ?? $user->name ?? $user->username,
                'username' => $user->username,
                'role' => $user->role,
                'phone' => $user->whatsapp_number ?? $user->phone_number,
                'rt' => $user->resident?->rt?->number ?? ($user->rt_id ? 'RT Terdaftar' : '-'),
                'rw' => $user->resident?->rt?->rw?->number ?? ($user->rw_id ? 'RW Terdaftar' : '-'),
                'village' => $user->resident?->rt?->rw?->village?->name ?? 'Desa Terdaftar',
            ],
        ]);
    }

    /**
     * Login directly from WhatsApp to link account
     */
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
            'whatsapp_number' => 'required|string',
        ]);

        $loginInput = trim($request->input('login'));
        $password = $request->input('password');
        $rawWa = trim($request->input('whatsapp_number'));
        $rawClean = preg_replace('/[^0-9]/', '', $rawWa);
        $isLid = $this->isLid($rawClean);
        $whatsappNumber = WhatsAppService::normalizePhoneNumber($rawWa);

        // Normalize if phone format
        $normPhone = WhatsAppService::normalizePhoneNumber($loginInput);
        $altPhone0 = $normPhone ? ('0'.substr($normPhone, 2)) : null;

        $user = User::where(function ($q) use ($loginInput, $normPhone, $altPhone0) {
            $q->where('username', $loginInput)
                ->orWhere('email', $loginInput)
                ->orWhere('phone_number', $loginInput);

            if ($normPhone) {
                $q->orWhere('phone_number', $normPhone)
                    ->orWhere('phone_number', $altPhone0)
                    ->orWhere('whatsapp_number', $normPhone);
            }
        })->first();

        if ($user && Hash::check($password, $user->password)) {
            if ($user->status === 'pending') {
                return response()->json(['error' => 'Akun Anda masih menunggu verifikasi pengurus.'], 403);
            }

            if (in_array($user->status, ['rejected', 'inactive'])) {
                return response()->json(['error' => 'Akun Anda berstatus tidak aktif atau telah ditolak.'], 403);
            }

            // Link WhatsApp number & LID appropriately
            if ($isLid) {
                $user->whatsapp_lid = $rawClean;
                // If user doesn't have a valid phone number stored in whatsapp_number, use their phone_number or login input
                if (empty($user->whatsapp_number) || $this->isLid($user->whatsapp_number)) {
                    $resolvedPhone = WhatsAppService::normalizePhoneNumber($user->phone_number ?: $normPhone);
                    if ($resolvedPhone) {
                        $user->whatsapp_number = $resolvedPhone;
                    }
                }
            } else {
                $user->whatsapp_number = $whatsappNumber;
            }
            $user->save();
            $user->load(['resident.rt.rw.village']);

            return response()->json([
                'message' => 'Berhasil menghubungkan akun SIWIL dengan WhatsApp.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->resident?->full_name ?? $user->name ?? $user->username,
                    'username' => $user->username,
                    'role' => $user->role,
                    'phone' => $user->whatsapp_number ?? $user->phone_number,
                ],
            ]);
        }

        return response()->json(['error' => 'NIK / Email / Nomor HP atau Password salah atau tidak ditemukan.'], 401);
    }

    /**
     * Logout / Unlink WhatsApp
     */
    public function logout(Request $request)
    {
        $request->validate([
            'whatsapp_number' => 'required|string',
        ]);

        $user = $this->getUserByWhatsApp($request->input('whatsapp_number'));

        if (! $user) {
            return response()->json(['error' => 'Nomor WhatsApp belum terhubung ke akun manapun.'], 404);
        }

        $userName = $user->resident?->full_name ?? $user->name ?? $user->username;
        $user->whatsapp_number = null;
        $user->whatsapp_lid = null;
        $user->save();

        return response()->json([
            'message' => "Berhasil keluar. Akun {$userName} telah diputuskan dari bot WhatsApp.",
        ]);
    }

    /**
     * Get available document services list
     */
    public function getDocumentServices()
    {
        $types = [
            ['code' => 'SURAT_KETERANGAN_USAHA', 'name' => 'Surat Keterangan Usaha (SKU)'],
            ['code' => 'SURAT_KETERANGAN_TIDAK_MAMPU', 'name' => 'Surat Keterangan Tidak Mampu (SKTM)'],
            ['code' => 'SURAT_PENGANTAR_NIKAH', 'name' => 'Surat Pengantar Nikah (NA)'],
            ['code' => 'SURAT_KETERANGAN_DOMISILI', 'name' => 'Surat Keterangan Domisili'],
            ['code' => 'SURAT_KETERANGAN_WARIS', 'name' => 'Surat Keterangan Waris'],
            ['code' => 'SURAT_IZIN_KERAMAIAN', 'name' => 'Surat Izin Keramaian'],
            ['code' => 'SURAT_PENGANTAR_KEHILANGAN', 'name' => 'Surat Pengantar Kehilangan'],
            ['code' => 'MEDIASI_SENGKETA_WARGA', 'name' => 'Mediasi Sengketa Warga'],
            ['code' => 'IZIN_RENOVASI_RUMAH', 'name' => 'Izin Renovasi Rumah'],
            ['code' => 'PENDATAAN_TAMU_MENGINAP', 'name' => 'Pendataan Tamu Menginap'],
            ['code' => 'IURAN_KAS_RT', 'name' => 'Iuran Kas RT'],
            ['code' => 'SURAT_REKOMENDASI_INTERNAL', 'name' => 'Surat Rekomendasi RT'],
        ];

        return response()->json(['data' => $types]);
    }

    /**
     * Create document request from WhatsApp
     */
    public function createDocumentRequest(Request $request)
    {
        $request->validate([
            'whatsapp_number' => 'required|string',
            'type' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $user = $this->getUserByWhatsApp($request->input('whatsapp_number'));
        if (! $user) {
            return response()->json(['error' => 'Nomor WhatsApp belum terhubung ke akun SIWIL. Silakan login terlebih dahulu.'], 404);
        }

        $resident = Resident::where('nik', $user->username)->first();
        if (! $resident) {
            return response()->json(['error' => 'Data kependudukan (NIK) Anda belum lengkap di sistem SIWIL. Harap lengkapi profil terlebih dahulu di website.'], 400);
        }

        $requestNumber = 'SRT-'.date('Ymd').'-'.strtoupper(Str::random(4));

        $docReq = DocumentRequest::create([
            'id' => (string) Str::uuid(),
            'request_number' => $requestNumber,
            'type' => $request->input('type'),
            'notes' => $request->input('notes'),
            'resident_id' => $resident->id,
            'status' => 'DIAJUKAN_RT',
        ]);

        DocumentRequestHistory::create([
            'id' => (string) Str::uuid(),
            'document_request_id' => $docReq->id,
            'status' => 'DIAJUKAN_RT',
            'actor_id' => $user->id,
            'notes' => 'Pengajuan surat via Bot WhatsApp',
        ]);

        // Kirim notifikasi via WhatsApp & Telegram
        $this->whatsAppService->sendDocumentCreatedNotification($docReq, $user);
        $this->telegramService->sendDocumentCreatedNotification($docReq, $user);

        return response()->json([
            'message' => 'Pengajuan surat berhasil dikirim.',
            'data' => [
                'id' => $docReq->id,
                'request_number' => $docReq->request_number,
                'type' => $this->whatsAppService->formatDocumentType($docReq->type),
                'status' => $docReq->status,
                'created_at' => $docReq->created_at->format('d M Y, H:i'),
            ],
        ]);
    }

    /**
     * Get document requests history for user
     */
    public function getDocumentRequests(string $whatsappNumber)
    {
        $user = $this->getUserByWhatsApp($whatsappNumber);
        if (! $user) {
            return response()->json(['error' => 'Akun tidak ditemukan'], 404);
        }

        $resident = Resident::where('nik', $user->username)->first();
        if (! $resident) {
            return response()->json(['data' => []]);
        }

        $docs = DocumentRequest::where('resident_id', $resident->id)
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'request_number' => $doc->request_number,
                    'type_raw' => $doc->type,
                    'type' => $this->whatsAppService->formatDocumentType($doc->type),
                    'status' => $doc->status,
                    'notes' => $doc->notes,
                    'can_download' => in_array($doc->status, ['DISETUJUI', 'SELESAI']),
                    'date' => $doc->created_at->format('d/m/Y H:i'),
                ];
            });

        return response()->json(['data' => $docs]);
    }

    /**
     * Download printable PDF of approved document
     */
    public function downloadDocumentPdf(Request $request, string $identifier)
    {
        // $identifier can be document request id OR request_number (e.g. SRT-20261016-ABCD)
        $doc = DocumentRequest::where('id', $identifier)
            ->orWhere('request_number', $identifier)
            ->first();

        if (! $doc) {
            return response()->json(['error' => 'Dokumen pengajuan surat tidak ditemukan.'], 404);
        }

        if (! in_array($doc->status, ['DISETUJUI', 'SELESAI', 'DIAJUKAN_RW', 'DIAJUKAN_DESA'])) {
            return response()->json(['error' => 'Surat masih dalam proses dan belum disetujui untuk dicetak.'], 400);
        }

        $pdfBinary = $this->whatsAppService->generateDocumentPdf($doc);
        if (! $pdfBinary) {
            return response()->json(['error' => 'Gagal membuat file PDF surat.'], 500);
        }

        if ($request->query('base64') === '1' || $request->header('Accept') === 'application/json') {
            return response()->json([
                'filename' => "Surat_{$doc->request_number}.pdf",
                'base64' => base64_encode($pdfBinary),
                'doc' => [
                    'request_number' => $doc->request_number,
                    'type' => $this->whatsAppService->formatDocumentType($doc->type),
                    'status' => $doc->status,
                ],
            ]);
        }

        return response($pdfBinary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"Surat_{$doc->request_number}.pdf\"",
        ]);
    }

    /**
     * Create complaint from WhatsApp
     */
    public function createComplaint(Request $request)
    {
        $request->validate([
            'whatsapp_number' => 'required|string',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'nullable|string|max:255',
        ]);

        $user = $this->getUserByWhatsApp($request->input('whatsapp_number'));
        if (! $user) {
            return response()->json(['error' => 'Nomor WhatsApp belum terhubung ke akun SIWIL. Silakan login terlebih dahulu.'], 404);
        }

        $rtId = $user->rt_id ?? ($user->resident?->rt_id ?? ($user->resident?->family?->rt_id ?? null));
        $rwId = $user->rw_id ?? ($user->resident?->rt?->rw_id ?? ($user->resident?->family?->rt?->rw_id ?? null));
        $villageId = $user->village_id ?? ($user->resident?->rt?->rw?->village_id ?? ($user->resident?->family?->rt?->rw?->village_id ?? null));

        $complaint = Complaint::create([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'location' => $request->input('location'),
            'status' => 'PENDING',
            'rt_id' => $rtId,
            'rw_id' => $rwId,
            'village_id' => $villageId,
        ]);

        // Kirim notifikasi konfirmasi ke warga & petugas RT/RW
        $this->whatsAppService->sendComplaintCreatedNotification($complaint, $user);
        $this->telegramService->sendComplaintCreatedNotification($complaint, $user);

        return response()->json([
            'message' => 'Laporan pengaduan berhasil disampaikan.',
            'data' => [
                'id' => $complaint->id,
                'title' => $complaint->title,
                'status' => $complaint->status,
                'location' => $complaint->location,
                'created_at' => $complaint->created_at->format('d M Y, H:i'),
            ],
        ]);
    }

    /**
     * Get complaints history for user
     */
    public function getComplaints(string $whatsappNumber)
    {
        $user = $this->getUserByWhatsApp($whatsappNumber);
        if (! $user) {
            return response()->json(['error' => 'Akun tidak ditemukan'], 404);
        }

        $complaints = Complaint::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($comp) {
                return [
                    'id' => $comp->id,
                    'title' => $comp->title,
                    'status' => $comp->status,
                    'location' => $comp->location,
                    'date' => $comp->created_at->format('d/m/Y H:i'),
                ];
            });

        return response()->json(['data' => $complaints]);
    }

    /**
     * Get active polls accessible by user
     */
    public function getPolls(string $whatsappNumber)
    {
        $user = $this->getUserByWhatsApp($whatsappNumber);
        if (! $user) {
            return response()->json(['error' => 'Akun tidak ditemukan'], 404);
        }

        $polls = Poll::accessibleBy($user)
            ->where('status', 'ACTIVE')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->with(['options.votes'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($poll) use ($user) {
                $hasVoted = $poll->votes()->where('user_id', $user->id)->exists();
                $userVote = $hasVoted ? $poll->votes()->where('user_id', $user->id)->first() : null;

                $options = $poll->options->values()->map(function ($opt, $idx) use ($userVote) {
                    return [
                        'index' => $idx + 1,
                        'id' => $opt->id,
                        'text' => $opt->text,
                        'votes_count' => $opt->votes->count(),
                        'is_selected' => $userVote && $userVote->poll_option_id === $opt->id,
                    ];
                });

                return [
                    'id' => $poll->id,
                    'title' => $poll->title,
                    'description' => $poll->description,
                    'expires_at' => $poll->expires_at ? $poll->expires_at->format('d M Y, H:i') : null,
                    'has_voted' => $hasVoted,
                    'options' => $options,
                ];
            });

        return response()->json(['data' => $polls]);
    }

    /**
     * Submit vote in a poll
     */
    public function votePoll(Request $request)
    {
        $request->validate([
            'whatsapp_number' => 'required|string',
            'poll_id' => 'required|string',
            'option_number' => 'nullable|integer',
            'option_id' => 'nullable|string',
        ]);

        $user = $this->getUserByWhatsApp($request->input('whatsapp_number'));
        if (! $user) {
            return response()->json(['error' => 'Nomor WhatsApp belum terhubung ke akun SIWIL.'], 404);
        }

        $poll = Poll::accessibleBy($user)->where('id', $request->input('poll_id'))->first();
        if (! $poll) {
            return response()->json(['error' => 'Musyawarah / Polling tidak ditemukan atau tidak tersedia untuk wilayah Anda.'], 404);
        }

        if ($poll->status !== 'ACTIVE' || ($poll->expires_at && $poll->expires_at->isPast())) {
            return response()->json(['error' => 'Musyawarah / Polling ini telah berakhir.'], 400);
        }

        if ($poll->votes()->where('user_id', $user->id)->exists()) {
            return response()->json(['error' => 'Anda sudah memberikan suara pada musyawarah ini sebelumnya.'], 400);
        }

        $option = null;
        if ($request->filled('option_id')) {
            $option = $poll->options()->where('id', $request->input('option_id'))->first();
        } elseif ($request->filled('option_number')) {
            $options = $poll->options->values();
            $idx = (int) $request->input('option_number') - 1;
            if (isset($options[$idx])) {
                $option = $options[$idx];
            }
        }

        if (! $option) {
            return response()->json(['error' => 'Pilihan opsi tidak valid. Periksa kembali nomor pilihan yang tersedia.'], 400);
        }

        $poll->votes()->create([
            'poll_option_id' => $option->id,
            'user_id' => $user->id,
        ]);

        return response()->json([
            'message' => "Terima kasih! Suara Anda untuk pilihan '{$option->text}' pada musyawarah '{$poll->title}' berhasil dicatat.",
            'poll_title' => $poll->title,
            'selected_option' => $option->text,
        ]);
    }
}
