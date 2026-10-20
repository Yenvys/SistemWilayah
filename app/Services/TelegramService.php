<?php

namespace App\Services;

use App\Models\Complaint;
use App\Models\DocumentRequest;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected ?string $botToken;

    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token', env('TELEGRAM_BOT_TOKEN'));
    }

    /**
     * Send raw text message to a specific Telegram chat/user.
     */
    public function sendMessage(string $chatId, string $message, string $parseMode = 'HTML'): bool
    {
        if (empty($this->botToken)) {
            Log::warning('TelegramService: BOT_TOKEN is not configured.');
            return false;
        }

        if (empty($chatId)) {
            return false;
        }

        try {
            $response = Http::timeout(8)->post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => $parseMode,
                'disable_web_page_preview' => true,
            ]);

            if ($response->successful()) {
                Log::info("TelegramService: Message sent successfully to chat_id {$chatId}");
                return true;
            }

            Log::error('TelegramService: Failed to send message', [
                'chat_id' => $chatId,
                'status' => $response->status(),
                'response' => $response->json(),
            ]);
            return false;
        } catch (\Throwable $e) {
            Log::error('TelegramService Exception: ' . $e->getMessage(), [
                'chat_id' => $chatId,
            ]);
            return false;
        }
    }

    /**
     * Send direct notification to citizen when their document status is updated.
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
                'Silakan menunggu proses verifikasi awal dari pengurus RT Anda.'
            ],
            'DIAJUKAN_RW' => [
                'Disetujui RT (Diteruskan ke RW)',
                '⏳',
                "Pengajuan surat Anda telah disetujui oleh {$actorTitle} ({$actorName}) dan diteruskan ke Ketua RW untuk verifikasi tahap selanjutnya.",
                'Pengajuan sedang dalam antrean verifikasi pengurus RW.'
            ],
            'DIAJUKAN_DESA' => [
                'Disetujui RW (Diteruskan ke Kantor Desa)',
                '⏳',
                "Pengajuan surat Anda telah disetujui oleh {$actorTitle} ({$actorName}) dan diteruskan ke Kantor Desa untuk verifikasi dan penandatanganan akhir.",
                'Pengajuan sedang diproses oleh pihak Kantor Desa.'
            ],
            'DISETUJUI', 'SELESAI' => [
                'Surat Disetujui & Siap Dicetak',
                '✅',
                "Kabar baik! Pengajuan surat Anda telah disetujui penuh oleh {$actorTitle} ({$actorName}) dan selesai diproses.",
                'Dokumen surat sudah selesai. Anda dapat mengunduh/mencetak melalui dashboard website SIWIL atau mengambil surat fisik di kantor yang bersangkutan.'
            ],
            'DITOLAK' => [
                'Pengajuan Surat Ditolak',
                '❌',
                "Mohon maaf, pengajuan surat Anda ditolak oleh {$actorTitle} ({$actorName}).",
                'Silakan lengkapi berkas atau perbaiki persyaratan sesuai catatan di atas, kemudian ajukan kembali.'
            ],
            default => [
                "Status: {$status}",
                'ℹ️',
                "Status pengajuan surat Anda telah diperbarui menjadi {$status} oleh {$actorTitle}.",
                'Gunakan perintah /cek_pengajuan di bot ini untuk memantau status surat.'
            ]
        };

        $sent = false;

        // 1. Notifikasi ke Pemohon (Warga)
        if ($wargaUser && ! empty($wargaUser->telegram_id)) {
            $wargaName = $docRequest->resident?->full_name ?? $wargaUser->name ?? 'Warga';
            $currentTime = now()->format('d M Y, H:i') . ' WIB';

            $msg = "📬 <b>UPDATE PROSES SURAT SIWIL</b>\n\n";
            $msg .= "Halo <b>{$wargaName}</b>,\n";
            $msg .= "Ada pembaruan proses pada surat yang Anda ajukan:\n\n";
            $msg .= "📄 <b>Nomor Surat:</b> <code>{$docRequest->request_number}</code>\n";
            $msg .= "📑 <b>Jenis Surat:</b> <b>{$docTypeName}</b>\n";
            $msg .= "🔄 <b>Tahap Proses:</b> {$statusIcon} <b>{$statusTitle}</b>\n";
            $msg .= "👤 <b>Diproses Oleh:</b> {$actorTitle} (<i>{$actorName}</i>)\n";

            if (! empty($notes)) {
                $msg .= "📝 <b>Catatan:</b> <i>\"{$notes}\"</i>\n";
            }

            $msg .= "⏰ <b>Waktu Pembaruan:</b> {$currentTime}\n\n";
            $msg .= "💡 <i>{$statusDescription}</i>\n\n";
            $msg .= "👉 <b>Langkah Selanjutnya:</b>\n{$nextStepInfo}\n\n";
            $msg .= "<i>Ketik /cek_pengajuan untuk melihat riwayat surat Anda.</i>";

            $sent = $this->sendMessage($wargaUser->telegram_id, $msg);
        }

        // 2. Notifikasi ke Petugas / Verifikator Selanjutnya (jika terhubung ke Telegram)
        $this->notifyNextApprover($docRequest, $status, $docTypeName);

        return $sent;
    }

    /**
     * Send notification when a document is first created (e.g. from Web)
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

        // Notif ke Warga pemohon jika belum menerima konfirmasi
        if ($wargaUser && ! empty($wargaUser->telegram_id)) {
            $wargaName = $docRequest->resident?->full_name ?? $wargaUser->name ?? 'Warga';
            $currentTime = now()->format('d M Y, H:i') . ' WIB';

            $msg = "📄 <b>PENGAJUAN SURAT BARU BERHASIL</b>\n\n";
            $msg .= "Halo <b>{$wargaName}</b>,\n";
            $msg .= "Pengajuan surat Anda berhasil dicatat ke sistem SIWIL:\n\n";
            $msg .= "📄 <b>Nomor Surat:</b> <code>{$docRequest->request_number}</code>\n";
            $msg .= "📑 <b>Jenis Surat:</b> <b>{$docTypeName}</b>\n";
            $msg .= "🔄 <b>Status Saat Ini:</b> 📝 <b>Menunggu Verifikasi RT</b>\n";
            if (! empty($docRequest->notes)) {
                $msg .= "📝 <b>Keperluan:</b> {$docRequest->notes}\n";
            }
            $msg .= "⏰ <b>Waktu:</b> {$currentTime}\n\n";
            $msg .= "ℹ️ <i>Pengajuan Anda akan segera diverifikasi oleh Ketua RT setempat. Setiap perkembangan proses akan langsung dinotifikasikan ke Telegram ini.</i>";

            $this->sendMessage($wargaUser->telegram_id, $msg);
        }

        // Notifikasi ke Ketua RT
        $this->notifyNextApprover($docRequest, 'DIAJUKAN_RT', $docTypeName);
    }

    /**
     * Notify next approver (RT / RW / Desa) if they have linked their Telegram account.
     */
    protected function notifyNextApprover(DocumentRequest $docRequest, string $status, string $docTypeName): void
    {
        try {
            $resident = $docRequest->resident;
            if (! $resident) return;

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
                    ->whereNotNull('telegram_id')
                    ->get();
                $stageText = "Perlu Verifikasi Ketua RT (RT {$rtNumber})";
            } elseif ($status === 'DIAJUKAN_RW' && $rwId) {
                $approvers = User::where('role', 'RW')
                    ->where('rw_id', $rwId)
                    ->whereNotNull('telegram_id')
                    ->get();
                $stageText = "Perlu Verifikasi Ketua RW (RW {$rwNumber})";
            } elseif ($status === 'DIAJUKAN_DESA' && $villageId) {
                $approvers = User::whereIn('role', ['ADMIN_DESA', 'SUPER_ADMIN'])
                    ->where('village_id', $villageId)
                    ->whereNotNull('telegram_id')
                    ->get();
                $stageText = "Perlu Verifikasi & Tanda Tangan Kantor Desa";
            }

            foreach ($approvers as $approver) {
                if (empty($approver->telegram_id)) continue;

                $approverMsg = "🔔 <b>SURAT MASUK MEMERLUKAN VERIFIKASI</b>\n\n";
                $approverMsg .= "Halo <b>{$approver->name}</b>,\n";
                $approverMsg .= "Ada surat warga yang memerlukan tindakan Anda:\n\n";
                $approverMsg .= "📄 <b>Nomor Surat:</b> <code>{$docRequest->request_number}</code>\n";
                $approverMsg .= "📑 <b>Jenis:</b> {$docTypeName}\n";
                $approverMsg .= "👤 <b>Pemohon:</b> {$applicantName} (RT {$rtNumber} / RW {$rwNumber})\n";
                $approverMsg .= "📌 <b>Tahap:</b> <b>{$stageText}</b>\n";
                if (! empty($docRequest->notes)) {
                    $approverMsg .= "📝 <b>Catatan Pemohon:</b> {$docRequest->notes}\n";
                }
                $approverMsg .= "\n<i>Silakan masuk ke Dashboard SIWIL untuk memeriksa berkas dan menyetujui surat ini.</i>";

                $this->sendMessage($approver->telegram_id, $approverMsg);
            }
        } catch (\Throwable $e) {
            Log::warning('TelegramService notifyNextApprover error: ' . $e->getMessage());
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
        if (! $wargaUser || empty($wargaUser->telegram_id)) {
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
                'Petugas sedang melakukan pengecekan atau penanganan langsung di lapangan.'
            ],
            'RESOLVED' => [
                'Pengaduan Selesai Ditangani',
                '✅',
                "Laporan pengaduan Anda telah selesai ditangani oleh {$actorTitle} ({$actorName}).",
                'Terima kasih atas partisipasi dan kepedulian Anda dalam menjaga kenyamanan dan ketertiban lingkungan kita.'
            ],
            'REJECTED' => [
                'Pengaduan Ditolak / Tidak Ditindaklanjuti',
                '❌',
                "Laporan pengaduan Anda belum dapat ditindaklanjuti atau ditolak oleh {$actorTitle} ({$actorName}).",
                'Silakan periksa kembali kelengkapan informasi laporan Anda atau hubungi pengurus RT/RW setempat jika memerlukan klarifikasi.'
            ],
            default => [
                "Status: {$status}",
                'ℹ️',
                "Status laporan pengaduan Anda telah diperbarui menjadi {$status} oleh {$actorTitle}.",
                'Gunakan perintah /cek_pengaduan di bot ini untuk memantau status pengaduan.'
            ]
        };

        $wargaName = $wargaUser->resident?->full_name ?? $wargaUser->name ?? 'Warga';
        $currentTime = now()->format('d M Y, H:i') . ' WIB';

        $msg = "📢 <b>UPDATE STATUS PENGADUAN WARGA</b>\n\n";
        $msg .= "Halo <b>{$wargaName}</b>,\n";
        $msg .= "Ada perkembangan terbaru mengenai pengaduan yang Anda sampaikan:\n\n";
        $msg .= "📌 <b>Judul:</b> <b>{$complaint->title}</b>\n";
        if (! empty($complaint->location)) {
            $msg .= "📍 <b>Lokasi:</b> {$complaint->location}\n";
        }
        $msg .= "🔄 <b>Status Terbaru:</b> {$statusIcon} <b>{$statusTitle}</b>\n";
        $msg .= "👤 <b>Ditangani Oleh:</b> {$actorTitle} (<i>{$actorName}</i>)\n";
        $msg .= "⏰ <b>Waktu Pembaruan:</b> {$currentTime}\n\n";
        $msg .= "💡 <i>{$statusDescription}</i>\n\n";
        $msg .= "👉 <b>Keterangan:</b>\n{$nextStepInfo}\n\n";
        $msg .= "<i>Ketik /cek_pengaduan untuk melihat riwayat pengaduan Anda.</i>";

        return $this->sendMessage($wargaUser->telegram_id, $msg);
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

        // 1. Notifikasi ke Pelapor (Warga) jika memiliki telegram_id
        if (! empty($creator->telegram_id)) {
            $wargaName = $creator->resident?->full_name ?? $creator->name ?? 'Warga';
            $currentTime = now()->format('d M Y, H:i') . ' WIB';

            $msg = "📢 <b>PENGADUAN BERHASIL DIAJUKAN</b>\n\n";
            $msg .= "Halo <b>{$wargaName}</b>,\n";
            $msg .= "Laporan pengaduan Anda telah berhasil dicatat ke sistem SIWIL:\n\n";
            $msg .= "📌 <b>Judul:</b> <b>{$complaint->title}</b>\n";
            $msg .= "📝 <b>Deskripsi:</b> <i>\"{$complaint->description}\"</i>\n";
            if (! empty($complaint->location)) {
                $msg .= "📍 <b>Lokasi:</b> {$complaint->location}\n";
            }
            $msg .= "🔄 <b>Status Saat Ini:</b> ⏳ <b>Menunggu Tindak Lanjut (PENDING)</b>\n";
            $msg .= "⏰ <b>Waktu Lapor:</b> {$currentTime}\n\n";
            $msg .= "ℹ️ <i>Pengaduan Anda akan segera ditinjau dan ditindaklanjuti oleh petugas wilayah. Setiap perkembangan proses akan langsung dinotifikasikan ke Telegram ini.</i>\n\n";
            $msg .= "<i>Ketik /cek_pengaduan untuk mengecek status laporan kapan saja.</i>";

            $this->sendMessage($creator->telegram_id, $msg);
        }

        // 2. Notifikasi ke Petugas Wilayah (RT / RW / Admin Desa)
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
                    ->whereNotNull('telegram_id')
                    ->get();
                $officers = $officers->merge($rtOfficers);
            }

            if ($rwId && $officers->isEmpty()) {
                $rwOfficers = User::where('role', 'RW')
                    ->where('rw_id', $rwId)
                    ->whereNotNull('telegram_id')
                    ->get();
                $officers = $officers->merge($rwOfficers);
            }

            foreach ($officers->unique('id') as $officer) {
                if (empty($officer->telegram_id)) continue;

                $officerMsg = "🔔 <b>PENGADUAN WARGA BARU MASUK</b>\n\n";
                $officerMsg .= "Halo <b>{$officer->name}</b>,\n";
                $officerMsg .= "Ada laporan pengaduan baru dari warga di wilayah Anda:\n\n";
                $officerMsg .= "👤 <b>Pelapor:</b> {$applicantName} (RT {$rtNumber} / RW {$rwNumber})\n";
                $officerMsg .= "📌 <b>Judul:</b> <b>{$complaint->title}</b>\n";
                $officerMsg .= "📝 <b>Deskripsi:</b> <i>\"{$complaint->description}\"</i>\n";
                if (! empty($complaint->location)) {
                    $officerMsg .= "📍 <b>Lokasi:</b> {$complaint->location}\n";
                }
                $officerMsg .= "\n<i>Silakan buka Dashboard SIWIL untuk memeriksa dan menindaklanjuti laporan ini.</i>";

                $this->sendMessage($officer->telegram_id, $officerMsg);
            }
        } catch (\Throwable $e) {
            Log::warning('TelegramService notifyOfficersForComplaint error: ' . $e->getMessage());
        }
    }

    /**
     * Broadcast notification when a poll is created to target citizens who have telegram_id.
     */
    public function sendPollCreatedNotification(\App\Models\Poll $poll): void
    {
        try {
            $poll->loadMissing(['creator', 'options']);
            $targetCitizens = $poll->getTargetCitizenUsers();

            $optionsList = "";
            foreach ($poll->options as $index => $option) {
                $optionsList .= "▫️ <b>" . ($index + 1) . ".</b> " . htmlspecialchars($option->text) . "\n";
            }

            $expiresText = $poll->expires_at
                ? $poll->expires_at->format('d M Y, H:i') . ' WIB'
                : 'Belum ditentukan batas akhir';

            $creatorRole = $poll->creator?->role ?? 'Pengurus';
            $creatorTitle = match ($creatorRole) {
                'RT' => 'Ketua RT',
                'RW' => 'Ketua RW',
                'ADMIN_DESA' => 'Pemerintah Desa',
                default => 'Pengurus Wilayah'
            };

            $pollUrl = route('polls.show', $poll->id);

            foreach ($targetCitizens as $citizen) {
                if (empty($citizen->telegram_id)) continue;

                $citizenName = $citizen->resident?->full_name ?? $citizen->name ?? $citizen->username;

                $msg = "🗳️ <b>MUSYAWARAH & POLLING WARGA SIWIL</b>\n\n";
                $msg .= "Halo <b>" . htmlspecialchars($citizenName) . "</b>! 👋\n";
                $msg .= "Ada musyawarah / polling baru dari <b>{$creatorTitle}</b> yang memerlukan partisipasi suara Anda:\n\n";
                $msg .= "📌 <b>Judul:</b> <b>" . htmlspecialchars($poll->title) . "</b>\n";
                if (! empty($poll->description)) {
                    $msg .= "📝 <b>Keterangan:</b> <i>\"" . htmlspecialchars($poll->description) . "\"</i>\n";
                }
                $msg .= "⏰ <b>Batas Waktu:</b> {$expiresText}\n\n";
                $msg .= "<b>Pilihan Suara Tersedia:</b>\n{$optionsList}\n";
                $msg .= "👉 <a href=\"{$pollUrl}\">Klik di sini untuk berpartisipasi di SIWIL</a>\n\n";
                $msg .= "<i>Suara Anda sangat menentukan masa depan dan kemajuan lingkungan kita.</i>";

                $this->sendMessage($citizen->telegram_id, $msg);
            }
        } catch (\Throwable $e) {
            Log::warning('TelegramService sendPollCreatedNotification error: ' . $e->getMessage());
        }
    }

    /**
     * Broadcast notification when a poll is closed with results to target citizens who have telegram_id.
     */
    public function sendPollClosedNotification(\App\Models\Poll $poll, string $resultMessage): void
    {
        try {
            $poll->loadMissing(['creator', 'options.votes']);
            $targetCitizens = $poll->getTargetCitizenUsers();
            $pollUrl = route('polls.show', $poll->id);

            foreach ($targetCitizens as $citizen) {
                if (empty($citizen->telegram_id)) continue;

                $citizenName = $citizen->resident?->full_name ?? $citizen->name ?? $citizen->username;

                $msg = "🏛️ <b>HASIL AKHIR MUSYAWARAH SIWIL</b>\n\n";
                $msg .= "Halo <b>" . htmlspecialchars($citizenName) . "</b>,\n";
                $msg .= "Musyawarah warga berikut telah resmi ditutup dan hasilnya telah dipublikasikan ke Informasi Desa:\n\n";
                $msg .= "📌 <b>Judul:</b> <b>" . htmlspecialchars($poll->title) . "</b>\n\n";
                $msg .= "📊 <b>Hasil Keputusan:</b>\n" . htmlspecialchars($resultMessage) . "\n\n";
                $msg .= "👉 <a href=\"{$pollUrl}\">Lihat detail perolehan suara di Dashboard SIWIL</a>\n\n";
                $msg .= "<i>Terima kasih atas partisipasi aktif warga dalam musyawarah lingkungan.</i>";

                $this->sendMessage($citizen->telegram_id, $msg);
            }
        } catch (\Throwable $e) {
            Log::warning('TelegramService sendPollClosedNotification error: ' . $e->getMessage());
        }
    }
}
