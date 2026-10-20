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
            ->get();

        return response()->json(['data' => $docs]);
    }
}
