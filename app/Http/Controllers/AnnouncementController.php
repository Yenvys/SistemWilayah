<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AppNotification;
use App\Models\Poll;
use App\Models\User;
use App\Services\EmailNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AnnouncementController extends Controller
{
    protected EmailNotificationService $emailNotificationService;

    public function __construct(EmailNotificationService $emailNotificationService)
    {
        $this->emailNotificationService = $emailNotificationService;
    }

    public function index()
    {
        $isDesa = in_array(auth()->user()->role, ['SUPER_ADMIN', 'ADMIN_DESA']);

        $announcements = Announcement::with('author')
            ->when(! $isDesa, function ($query) {
                $query->where('is_active', true);
            })
            ->latest('published_at')
            ->latest('created_at')
            ->paginate(12);

        $completedPolls = Poll::accessibleBy(auth()->user())
            ->where('status', 'CLOSED')
            ->with(['options.votes', 'creator', 'rt', 'rw'])
            ->latest('updated_at')
            ->take(6)
            ->get();

        return view('dashboard.announcements.index', compact('announcements', 'isDesa', 'completedPolls'));
    }

    public function show(Announcement $announcement)
    {
        $isDesa = in_array(auth()->user()->role, ['SUPER_ADMIN', 'ADMIN_DESA']);

        if (! $isDesa && ! $announcement->is_active) {
            abort(404, 'Pengumuman tidak ditemukan atau belum dipublikasikan.');
        }

        $announcement->loadMissing('author');

        return view('dashboard.announcements.show', compact('announcement', 'isDesa'));
    }

    public function manage()
    {
        return redirect()->route('announcements.index');
    }

    public function create()
    {
        return view('dashboard.announcements.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'is_active' => 'nullable',
        ]);

        $isActive = $request->boolean('is_active');

        $announcement = Announcement::create([
            'title' => $request->title,
            'content' => $request->content,
            'author_id' => auth()->id(),
            'is_active' => $isActive,
            'published_at' => $isActive ? now() : null,
        ]);

        if ($announcement->is_active) {
            $this->emailNotificationService->sendAnnouncementNotification($announcement);
            $this->broadcastAppNotification($announcement);
        }

        return redirect()->route('announcements.index')->with('success', 'Pengumuman desa berhasil '.($isActive ? 'diterbitkan dan disiarkan ke semua warga.' : 'disimpan sebagai draf.'));
    }

    public function edit(Announcement $announcement)
    {
        return view('dashboard.announcements.form', compact('announcement'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'is_active' => 'nullable',
        ]);

        $wasActive = $announcement->is_active;
        $isActive = $request->boolean('is_active');

        $announcement->update([
            'title' => $request->title,
            'content' => $request->content,
            'is_active' => $isActive,
            'published_at' => (! $wasActive && $isActive) ? now() : ($announcement->published_at ?? ($isActive ? now() : null)),
        ]);

        // Jika baru diaktifkan atau dipublikasikan ulang
        if (! $wasActive && $announcement->is_active) {
            $this->emailNotificationService->sendAnnouncementNotification($announcement);
            $this->broadcastAppNotification($announcement);
        }

        return redirect()->route('announcements.index')->with('success', 'Pengumuman desa berhasil diperbarui.');
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return redirect()->route('announcements.index')->with('success', 'Pengumuman desa berhasil dihapus.');
    }

    protected function broadcastAppNotification(Announcement $announcement): void
    {
        try {
            $users = User::where('id', '!=', auth()->id())->get(['id']);
            $now = now();
            $batch = [];
            foreach ($users as $u) {
                $batch[] = [
                    'id' => (string) Str::uuid(),
                    'user_id' => $u->id,
                    'type' => 'PENGUMUMAN',
                    'title' => 'Pengumuman Desa: '.Str::limit($announcement->title, 40),
                    'message' => Str::limit(strip_tags($announcement->content), 120),
                    'url' => route('announcements.show', $announcement->id),
                    'is_read' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            if (! empty($batch)) {
                foreach (array_chunk($batch, 100) as $chunk) {
                    AppNotification::insert($chunk);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal menyiarkan in-app notification pengumuman: '.$e->getMessage());
        }
    }
}
