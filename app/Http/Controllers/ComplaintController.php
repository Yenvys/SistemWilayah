<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\Complaint;
use App\Models\User;
use App\Services\EmailNotificationService;
use App\Services\TelegramService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;

class ComplaintController extends Controller
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

    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Complaint::with(['user', 'rt', 'rw', 'village'])->latest();

        $rtId = $user->rt_id ?? ($user->resident?->rt_id ?? ($user->resident?->family?->rt_id ?? null));
        $rwId = $user->rw_id ?? ($user->resident?->rt?->rw_id ?? ($user->resident?->family?->rt?->rw_id ?? null));
        $villageId = $user->village_id ?? ($user->resident?->rt?->rw?->village_id ?? ($user->resident?->family?->rt?->rw?->village_id ?? null));

        if ($user->hasRole('WARGA')) {
            $query->where('user_id', $user->id);
        } elseif ($user->hasRole('RT')) {
            if (! $rtId) {
                $query->whereRaw('1 = 0');
            } else {
                $query->where(function ($q) use ($rtId) {
                    $q->where('rt_id', $rtId)
                        ->orWhereHas('user', fn ($u) => $u->where('rt_id', $rtId)->orWhereHas('resident', fn ($r) => $r->where('rt_id', $rtId)));
                });
            }
        } elseif ($user->hasRole('RW')) {
            if (! $rwId) {
                $query->whereRaw('1 = 0');
            } else {
                $query->where(function ($q) use ($rwId) {
                    $q->where('rw_id', $rwId)
                        ->orWhereHas('rt', fn ($r) => $r->where('rw_id', $rwId));
                });
            }
        } elseif ($user->hasRole('ADMIN_DESA')) {
            if (! $villageId) {
                $query->whereRaw('1 = 0');
            } else {
                $query->where(function ($q) use ($villageId) {
                    $q->where('village_id', $villageId)
                        ->orWhereHas('rw', fn ($rw) => $rw->where('village_id', $villageId));
                });
            }
        }

        $complaints = $query->paginate(10);

        return view('dashboard.complaints.index', compact('complaints'));
    }

    public function create()
    {
        return view('dashboard.complaints.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'nullable|string|max:255',
            'photo' => 'nullable|image|max:2048',
        ]);

        $user = auth()->user();

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('complaints', 'public');
        }

        $rtId = $user->rt_id ?? ($user->resident?->rt_id ?? ($user->resident?->family?->rt_id ?? null));
        $rwId = $user->rw_id ?? ($user->resident?->rt?->rw_id ?? ($user->resident?->family?->rt?->rw_id ?? null));
        $villageId = $user->village_id ?? ($user->resident?->rt?->rw?->village_id ?? ($user->resident?->family?->rt?->rw?->village_id ?? null));

        $complaint = Complaint::create([
            'user_id' => $user->id,
            'title' => $request->title,
            'description' => $request->description,
            'location' => $request->location,
            'photo_path' => $photoPath,
            'status' => 'PENDING',
            'rt_id' => $rtId,
            'rw_id' => $rwId,
            'village_id' => $villageId,
        ]);

        // Notifikasi Telegram, WhatsApp & Email saat pengaduan baru diajukan
        $this->telegramService->sendComplaintCreatedNotification($complaint, $user);
        $this->whatsAppService->sendComplaintCreatedNotification($complaint, $user);
        $this->emailNotificationService->sendComplaintCreatedNotification($complaint, $user);

        if ($request->header('referer') && str_contains($request->header('referer'), 'dashboard') && ! str_contains($request->header('referer'), 'complaints/create')) {
            return redirect()->route('dashboard')->with('success', 'Pengaduan berhasil dikirim.');
        }

        return redirect()->route('complaints.index')->with('success', 'Pengaduan berhasil dikirim.');
    }

    public function show(Complaint $complaint)
    {
        $this->authorizeComplaintAccess(auth()->user(), $complaint);

        return view('dashboard.complaints.show', compact('complaint'));
    }

    public function updateStatus(Request $request, Complaint $complaint)
    {
        $this->authorizeComplaintAccess(auth()->user(), $complaint);

        $request->validate([
            'status' => 'required|in:PENDING,PROCESSING,RESOLVED,REJECTED',
        ]);

        $complaint->update(['status' => $request->status]);

        // Kirim notifikasi ke pelapor (warga)
        if ($complaint->user_id) {
            $userActor = auth()->user();
            $actorTitle = match ($userActor->role) {
                'RT' => 'Ketua RT',
                'RW' => 'Ketua RW',
                'ADMIN_DESA' => 'Pemerintah Desa',
                default => 'Petugas Wilayah'
            };

            $notifTitle = match ($request->status) {
                'PROCESSING' => 'Pengaduan Sedang Diproses',
                'RESOLVED' => 'Pengaduan Telah Selesai',
                'REJECTED' => 'Pengaduan Ditolak',
                default => 'Status Pengaduan Diperbarui'
            };

            $notifMessage = match ($request->status) {
                'PROCESSING' => "Pengaduan Anda '{$complaint->title}' sedang ditindaklanjuti dan diproses oleh {$actorTitle}.",
                'RESOLVED' => "Pengaduan Anda '{$complaint->title}' telah selesai ditangani oleh {$actorTitle}. Terima kasih atas kepedulian Anda.",
                'REJECTED' => "Pengaduan Anda '{$complaint->title}' belum dapat ditindaklanjuti/ditolak oleh {$actorTitle}.",
                default => "Status pengaduan Anda '{$complaint->title}' telah diubah menjadi {$request->status} oleh {$actorTitle}."
            };

            AppNotification::create([
                'user_id' => $complaint->user_id,
                'title' => $notifTitle,
                'message' => $notifMessage,
                'type' => 'PENGADUAN',
                'reference_id' => $complaint->id,
                'url' => route('complaints.show', $complaint->id),
            ]);

            // Kirim notifikasi Telegram langsung ke pelapor
            $this->telegramService->sendComplaintStatusNotification(
                $complaint,
                $request->status,
                $userActor
            );

            // Kirim notifikasi WhatsApp langsung ke pelapor
            $this->whatsAppService->sendComplaintStatusNotification(
                $complaint,
                $request->status,
                $userActor
            );

            // Kirim notifikasi Email langsung ke pelapor
            $this->emailNotificationService->sendComplaintStatusNotification(
                $complaint,
                $request->status,
                $userActor
            );
        }

        return back()->with('success', 'Status pengaduan berhasil diperbarui.');
    }

    protected function authorizeComplaintAccess(User $user, Complaint $complaint): void
    {
        if (in_array($user->role, ['SUPER_ADMIN'])) {
            return;
        }

        $rtId = $user->rt_id ?? ($user->resident?->rt_id ?? ($user->resident?->family?->rt_id ?? null));
        $rwId = $user->rw_id ?? ($user->resident?->rt?->rw_id ?? ($user->resident?->family?->rt?->rw_id ?? null));
        $villageId = $user->village_id ?? ($user->resident?->rt?->rw?->village_id ?? ($user->resident?->family?->rt?->rw?->village_id ?? null));

        if ($user->role === 'WARGA') {
            if ($complaint->user_id !== $user->id) {
                abort(403, 'Anda hanya dapat melihat pengaduan milik Anda sendiri.');
            }
        } elseif ($user->role === 'RT') {
            $complaintRtId = $complaint->rt_id ?? ($complaint->user?->rt_id ?? ($complaint->user?->resident?->rt_id ?? null));
            if (! $rtId || $complaintRtId !== $rtId) {
                abort(403, 'Anda hanya dapat mengakses pengaduan di wilayah RT Anda.');
            }
        } elseif ($user->role === 'RW') {
            $complaintRwId = $complaint->rw_id ?? ($complaint->rt?->rw_id ?? ($complaint->user?->resident?->rt?->rw_id ?? null));
            if (! $rwId || $complaintRwId !== $rwId) {
                abort(403, 'Anda hanya dapat mengakses pengaduan di wilayah RW Anda.');
            }
        } elseif ($user->role === 'ADMIN_DESA') {
            $complaintVillageId = $complaint->village_id ?? ($complaint->rw?->village_id ?? ($complaint->rt?->rw?->village_id ?? null));
            if (! $villageId || $complaintVillageId !== $villageId) {
                abort(403, 'Anda hanya dapat mengakses pengaduan di wilayah desa Anda.');
            }
        }
    }
}
