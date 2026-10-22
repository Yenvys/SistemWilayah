<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\DocumentRequest;
use App\Models\DocumentRequestHistory;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Support\Str;

class DocumentRequestService
{
    protected TelegramService $telegramService;

    protected WhatsAppService $whatsAppService;

    protected EmailNotificationService $emailNotificationService;

    public function __construct(
        TelegramService $telegramService,
        WhatsAppService $whatsAppService,
        EmailNotificationService $emailNotificationService
    ) {
        $this->telegramService = $telegramService;
        $this->whatsAppService = $whatsAppService;
        $this->emailNotificationService = $emailNotificationService;
    }

    /**
     * @throws \Exception
     */
    public function store(array $data, User $user, $attachmentFile = null): DocumentRequest
    {
        $residentId = null;

        if ($user->role === 'WARGA') {
            $resident = Resident::where('nik', $user->username)->first();
            if (! $resident) {
                throw new \Exception('Data warga Anda belum terdaftar di sistem. Harap lengkapi profil Anda di menu Pengaturan.');
            }
            $residentId = $resident->id;
        } else {
            $resident = Resident::where('nik', $data['nik'])->first();
            $residentId = $resident->id;
        }

        $docId = (string) Str::uuid();
        $requestNumber = 'SRT-'.date('Ymd').'-'.strtoupper(Str::random(4));

        $attachmentPath = null;
        if ($attachmentFile) {
            $attachmentPath = $attachmentFile->store('documents', 'public');
        }

        $documentRequest = DocumentRequest::create([
            'id' => $docId,
            'request_number' => $requestNumber,
            'type' => $data['type'],
            'status' => 'DIAJUKAN_RT',
            'notes' => $data['notes'] ?? null,
            'resident_id' => $residentId,
            'attachment_path' => $attachmentPath,
        ]);

        DocumentRequestHistory::create([
            'id' => (string) Str::uuid(),
            'document_request_id' => $docId,
            'status' => 'DIAJUKAN_RT',
            'actor_id' => $user->id,
            'notes' => 'Pengajuan awal dibuat oleh '.($user->role === 'WARGA' ? 'Warga' : $user->role),
        ]);

        // Notifikasi Telegram, WhatsApp & Email saat surat pertama kali diajukan
        $this->telegramService->sendDocumentCreatedNotification($documentRequest, $user);
        $this->whatsAppService->sendDocumentCreatedNotification($documentRequest, $user);
        $this->emailNotificationService->sendDocumentStatusNotification(
            $documentRequest,
            'DIAJUKAN_RT',
            $user,
            'Pengajuan awal dibuat oleh '.($user->role === 'WARGA' ? 'Warga' : $user->role)
        );

        return $documentRequest;
    }

    public function updateStatus(DocumentRequest $docRequest, array $data, User $user): DocumentRequest
    {
        $status = $data['status'];

        $updateData = [
            'status' => $status,
            'verified_by_id' => $user->id,
            'completed_at' => in_array($status, ['DISETUJUI', 'SELESAI']) ? now() : null,
        ];

        if ($user->role === 'RT') {
            $updateData['rt_approved_by_id'] = $user->id;
        } elseif ($user->role === 'RW') {
            $updateData['rw_approved_by_id'] = $user->id;
        } elseif (in_array($user->role, ['ADMIN_DESA', 'SUPER_ADMIN'])) {
            $updateData['desa_approved_by_id'] = $user->id;
        }

        $docRequest->update($updateData);

        DocumentRequestHistory::create([
            'id' => (string) Str::uuid(),
            'document_request_id' => $docRequest->id,
            'status' => $status,
            'actor_id' => $user->id,
            'notes' => $data['notes'] ?? ('Status diperbarui oleh '.$user->role),
        ]);

        // Kirim notifikasi ke pemohon (warga)
        $wargaUser = $docRequest->resident?->user ?? User::where('username', $docRequest->resident?->nik)->first();
        if ($wargaUser) {
            $docTypeName = ucwords(str_replace('_', ' ', strtolower($docRequest->type)));
            $actorTitle = match ($user->role) {
                'RT' => 'Ketua RT',
                'RW' => 'Ketua RW',
                'ADMIN_DESA' => 'Pemerintah Desa',
                default => 'Petugas Administrasi'
            };

            $notifTitle = match ($status) {
                'DISETUJUI', 'SELESAI' => 'Surat Disetujui & Siap Dicetak',
                'DIAJUKAN_RW' => 'Surat Disetujui RT (Menunggu Verifikasi RW)',
                'DIAJUKAN_DESA' => 'Surat Disetujui RW (Menunggu Verifikasi Desa)',
                'DITOLAK' => 'Pengajuan Surat Ditolak',
                default => 'Status Surat Diperbarui'
            };

            $notifMessage = match ($status) {
                'DISETUJUI', 'SELESAI' => "Pengajuan {$docTypeName} ({$docRequest->request_number}) telah disetujui penuh oleh {$actorTitle} dan kini dapat dicetak.",
                'DIAJUKAN_RW' => "Pengajuan {$docTypeName} ({$docRequest->request_number}) telah disetujui oleh {$actorTitle} dan diteruskan ke RW untuk verifikasi tahap selanjutnya.",
                'DIAJUKAN_DESA' => "Pengajuan {$docTypeName} ({$docRequest->request_number}) telah disetujui oleh {$actorTitle} dan diteruskan ke Kantor Desa untuk verifikasi akhir.",
                'DITOLAK' => "Pengajuan {$docTypeName} ({$docRequest->request_number}) ditolak oleh {$actorTitle}. Catatan: ".($data['notes'] ?? 'Persyaratan belum terpenuhi.'),
                default => "Status pengajuan {$docTypeName} ({$docRequest->request_number}) telah diperbarui menjadi {$status} oleh {$actorTitle}."
            };

            AppNotification::create([
                'user_id' => $wargaUser->id,
                'title' => $notifTitle,
                'message' => $notifMessage,
                'type' => 'SURAT',
                'reference_id' => $docRequest->id,
                'url' => route('documents.show', $docRequest->id),
            ]);
        }

        // Kirim notifikasi Telegram langsung untuk setiap tahapan proses
        $this->telegramService->sendDocumentStatusNotification(
            $docRequest,
            $status,
            $user,
            $data['notes'] ?? null
        );

        // Kirim notifikasi WhatsApp langsung (termasuk kirim file PDF otomatis jika telah disetujui)
        $this->whatsAppService->sendDocumentStatusNotification(
            $docRequest,
            $status,
            $user,
            $data['notes'] ?? null
        );

        // Kirim notifikasi Email langsung ke pemohon surat (termasuk link unduh dokumen)
        $this->emailNotificationService->sendDocumentStatusNotification(
            $docRequest,
            $status,
            $user,
            $data['notes'] ?? null
        );

        return $docRequest;
    }
}
