<?php

namespace App\Services;

use App\Mail\AnnouncementMail;
use App\Mail\ComplaintStatusMail;
use App\Mail\DocumentStatusMail;
use App\Mail\PollNotificationMail;
use App\Models\Announcement;
use App\Models\Complaint;
use App\Models\DocumentRequest;
use App\Models\Poll;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailNotificationService
{
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
     * Kirim email notifikasi perubahan status pengajuan surat ke warga pemohon.
     */
    public function sendDocumentStatusNotification(
        DocumentRequest $docRequest,
        string $status,
        User $actor,
        ?string $notes = null
    ): bool {
        try {
            $docRequest->loadMissing([
                'resident.user',
            ]);

            $wargaUser = $docRequest->resident?->user
                ?? User::where('username', $docRequest->resident?->nik)->first();

            if (! $wargaUser || empty($wargaUser->email)) {
                Log::info("EmailNotificationService: Warga pemohon surat ({$docRequest->id}) tidak memiliki email.");

                return false;
            }

            $docTypeName = $this->formatDocumentType($docRequest->type);

            $actorTitle = match ($actor->role) {
                'RT' => 'Ketua RT',
                'RW' => 'Ketua RW',
                'ADMIN_DESA' => 'Pemerintah Desa',
                'SUPER_ADMIN' => 'Administrator Sistem',
                default => 'Petugas Administrasi'
            };
            $actorName = $actor->name ?: $actor->username;

            [$statusTitle, $statusDescription] = match ($status) {
                'DIAJUKAN_RT' => [
                    'Pengajuan Masuk (Menunggu Verifikasi RT)',
                    'Pengajuan surat Anda telah masuk ke sistem dan sedang menunggu verifikasi oleh Ketua RT.',
                ],
                'DIAJUKAN_RW' => [
                    'Disetujui RT (Diteruskan ke RW)',
                    "Pengajuan surat Anda telah disetujui oleh {$actorTitle} ({$actorName}) dan diteruskan ke Ketua RW untuk verifikasi tahap selanjutnya.",
                ],
                'DIAJUKAN_DESA' => [
                    'Disetujui RW (Diteruskan ke Kantor Desa)',
                    "Pengajuan surat Anda telah disetujui oleh {$actorTitle} ({$actorName}) dan diteruskan ke Kantor Desa untuk verifikasi dan penandatanganan akhir.",
                ],
                'SELESAI', 'APPROVED', 'DISETUJUI_DESA' => [
                    'Surat Selesai & Diterbitkan',
                    "Selamat! Pengajuan surat Anda telah selesai diproses dan disetujui resmi oleh {$actorTitle} ({$actorName}). Dokumen resmi telah siap dan dapat langsung diunduh melalui website.",
                ],
                'DITOLAK_RT' => [
                    'Pengajuan Ditolak oleh RT',
                    "Pengajuan surat Anda belum dapat disetujui oleh {$actorTitle} ({$actorName}). Silakan periksa catatan perbaikan.",
                ],
                'DITOLAK_RW' => [
                    'Pengajuan Ditolak oleh RW',
                    "Pengajuan surat Anda belum dapat disetujui oleh {$actorTitle} ({$actorName}). Silakan periksa catatan perbaikan.",
                ],
                'DITOLAK_DESA', 'REJECTED' => [
                    'Pengajuan Ditolak oleh Desa',
                    "Pengajuan surat Anda belum dapat disetujui oleh {$actorTitle} ({$actorName}). Silakan periksa catatan perbaikan.",
                ],
                default => [
                    'Status Surat Diperbarui',
                    "Status pengajuan surat Anda telah diperbarui menjadi {$status} oleh {$actorTitle} ({$actorName}).",
                ]
            };

            $actionUrl = route('documents.show', $docRequest->id);

            Mail::to($wargaUser->email)->send(new DocumentStatusMail(
                docRequest: $docRequest,
                status: $status,
                statusTitle: $statusTitle,
                statusDescription: $statusDescription,
                docTypeName: $docTypeName,
                userName: $wargaUser->name ?: 'Warga',
                actorTitle: $actorTitle,
                actorName: $actorName,
                notes: $notes,
                actionUrl: $actionUrl
            ));

            Log::info("EmailNotificationService: Berhasil mengirim email status surat [{$status}] ke {$wargaUser->email}");

            return true;
        } catch (\Throwable $e) {
            Log::error('EmailNotificationService sendDocumentStatusNotification error: '.$e->getMessage(), [
                'doc_request_id' => $docRequest->id,
                'status' => $status,
            ]);

            return false;
        }
    }

    /**
     * Kirim email saat pengaduan baru berhasil diajukan oleh warga (konfirmasi penerimaan).
     */
    public function sendComplaintCreatedNotification(Complaint $complaint, User $creator): bool
    {
        try {
            if (empty($creator->email)) {
                return false;
            }

            $actionUrl = route('complaints.show', $complaint->id);

            Mail::to($creator->email)->send(new ComplaintStatusMail(
                complaint: $complaint,
                status: 'PENDING',
                statusTitle: 'Pengaduan Berhasil Dikirim',
                statusDescription: 'Pengaduan Anda telah kami terima dan tercatat di sistem SIWIL. Laporan Anda sedang menunggu verifikasi dari pengurus lingkungan.',
                userName: $creator->name ?: 'Warga',
                actionUrl: $actionUrl
            ));

            Log::info("EmailNotificationService: Berhasil mengirim email konfirmasi pengaduan ke {$creator->email}");

            return true;
        } catch (\Throwable $e) {
            Log::error('EmailNotificationService sendComplaintCreatedNotification error: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Kirim email update status pengaduan ke warga pelapor.
     */
    public function sendComplaintStatusNotification(Complaint $complaint, string $status, User $actor): bool
    {
        try {
            $complaint->loadMissing('user');
            $user = $complaint->user;

            if (! $user || empty($user->email)) {
                return false;
            }

            $actorTitle = match ($actor->role) {
                'RT' => 'Ketua RT',
                'RW' => 'Ketua RW',
                'ADMIN_DESA' => 'Pemerintah Desa',
                default => 'Petugas Wilayah'
            };
            $actorName = $actor->name ?: $actor->username;

            [$statusTitle, $statusDescription] = match ($status) {
                'PROCESSING' => [
                    'Pengaduan Sedang Diproses',
                    "Pengaduan Anda '{$complaint->title}' sedang ditindaklanjuti dan diproses oleh {$actorTitle} ({$actorName}).",
                ],
                'RESOLVED' => [
                    'Pengaduan Telah Selesai Ditangani',
                    "Pengaduan Anda '{$complaint->title}' telah berhasil diselesaikan oleh {$actorTitle} ({$actorName}). Terima kasih atas kepedulian Anda.",
                ],
                'REJECTED' => [
                    'Pengaduan Ditolak',
                    "Pengaduan Anda '{$complaint->title}' belum dapat ditindaklanjuti oleh {$actorTitle} ({$actorName}).",
                ],
                default => [
                    'Status Pengaduan Diperbarui',
                    "Status pengaduan Anda '{$complaint->title}' telah diperbarui menjadi {$status} oleh {$actorTitle} ({$actorName}).",
                ]
            };

            $actionUrl = route('complaints.show', $complaint->id);

            Mail::to($user->email)->send(new ComplaintStatusMail(
                complaint: $complaint,
                status: $status,
                statusTitle: $statusTitle,
                statusDescription: $statusDescription,
                userName: $user->name ?: 'Warga',
                actorTitle: $actorTitle,
                actorName: $actorName,
                actionUrl: $actionUrl
            ));

            Log::info("EmailNotificationService: Berhasil mengirim email status pengaduan [{$status}] ke {$user->email}");

            return true;
        } catch (\Throwable $e) {
            Log::error('EmailNotificationService sendComplaintStatusNotification error: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Siarkan email notifikasi ke warga di wilayah polling saat polling baru dibuat.
     */
    public function sendPollCreatedNotification(Poll $poll): void
    {
        try {
            $poll->loadMissing(['creator', 'options']);
            $targetCitizens = $poll->getTargetCitizenUsers();

            $creatorTitle = match ($poll->creator?->role) {
                'RT' => 'Ketua RT '.($poll->rt?->number ?? ''),
                'RW' => 'Ketua RW '.($poll->rw?->number ?? ''),
                'ADMIN_DESA' => 'Pemerintah Desa',
                default => 'Pengurus Wilayah'
            };

            $actionUrl = route('polls.show', $poll->id);

            $sentCount = 0;
            foreach ($targetCitizens as $citizen) {
                if (! empty($citizen->email)) {
                    try {
                        Mail::to($citizen->email)->send(new PollNotificationMail(
                            poll: $poll,
                            type: 'CREATED',
                            creatorTitle: $creatorTitle,
                            actionUrl: $actionUrl
                        ));
                        $sentCount++;
                    } catch (\Throwable $inner) {
                        Log::warning("Gagal kirim email polling ke {$citizen->email}: ".$inner->getMessage());
                    }
                }
            }

            Log::info("EmailNotificationService: Berhasil menyiarkan email polling baru [{$poll->title}] ke {$sentCount} warga.");
        } catch (\Throwable $e) {
            Log::error('EmailNotificationService sendPollCreatedNotification error: '.$e->getMessage());
        }
    }

    /**
     * Siarkan email hasil musyawarah / polling saat polling resmi ditutup.
     */
    public function sendPollClosedNotification(Poll $poll, string $resultMessage): void
    {
        try {
            $poll->loadMissing(['creator', 'options.votes']);
            $targetCitizens = $poll->getTargetCitizenUsers();

            $creatorTitle = match ($poll->creator?->role) {
                'RT' => 'Ketua RT '.($poll->rt?->number ?? ''),
                'RW' => 'Ketua RW '.($poll->rw?->number ?? ''),
                'ADMIN_DESA' => 'Pemerintah Desa',
                default => 'Pengurus Wilayah'
            };

            $actionUrl = route('polls.show', $poll->id);

            $sentCount = 0;
            foreach ($targetCitizens as $citizen) {
                if (! empty($citizen->email)) {
                    try {
                        Mail::to($citizen->email)->send(new PollNotificationMail(
                            poll: $poll,
                            type: 'CLOSED',
                            creatorTitle: $creatorTitle,
                            resultMessage: $resultMessage,
                            actionUrl: $actionUrl
                        ));
                        $sentCount++;
                    } catch (\Throwable $inner) {
                        Log::warning("Gagal kirim email hasil polling ke {$citizen->email}: ".$inner->getMessage());
                    }
                }
            }

            Log::info("EmailNotificationService: Berhasil menyiarkan email hasil polling [{$poll->title}] ke {$sentCount} warga.");
        } catch (\Throwable $e) {
            Log::error('EmailNotificationService sendPollClosedNotification error: '.$e->getMessage());
        }
    }

    /**
     * Siarkan notifikasi pengumuman / informasi publik ke SEMUA pengguna terdaftar yang memiliki email.
     */
    public function sendAnnouncementNotification(Announcement $announcement): void
    {
        try {
            $announcement->loadMissing('author');
            $users = User::whereNotNull('email')->where('email', '!=', '')->get();
            $actionUrl = route('announcements.index');

            $sentCount = 0;
            foreach ($users as $user) {
                try {
                    Mail::to($user->email)->send(new AnnouncementMail(
                        announcement: $announcement,
                        actionUrl: $actionUrl
                    ));
                    $sentCount++;
                } catch (\Throwable $inner) {
                    Log::warning("Gagal kirim email pengumuman ke {$user->email}: ".$inner->getMessage());
                }
            }

            Log::info("EmailNotificationService: Berhasil menyiarkan pengumuman [{$announcement->title}] ke {$sentCount} email warga terdaftar.");
        } catch (\Throwable $e) {
            Log::error('EmailNotificationService sendAnnouncementNotification error: '.$e->getMessage());
        }
    }
}
