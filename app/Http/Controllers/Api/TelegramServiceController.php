<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\DocumentRequest;
use App\Models\DocumentRequestHistory;
use App\Models\Resident;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TelegramServiceController extends Controller
{
    protected TelegramService $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    private function getUserByTelegramId($telegramId)
    {
        return User::where('telegram_id', $telegramId)->first();
    }

    public function createComplaint(Request $request)
    {
        $request->validate([
            'telegram_id' => 'required|string',
            'title' => 'required|string',
            'description' => 'required|string',
        ]);

        $user = $this->getUserByTelegramId($request->input('telegram_id'));
        if (! $user) {
            return response()->json(['error' => 'User not linked'], 404);
        }

        $complaint = Complaint::create([
            'id' => Str::uuid(),
            'user_id' => $user->id,
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'status' => 'PENDING',
            'rt_id' => $user->rt_id,
            'rw_id' => $user->rw_id,
            'village_id' => $user->village_id,
        ]);

        // Kirim notifikasi sistem dan petugas wilayah
        $this->telegramService->sendComplaintCreatedNotification($complaint, $user);

        return response()->json(['message' => 'Pengaduan berhasil dibuat.', 'data' => $complaint]);
    }

    public function getComplaints($telegramId)
    {
        $user = $this->getUserByTelegramId($telegramId);
        if (! $user) {
            return response()->json(['error' => 'User not linked'], 404);
        }

        $complaints = Complaint::where('user_id', $user->id)->latest()->take(5)->get();

        return response()->json(['data' => $complaints]);
    }

    public function createDocumentRequest(Request $request)
    {
        $request->validate([
            'telegram_id' => 'required|string',
            'type' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $user = $this->getUserByTelegramId($request->input('telegram_id'));
        if (! $user) {
            return response()->json(['error' => 'User not linked'], 404);
        }

        // check if user has resident data
        $resident = Resident::where('nik', $user->username)->first();
        if (! $resident) {
            return response()->json(['error' => 'Data penduduk (NIK) belum lengkap. Harap lengkapi profil di website.'], 400);
        }

        // generate request number: SRT-YYYYMMDD-RANDOM
        $requestNumber = 'SRT-'.date('Ymd').'-'.strtoupper(Str::random(4));

        $docReq = DocumentRequest::create([
            'id' => Str::uuid(),
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
            'notes' => 'Pengajuan surat via Bot Telegram',
        ]);

        // Kirim notifikasi sistem & RT
        $this->telegramService->sendDocumentCreatedNotification($docReq, $user);

        return response()->json(['message' => 'Pengajuan surat berhasil dibuat.', 'data' => $docReq]);
    }

    public function getDocumentServices()
    {
        $types = [
            ['code' => 'SURAT_KETERANGAN_USAHA', 'name' => 'Surat Keterangan Usaha (SKU)', 'short' => 'SKU'],
            ['code' => 'SURAT_KETERANGAN_TIDAK_MAMPU', 'name' => 'Surat Keterangan Tidak Mampu (SKTM)', 'short' => 'SKTM'],
            ['code' => 'SURAT_PENGANTAR_NIKAH', 'name' => 'Surat Pengantar Nikah (NA)', 'short' => 'NIKAH'],
            ['code' => 'SURAT_KETERANGAN_DOMISILI', 'name' => 'Surat Keterangan Domisili', 'short' => 'DOMISILI'],
            ['code' => 'SURAT_KETERANGAN_WARIS', 'name' => 'Surat Keterangan Waris', 'short' => 'WARIS'],
            ['code' => 'SURAT_IZIN_KERAMAIAN', 'name' => 'Surat Izin Keramaian', 'short' => 'KERAMAIAN'],
            ['code' => 'SURAT_PENGANTAR_KEHILANGAN', 'name' => 'Surat Pengantar Kehilangan', 'short' => 'HILANG'],
            ['code' => 'MEDIASI_SENGKETA_WARGA', 'name' => 'Mediasi Sengketa Warga', 'short' => 'MEDIASI'],
            ['code' => 'IZIN_RENOVASI_RUMAH', 'name' => 'Izin Renovasi Rumah', 'short' => 'RENOVASI'],
            ['code' => 'PENDATAAN_TAMU_MENGINAP', 'name' => 'Pendataan Tamu Menginap', 'short' => 'TAMU'],
            ['code' => 'IURAN_KAS_RT', 'name' => 'Iuran Kas RT', 'short' => 'KAS'],
            ['code' => 'SURAT_REKOMENDASI_INTERNAL', 'name' => 'Surat Rekomendasi RT', 'short' => 'REKOM'],
        ];

        return response()->json(['data' => $types]);
    }

    public function getDocumentRequests($telegramId)
    {
        $user = $this->getUserByTelegramId($telegramId);
        if (! $user) {
            return response()->json(['error' => 'User not linked'], 404);
        }

        $resident = Resident::where('nik', $user->username)->first();
        if (! $resident) {
            return response()->json(['data' => []]);
        }

        $docs = DocumentRequest::with(['histories' => fn ($q) => $q->latest()])
            ->where('resident_id', $resident->id)
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'request_number' => $doc->request_number,
                    'type' => $doc->type,
                    'type_name' => $this->telegramService->formatDocumentType($doc->type),
                    'status' => $doc->status,
                    'notes' => $doc->notes,
                    'can_download' => in_array($doc->status, ['DISETUJUI', 'SELESAI']),
                    'date' => $doc->created_at->format('d/m/Y H:i'),
                    'histories' => $doc->histories,
                ];
            });

        return response()->json(['data' => $docs]);
    }

    public function downloadDocumentPdf(Request $request, string $identifier)
    {
        $doc = DocumentRequest::where('id', $identifier)
            ->orWhere('request_number', $identifier)
            ->first();

        if (! $doc) {
            return response()->json(['error' => 'Dokumen pengajuan surat tidak ditemukan.'], 404);
        }

        if (! in_array($doc->status, ['DISETUJUI', 'SELESAI', 'DIAJUKAN_RW', 'DIAJUKAN_DESA'])) {
            return response()->json(['error' => 'Surat masih dalam proses dan belum disetujui untuk dicetak.'], 400);
        }

        $pdfBinary = $this->telegramService->generateDocumentPdf($doc);
        if (! $pdfBinary) {
            return response()->json(['error' => 'Gagal membuat file PDF surat.'], 500);
        }

        if ($request->query('base64') === '1' || $request->header('Accept') === 'application/json') {
            return response()->json([
                'filename' => "Surat_{$doc->request_number}.pdf",
                'base64' => base64_encode($pdfBinary),
                'doc' => [
                    'request_number' => $doc->request_number,
                    'type' => $this->telegramService->formatDocumentType($doc->type),
                    'status' => $doc->status,
                ],
            ]);
        }

        return response($pdfBinary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"Surat_{$doc->request_number}.pdf\"",
        ]);
    }
}
