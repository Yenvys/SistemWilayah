<?php

namespace App\Services;

use App\Models\Complaint;
use App\Models\DocumentRequest;
use App\Models\Poll;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WhatsAppService
{
    protected string $botUrl;

    public function __construct()
    {
        $this->botUrl = rtrim(config('services.whatsapp.bot_url', env('WHATSAPP_BOT_URL', 'http://127.0.0.1:3000')), '/');
    }

    /**
     * Normalize Indonesian phone number to international format (628xxx)
     */
    public static function normalizePhoneNumber(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        // Remove non-numeric characters except +
        $clean = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($clean, '0')) {
            $clean = '62'.substr($clean, 1);
        } elseif (str_starts_with($clean, '8')) {
            $clean = '62'.$clean;
        }

        return $clean;
    }

    /**
     * Get valid WhatsApp destination number for a user
     */
    public function getUserWhatsAppNumber(?User $user): ?string
    {
        if (! $user) {
            return null;
        }

        $wa = $user->whatsapp_number;
        $phone = $user->phone_number;

        if (! empty($wa)) {
            $normWa = self::normalizePhoneNumber($wa);
            // If whatsapp_number starts with Indonesian code (62) or no phone_number available
            if (str_starts_with($normWa, '62') || empty($phone)) {
                return $normWa;
            }
        }

        return self::normalizePhoneNumber($phone);
    }

    /**
     * Send text message via Node.js WhatsApp Bot HTTP API with optional interactive buttons
     * Send text message via Node.js WhatsApp Bot HTTP API with optional in-place edit and interactive buttons
     */
    public function sendMessage(
        string $toNumber,
        string $message,
        array $buttons = [],
        ?string $title = null,
        ?string $footer = null,
        ?string $referenceId = null,
        bool $edit = false
    ): bool {
        $normalized = self::normalizePhoneNumber($toNumber);
        if (empty($normalized)) {
            return false;
        }

        try {
            $payload = [
                'number' => $normalized,
                'message' => $message,
            ];

            if (! empty($buttons)) {
                $payload['buttons'] = $buttons;
            }

            if (! empty($title)) {
                $payload['title'] = $title;
            }

            if (! empty($footer)) {
                $payload['footer'] = $footer;
            }

            if (! empty($referenceId)) {
                $payload['referenceId'] = $referenceId;
                $payload['edit'] = $edit;
            }

            $response = Http::timeout(10)->post("{$this->botUrl}/send-message", $payload);

            if ($response->successful()) {
                $resData = $response->json();
                $action = ($resData['edited'] ?? false) ? 'Edited' : 'Sent';
                Log::info("WhatsAppService: {$action} message to {$normalized}");

                return true;
            }

            Log::warning("WhatsAppService: Failed to send message to {$normalized}", [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::warning("WhatsAppService Exception sending message: {$e->getMessage()}");

            return false;
        }
    }

    /**
     * Send document (e.g. PDF) via Node.js WhatsApp Bot HTTP API
     */
    public function sendDocument(string $toNumber, string $pdfBase64, string $filename, ?string $caption = null): bool
    {
        $normalized = self::normalizePhoneNumber($toNumber);
        if (empty($normalized)) {
            return false;
        }

        try {
            $response = Http::timeout(15)->post("{$this->botUrl}/send-document", [
                'number' => $normalized,
                'base64' => $pdfBase64,
                'filename' => $filename,
                'caption' => $caption,
                'mimetype' => 'application/pdf',
            ]);

            if ($response->successful()) {
                Log::info("WhatsAppService: Sent document {$filename} to {$normalized}");

                return true;
            }

            Log::warning("WhatsAppService: Failed to send document {$filename} to {$normalized}", [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::warning("WhatsAppService Exception sending document: {$e->getMessage()}");

            return false;
        }
    }

    /**
     * Generate printable PDF binary for a DocumentRequest
     */
    public function generateDocumentPdf(DocumentRequest $docRequest): ?string
    {
        $docRequest->loadMissing([
            'resident.rt.rw.village',
            'resident.family.rt.rw.village',
            'verifiedBy.resident',
            'rtApprovedBy',
            'rwApprovedBy',
            'desaApprovedBy',
        ]);

        $level = $docRequest->final_approval_level;
        $viewName = match ($level) {
            'RT' => 'dashboard.documents.formats.rt_only',
            'RW' => 'dashboard.documents.formats.rw_only',
            'DESA' => 'dashboard.documents.formats.desa',
            default => 'dashboard.documents.formats.default',
        };

        if (! view()->exists($viewName)) {
            $viewName = 'dashboard.documents.formats.default';
        }

        try {
            $pdf = Pdf::loadView($viewName, [
                'docRequest' => $docRequest,
                'isPdf' => true,
            ]);
            $pdf->setPaper('a4', 'portrait');
            $pdf->getDomPDF()->set_option('defaultMediaType', 'print');
            $pdf->getDomPDF()->set_option('isHtml5ParserEnabled', true);
            $pdf->getDomPDF()->set_option('isRemoteEnabled', true);

            return $pdf->output();
        } catch (\Throwable $e) {
            Log::error("WhatsAppService: PDF generation failed: {$e->getMessage()}");

            return null;
        }
    }

    /**
     * Send direct WhatsApp notification to citizen when their document status is updated.
     */
    public function sendDocumentStatusNotification(
        DocumentRequest $docRequest,
        string $status,
        User $actor,
        ?string $notes = null
    ): bool {
        $docRequest->loadMissing([
            'resident.user',
            'resident.rt.rw.village',
            'resident.family.rt.rw.village',
        ]);

        $wargaUser = $docRequest->resident?->user
            ?? User::where('username', $docRequest->resident?->nik)->first();

        $docTypeName = $this->formatDocumentType($docRequest->type);

        $actorTitle = match ($actor->role) {
            'RT' => 'Ketua RT',
            'RW' => 'Ketua RW',
            'ADMIN_DESA' => 'Pemerintah Desa',
            'SUPER_ADMIN' => 'Administrator Sistem',
            default => 'Petugas Administrasi'
        };
        $actorName = $actor->name ?: $actor->username;

        [$statusTitle, $statusIcon, $statusDescription, $nextStepInfo] = match ($status) {
            'DIAJUKAN_RT' => [
                'Pengajuan Masuk (Menunggu Verifikasi RT)',
                '📝',
                'Pengajuan surat Anda telah masuk ke sistem dan sedang menunggu verifikasi oleh Ketua RT.',
                'Silakan menunggu proses verifikasi awal dari pengurus RT Anda.',
            ],
            'DIAJUKAN_RW' => [
                'Disetujui RT (Diteruskan ke RW)',
                '⏳',
                "Pengajuan surat Anda telah disetujui oleh {$actorTitle} ({$actorName}) dan diteruskan ke Ketua RW untuk verifikasi tahap selanjutnya.",
                'Pengajuan sedang dalam antrean verifikasi pengurus RW.',
            ],
            'DIAJUKAN_DESA' => [
                'Disetujui RW (Diteruskan ke Kantor Desa)',
                '⏳',
                "Pengajuan surat Anda telah disetujui oleh {$actorTitle} ({$actorName}) dan diteruskan ke Kantor Desa untuk verifikasi dan penandatanganan akhir.",
                'Pengajuan sedang diproses oleh pihak Kantor Desa.',
            ],
            'DISETUJUI', 'SELESAI' => [
                'Surat Disetujui & Siap Dicetak / Diunduh',
                '✅',
                "Kabar gembira! Pengajuan surat Anda telah disetujui penuh oleh {$actorTitle} ({$actorName}) dan selesai diproses.",
                'File PDF dokumen resmi Anda terlampir di bawah ini dan siap langsung dicetak.',
            ],
            'DITOLAK' => [
                'Pengajuan Surat Ditolak',
                '❌',
                "Mohon maaf, pengajuan surat Anda ditolak oleh {$actorTitle} ({$actorName}).",
                'Silakan lengkapi berkas atau perbaiki persyaratan sesuai catatan pengurus, kemudian ajukan kembali.',
            ],
            default => [
                "Status: {$status}",
                'ℹ️',
                "Status pengajuan surat Anda telah diperbarui menjadi {$status} oleh {$actorTitle}.",
                'Ketik *surat_status* di bot ini untuk memantau status surat.',
            ]
        };

        $sent = false;
        $wargaWhatsApp = $this->getUserWhatsAppNumber($wargaUser);

        // 1. Notifikasi ke Pemohon (Warga)
        if ($wargaWhatsApp) {
            $wargaName = $docRequest->resident?->full_name ?? $wargaUser->name ?? 'Warga';
            $currentTime = now()->format('d M Y, H:i').' WIB';

            $msg = "📄 *Update Layanan Surat SIWIL*\n\n";
            $msg .= "Halo, *{$wargaName}* 👋\n";
            $msg .= "Pengajuan surat Anda telah diperbarui:\n\n";
            $msg .= "• *Nomor:* `{$docRequest->request_number}`\n";
            $msg .= "• *Layanan:* {$docTypeName}\n";
            $msg .= "• *Status:* {$statusIcon} *{$statusTitle}*\n";
            $msg .= "• *Petugas:* {$actorTitle} ({$actorName})\n";
            if (! empty($notes)) {
                $msg .= "• *Catatan:* _{$notes}_\n";
            }
            $msg .= "• *Waktu:* {$currentTime}\n\n";
            $msg .= "_{$statusDescription}_\n";

            $sent = $this->sendMessage(
                $wargaWhatsApp,
                $msg,
                [],
                null,
                null,
                "doc_{$docRequest->id}",
                true
            );

            // Jika status DISETUJUI / SELESAI, kirimkan langsung file PDF resmi printable ke WhatsApp!
            if (in_array($status, ['DISETUJUI', 'SELESAI'])) {
                try {
                    // Jeda 1 detik agar WhatsApp server mengonfirmasi pengiriman pesan pertama sebelum dokumen dikirim
                    sleep(1);

                    $pdfContent = $this->generateDocumentPdf($docRequest);
                    if ($pdfContent) {
                        $filename = "Surat_{$docRequest->request_number}.pdf";
                        $caption = "📄 *Dokumen Resmi SIWIL*\n\n".
                            "• *Nomor:* `{$docRequest->request_number}`\n".
                            "• *Layanan:* {$docTypeName}\n".
                            "• *Pemohon:* {$wargaName}\n".
                            "• *Status:* ✅ Disetujui\n\n".
                            '_Dokumen resmi siap langsung dicetak pada kertas A4._';

                        $this->sendDocument(
                            $wargaWhatsApp,
                            base64_encode($pdfContent),
                            $filename,
                            $caption
                        );
                    }
                } catch (\Throwable $e) {
                    Log::error("WhatsAppService: Failed to attach PDF to approval notification: {$e->getMessage()}");
                }
            }
        }

        // 2. Notifikasi ke Petugas / Verifikator Selanjutnya jika status DIAJUKAN_RT/RW/DESA
        $this->notifyNextApprover($docRequest, $status, $docTypeName);

        return $sent;
    }

    /**
     * Send notification when a document is first created
     */
    public function sendDocumentCreatedNotification(DocumentRequest $docRequest, User $creator): void
    {
        $docRequest->loadMissing([
            'resident.user',
            'resident.rt.rw.village',
            'resident.family.rt.rw.village',
        ]);

        $wargaUser = $docRequest->resident?->user
            ?? User::where('username', $docRequest->resident?->nik)->first();

        $docTypeName = $this->formatDocumentType($docRequest->type);
        $wargaWhatsApp = $this->getUserWhatsAppNumber($wargaUser);

        // Notifikasi konfirmasi ke Warga pemohon
        if ($wargaWhatsApp) {
            $wargaName = $docRequest->resident?->full_name ?? $wargaUser->name ?? 'Warga';
            $currentTime = now()->format('d M Y, H:i').' WIB';

            $msg = "📄 *Pengajuan Surat Diterima*\n\n";
            $msg .= "Halo, *{$wargaName}* 👋\n";
            $msg .= "Pengajuan surat Anda berhasil dicatat ke sistem SIWIL:\n\n";
            $msg .= "• *Nomor:* `{$docRequest->request_number}`\n";
            $msg .= "• *Layanan:* {$docTypeName}\n";
            $msg .= "• *Status:* 📝 *Menunggu Verifikasi RT*\n";
            if (! empty($docRequest->notes)) {
                $msg .= "• *Keperluan:* {$docRequest->notes}\n";
            }
            $msg .= "• *Waktu:* {$currentTime}\n\n";
            $msg .= "_Pengajuan masuk antrean Ketua RT._\n";

            $this->sendMessage(
                $wargaWhatsApp,
                $msg,
                [],
                null,
                null,
                "doc_{$docRequest->id}",
                false
            );
        }

        // Notifikasi ke Ketua RT
        $this->notifyNextApprover($docRequest, 'DIAJUKAN_RT', $docTypeName);
    }

    /**
     * Notify next approver (RT / RW / Desa) via WhatsApp
     */
    protected function notifyNextApprover(DocumentRequest $docRequest, string $status, string $docTypeName): void
    {
        try {
            $resident = $docRequest->resident;
            if (! $resident) {
                return;
            }

            $rtId = $resident->rt_id ?? ($resident->family?->rt_id ?? null);
            $rwId = $resident->rt?->rw_id ?? ($resident->family?->rt?->rw_id ?? null);
            $villageId = $resident->rt?->rw?->village_id ?? ($resident->family?->rt?->rw?->village_id ?? null);

            $applicantName = $resident->full_name;
            $rtNumber = $docRequest->rt_number;
            $rwNumber = $docRequest->rw_number;

            $approvers = collect();

            if ($status === 'DIAJUKAN_RT' && $rtId) {
                $approvers = User::where('role', 'RT')
                    ->where('rt_id', $rtId)
                    ->get();
                $stageText = "Verifikasi Ketua RT (RT {$rtNumber})";
            } elseif ($status === 'DIAJUKAN_RW' && $rwId) {
                $approvers = User::where('role', 'RW')
                    ->where('rw_id', $rwId)
                    ->get();
                $stageText = "Verifikasi Ketua RW (RW {$rwNumber})";
            } elseif ($status === 'DIAJUKAN_DESA' && $villageId) {
                $approvers = User::whereIn('role', ['ADMIN_DESA', 'SUPER_ADMIN'])
                    ->where('village_id', $villageId)
                    ->get();
                $stageText = 'Verifikasi & Tanda Tangan Kantor Desa';
            }

            foreach ($approvers as $approver) {
                $approverWa = $this->getUserWhatsAppNumber($approver);
                if (empty($approverWa)) {
                    continue;
                }

                $msg = "*Tugas Verifikasi Surat SIWIL*\n\n";
                $msg .= "Halo, *{$approver->name}* 👋\n";
                $msg .= "Terdapat permohonan surat warga yang memerlukan verifikasi:\n\n";
                $msg .= "• *Nomor:* `{$docRequest->request_number}`\n";
                $msg .= "• *Layanan:* {$docTypeName}\n";
                $msg .= "• *Pemohon:* {$applicantName}\n";
                $msg .= "• *Wilayah:* RT {$rtNumber} / RW {$rwNumber}\n";
                $msg .= "• *Tahap:* *{$stageText}*\n";
                if (! empty($docRequest->notes)) {
                    $msg .= "• *Catatan:* {$docRequest->notes}\n";
                }
                $msg .= "\n> Silakan periksa melalui Dashboard SIWIL.";

                $this->sendMessage(
                    $approverWa,
                    $msg,
                    [],
                    '🏛️ SIWIL Verifikasi Petugas',
                    '🏛️ SIWIL - Sistem Informasi Wilayah'
                );
            }
        } catch (\Throwable $e) {
            Log::warning('WhatsAppService notifyNextApprover error: '.$e->getMessage());
        }
    }

    /**
     * Send direct notification to citizen when their complaint status is updated.
     */
    public function sendComplaintStatusNotification(
        Complaint $complaint,
        string $status,
        User $actor
    ): bool {
        $complaint->loadMissing([
            'user.resident',
            'rt',
            'rw',
            'village',
        ]);

        $wargaUser = $complaint->user;
        $wargaWhatsApp = $this->getUserWhatsAppNumber($wargaUser);
        if (! $wargaWhatsApp) {
            return false;
        }

        $actorTitle = match ($actor->role) {
            'RT' => 'Ketua RT',
            'RW' => 'Ketua RW',
            'ADMIN_DESA' => 'Pemerintah Desa',
            'SUPER_ADMIN' => 'Administrator Sistem',
            default => 'Petugas Wilayah'
        };
        $actorName = $actor->name ?: $actor->username;

        [$statusTitle, $statusIcon, $statusDescription, $nextStepInfo] = match ($status) {
            'PROCESSING' => [
                'Pengaduan Sedang Diproses',
                '🔄',
                "Laporan pengaduan Anda sedang ditindaklanjuti dan diproses oleh {$actorTitle} ({$actorName}).",
                'Petugas sedang melakukan pengecekan atau penanganan langsung di lapangan.',
            ],
            'RESOLVED' => [
                'Pengaduan Selesai Ditangani',
                '✅',
                "Laporan pengaduan Anda telah selesai ditangani oleh {$actorTitle} ({$actorName}).",
                'Terima kasih atas partisipasi dan kepedulian Anda dalam menjaga kenyamanan dan ketertiban lingkungan kita.',
            ],
            'REJECTED' => [
                'Pengaduan Ditolak / Tidak Ditindaklanjuti',
                '❌',
                "Laporan pengaduan Anda belum dapat ditindaklanjuti atau ditolak oleh {$actorTitle} ({$actorName}).",
                'Silakan periksa kembali kejelasan informasi laporan Anda atau hubungi pengurus RT/RW setempat jika memerlukan klarifikasi.',
            ],
            default => [
                "Status: {$status}",
                'ℹ️',
                "Status laporan pengaduan Anda telah diperbarui menjadi {$status} oleh {$actorTitle}.",
                'Ketik *lapor_status* di bot ini untuk memantau status pengaduan.',
            ]
        };

        $wargaName = $wargaUser->resident?->full_name ?? $wargaUser->name ?? 'Warga';
        $currentTime = now()->format('d M Y, H:i').' WIB';

        $msg = "📢 *Update Pengaduan Warga*\n\n";
        $msg .= "Halo, *{$wargaName}* 👋\n";
        $msg .= "Status pengaduan Anda telah diperbarui:\n\n";
        $msg .= "• *Topik:* {$complaint->title}\n";
        if (! empty($complaint->location)) {
            $msg .= "• *Lokasi:* {$complaint->location}\n";
        }
        $msg .= "• *Status:* {$statusIcon} *{$statusTitle}*\n";
        $msg .= "• *Petugas:* {$actorTitle} ({$actorName})\n";
        $msg .= "• *Waktu:* {$currentTime}\n\n";
        $msg .= "_{$statusDescription}_\n";

        // Mengirim pesan teks standar dan edit in-place pesan sebelumnya agar tidak dobel
        return $this->sendMessage(
            $wargaWhatsApp,
            $msg,
            [],
            null,
            null,
            "complaint_{$complaint->id}",
            true
        );
    }

    /**
     * Send notification when a complaint is first submitted.
     */
    public function sendComplaintCreatedNotification(Complaint $complaint, User $creator): void
    {
        $complaint->loadMissing([
            'user.resident',
            'rt',
            'rw',
            'village',
        ]);

        $wargaWhatsApp = $this->getUserWhatsAppNumber($creator);

        // 1. Notifikasi konfirmasi ke Pelapor (Warga)
        if ($wargaWhatsApp) {
            $wargaName = $creator->resident?->full_name ?? $creator->name ?? 'Warga';
            $currentTime = now()->format('d M Y, H:i').' WIB';

            $msg = "📢 *Pengaduan Warga Diterima*\n\n";
            $msg .= "Halo, *{$wargaName}* 👋\n";
            $msg .= "Laporan pengaduan Anda berhasil dicatat ke antrean SIWIL:\n\n";
            $msg .= "• *Topik:* {$complaint->title}\n";
            if (! empty($complaint->location)) {
                $msg .= "• *Lokasi:* {$complaint->location}\n";
            }
            $msg .= "• *Status:* ⏳ *Menunggu Tindak Lanjut*\n";
            $msg .= "• *Waktu:* {$currentTime}\n\n";
            $msg .= "_Laporan diteruskan ke pengurus RT setempat._\n";

            $this->sendMessage(
                $wargaWhatsApp,
                $msg,
                [],
                null,
                null,
                "complaint_{$complaint->id}",
                false
            );
        }

        // 2. Notifikasi ke Petugas Wilayah (RT / RW)
        $this->notifyOfficersForComplaint($complaint, $creator);
    }

    /**
     * Notify local officers (RT / RW / Desa) when a new complaint is filed.
     */
    protected function notifyOfficersForComplaint(Complaint $complaint, User $creator): void
    {
        try {
            $rtId = $complaint->rt_id ?? ($creator->rt_id ?? ($creator->resident?->rt_id ?? null));
            $rwId = $complaint->rw_id ?? ($creator->rw_id ?? ($creator->resident?->rt?->rw_id ?? null));

            $applicantName = $creator->resident?->full_name ?? $creator->name ?? $creator->username;
            $rtNumber = $complaint->rt->number ?? ($creator->resident?->rt->number ?? '-');
            $rwNumber = $complaint->rw->number ?? ($creator->resident?->rt->rw->number ?? '-');

            $officers = collect();

            if ($rtId) {
                $rtOfficers = User::where('role', 'RT')
                    ->where('rt_id', $rtId)
                    ->get();
                $officers = $officers->merge($rtOfficers);
            }

            if ($rwId && $officers->isEmpty()) {
                $rwOfficers = User::where('role', 'RW')
                    ->where('rw_id', $rwId)
                    ->get();
                $officers = $officers->merge($rwOfficers);
            }

            foreach ($officers->unique('id') as $officer) {
                $officerWa = $this->getUserWhatsAppNumber($officer);
                if (empty($officerWa)) {
                    continue;
                }

                $msg = "*Laporan Pengaduan Warga Masuk*\n\n";
                $msg .= "Halo, *{$officer->name}* 👋\n";
                $msg .= "Terdapat laporan pengaduan baru di lingkungan Anda:\n\n";
                $msg .= "• *Pelapor:* {$applicantName}\n";
                $msg .= "• *Wilayah:* RT {$rtNumber} / RW {$rwNumber}\n";
                $msg .= "• *Topik:* *{$complaint->title}*\n";
                $msg .= "• *Deskripsi:* \"{$complaint->description}\"\n";
                if (! empty($complaint->location)) {
                    $msg .= "• *Lokasi:* {$complaint->location}\n";
                }
                $msg .= "\n> Silakan buka Dashboard SIWIL untuk tindak lanjut.";

                $this->sendMessage(
                    $officerWa,
                    $msg,
                    [],
                    '🏛️ SIWIL Pengaduan Lingkungan',
                    '🏛️ SIWIL - Sistem Informasi Wilayah'
                );
            }
        } catch (\Throwable $e) {
            Log::warning('WhatsAppService notifyOfficersForComplaint error: '.$e->getMessage());
        }
    }

    /**
     * Broadcast notification when a new poll / voting is created
     */
    public function sendPollCreatedNotification(Poll $poll): void
    {
        try {
            $poll->loadMissing(['creator', 'options']);
            $targetCitizens = $poll->getTargetCitizenUsers();

            $optionsList = '';
            foreach ($poll->options as $index => $option) {
                $optionsList .= '▫️ *'.($index + 1).".* {$option->text}\n";
            }

            $expiresText = $poll->expires_at
                ? $poll->expires_at->format('d M Y, H:i').' WIB'
                : 'Belum ditentukan batas akhir';

            $msg = "*Musyawarah & Polling Warga SIWIL*\n\n";
            $msg .= "Halo Warga SIWIL! 👋\n";
            $msg .= "Ada agenda musyawarah baru yang memerlukan partisipasi suara Anda:\n\n";
            $msg .= "• *Judul:* *{$poll->title}*\n";
            if (! empty($poll->description)) {
                $msg .= "• *Keterangan:* {$poll->description}\n";
            }
            $msg .= "• *Batas Waktu:* {$expiresText}\n\n";
            $msg .= "*Pilihan Suara Tersedia:*\n{$optionsList}\n\n";
            $msg .= '> Tekan salah satu tombol pilihan di bawah untuk memberikan suara:';

            $buttons = [];
            foreach ($poll->options->take(3) as $index => $option) {
                $buttons[] = [
                    'id' => "vote_{$poll->id}_".($index + 1),
                    'text' => '🗳️ '.Str::limit($option->text, 16),
                ];
            }
            $buttons[] = ['id' => 'menu_polling', 'text' => '📊 Lihat Polling'];

            foreach ($targetCitizens as $citizen) {
                $citizenWa = $this->getUserWhatsAppNumber($citizen);
                if ($citizenWa) {
                    $this->sendMessage(
                        $citizenWa,
                        $msg,
                        $buttons,
                        '🏛️ SIWIL Polling & Musyawarah',
                        '🏛️ SIWIL - Sistem Informasi Wilayah'
                    );
                }
            }
        } catch (\Throwable $e) {
            Log::warning('WhatsAppService sendPollCreatedNotification error: '.$e->getMessage());
        }
    }

    /**
     * Broadcast notification when a poll is closed with results
     */
    public function sendPollClosedNotification(Poll $poll, string $resultMessage): void
    {
        try {
            $targetCitizens = $poll->getTargetCitizenUsers();

            $msg = "*Hasil Akhir Musyawarah SIWIL*\n\n";
            $msg .= "Musyawarah warga berikut telah resmi ditutup:\n\n";
            $msg .= "• *Judul:* *{$poll->title}*\n\n";
            $msg .= "*Hasil Perolehan Suara:*\n{$resultMessage}\n\n";
            $msg .= '> Terima kasih atas partisipasi aktif warga dalam musyawarah lingkungan.';

            $buttons = [
                ['id' => 'menu_polling', 'text' => '📊 Lihat Hasil Polling'],
                ['id' => 'menu_utama', 'text' => '🏠 Menu Utama'],
            ];

            foreach ($targetCitizens as $citizen) {
                $citizenWa = $this->getUserWhatsAppNumber($citizen);
                if ($citizenWa) {
                    $this->sendMessage(
                        $citizenWa,
                        $msg,
                        $buttons,
                        '🏛️ SIWIL Hasil Musyawarah',
                        '🏛️ SIWIL - Sistem Informasi Wilayah'
                    );
                }
            }
        } catch (\Throwable $e) {
            Log::warning('WhatsAppService sendPollClosedNotification error: '.$e->getMessage());
        }
    }

    /**
     * Map document type code to human-readable Indonesian name.
     */
    public function formatDocumentType(string $type): string
    {
        $types = [
            'SURAT_KETERANGAN_USAHA' => 'Surat Keterangan Usaha (SKU)',
            'SURAT_KETERANGAN_TIDAK_MAMPU' => 'Surat Keterangan Tidak Mampu (SKTM)',
            'SURAT_PENGANTAR_NIKAH' => 'Surat Pengantar Nikah (NA)',
            'SURAT_KETERANGAN_DOMISILI' => 'Surat Keterangan Domisili',
            'SURAT_KETERANGAN_WARIS' => 'Surat Keterangan Waris',
            'SURAT_IZIN_KERAMAIAN' => 'Surat Izin Keramaian',
            'SURAT_PENGANTAR_KEHILANGAN' => 'Surat Pengantar Kehilangan',
            'MEDIASI_SENGKETA_WARGA' => 'Mediasi Sengketa Warga',
            'IZIN_RENOVASI_RUMAH' => 'Izin Renovasi Rumah',
            'PENDATAAN_TAMU_MENGINAP' => 'Pendataan Tamu Menginap',
            'IURAN_KAS_RT' => 'Iuran Kas RT',
            'SURAT_REKOMENDASI_INTERNAL' => 'Surat Rekomendasi RT',
        ];

        return $types[$type] ?? ucwords(str_replace('_', ' ', strtolower($type)));
    }

    /**
     * Send Password Reset OTP to User via WhatsApp
     */
    public function sendPasswordResetOtp(User $user, string $otp): bool
    {
        $toNumber = $this->getUserWhatsAppNumber($user);
        if (! $toNumber) {
            return false;
        }

        $name = $user->resident?->full_name ?? $user->name ?? $user->username;
        $time = now()->locale('id')->isoFormat('dddd, D MMMM Y - HH:mm');

        $message = "🔐 *KODE OTP RESET PASSWORD SIWIL*\n\n"
            ."Halo, *{$name}* 👋\n\n"
            ."Permintaan reset kata sandi akun SIWIL telah diterima pada {$time} WIB.\n\n"
            ."Gunakan kode OTP berikut untuk melanjutkan proses reset password:\n\n"
            ."👉 *{$otp}* 👈\n\n"
            ."⚠️ *PENTING:*\n"
            ."• Kode ini hanya berlaku selama *10 menit*.\n"
            ."• JANGAN PERNAH memberikan kode OTP ini kepada siapa pun (termasuk admin desa atau pengurus RT/RW).\n"
            ."• Jika Anda tidak meminta reset kata sandi, abaikan pesan ini. Akun Anda tetap aman.\n\n"
            .'_SIWIL AI — Sistem Informasi Wilayah Terpadu_';

        return $this->sendMessage(
            $toNumber,
            $message,
            title: '🔐 Kode OTP Reset Password',
            footer: 'SIWIL AI Keamanan Akun'
        );
    }

    /**
     * Send Password Changed Notification via WhatsApp
     */
    public function sendPasswordChangedNotification(User $user): bool
    {
        $toNumber = $this->getUserWhatsAppNumber($user);
        if (! $toNumber) {
            return false;
        }

        $name = $user->resident?->full_name ?? $user->name ?? $user->username;
        $time = now()->locale('id')->isoFormat('dddd, D MMMM Y - HH:mm');

        $message = "✅ *KATA SANDI BERHASIL DIUBAH*\n\n"
            ."Halo, *{$name}* 👋\n\n"
            ."Kata sandi akun SIWIL Anda telah berhasil diperbarui pada {$time} WIB.\n\n"
            ."Jika Anda merasa tidak melakukan perubahan ini, segera hubungi pengurus RT atau Administrator SIWIL untuk mengamankan akun Anda.\n\n"
            .'_SIWIL AI — Sistem Informasi Wilayah Terpadu_';

        return $this->sendMessage(
            $toNumber,
            $message,
            title: '✅ Keamanan Akun SIWIL',
            footer: 'SIWIL AI Notifikasi'
        );
    }
}
