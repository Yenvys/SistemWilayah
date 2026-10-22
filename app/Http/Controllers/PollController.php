<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AppNotification;
use App\Models\Poll;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\Village;
use App\Services\EmailNotificationService;
use App\Services\TelegramService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;

class PollController extends Controller
{
    protected WhatsAppService $whatsAppService;

    protected EmailNotificationService $emailNotificationService;

    protected TelegramService $telegramService;

    public function __construct(
        WhatsAppService $whatsAppService,
        EmailNotificationService $emailNotificationService,
        TelegramService $telegramService
    ) {
        $this->whatsAppService = $whatsAppService;
        $this->emailNotificationService = $emailNotificationService;
        $this->telegramService = $telegramService;
    }

    public function index()
    {
        $user = auth()->user();
        $polls = Poll::accessibleBy($user)
            ->with(['creator', 'options.votes', 'rt.rw', 'rw', 'village'])
            ->withCount('votes')
            ->latest()
            ->paginate(10);

        return view('dashboard.polls.index', compact('polls'));
    }

    public function create()
    {
        $user = auth()->user();
        if ($user->role === 'WARGA') {
            abort(403, 'Warga tidak memiliki akses untuk membuat polling.');
        }

        $villages = Village::orderBy('name')->get();
        $rwsQuery = Rw::with('village')->orderBy('number');
        $rtsQuery = Rt::with('rw.village')->orderBy('number');

        if ($user->role === 'ADMIN_DESA' && $user->village_id) {
            $rwsQuery->where('village_id', $user->village_id);
            $rtsQuery->whereHas('rw', fn ($q) => $q->where('village_id', $user->village_id));
        } elseif ($user->role === 'RW') {
            $rwId = $user->rw_id ?? ($user->resident?->rw_id ?? ($user->resident?->rt?->rw_id ?? null));
            if ($rwId) {
                $rwsQuery->where('id', $rwId);
                $rtsQuery->where('rw_id', $rwId);
            }
        } elseif ($user->role === 'RT') {
            $rtId = $user->rt_id ?? ($user->resident?->rt_id ?? ($user->resident?->family?->rt_id ?? null));
            if ($rtId) {
                $rtsQuery->where('id', $rtId);
            }
        }

        $rws = $rwsQuery->get();
        $rts = $rtsQuery->get();

        return view('dashboard.polls.create', compact('villages', 'rws', 'rts'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if ($user->role === 'WARGA') {
            abort(403, 'Warga tidak memiliki hak untuk membuat musyawarah / polling.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'options' => 'required|array|min:2',
            'options.*' => 'required|string|max:255',
            'expires_at' => 'nullable|date|after:today',
            'level' => 'nullable|in:DESA,RW,RT',
            'village_id' => 'nullable|exists:villages,id',
            'rw_id' => 'nullable|exists:rws,id',
            'rt_id' => 'nullable|exists:rts,id',
        ]);

        // Tentukan cakupan wilayah polling berdasarkan peran pengurus
        $level = 'DESA';
        $villageId = $user->village_id ?? ($user->resident?->village_id ?? ($user->resident?->rt?->rw?->village_id ?? null));
        $rwId = $user->rw_id ?? ($user->resident?->rw_id ?? ($user->resident?->rt?->rw_id ?? null));
        $rtId = $user->rt_id ?? ($user->resident?->rt_id ?? ($user->resident?->family?->rt_id ?? null));

        if ($user->role === 'RT') {
            $level = 'RT';
            // Wilayah otomatis terkunci ke RT bersangkutan
        } elseif ($user->role === 'RW') {
            $level = $request->level === 'RT' ? 'RT' : 'RW';
            $rtId = $level === 'RT' ? $request->rt_id : null;
        } elseif ($user->role === 'ADMIN_DESA') {
            $level = in_array($request->level, ['RT', 'RW', 'DESA']) ? $request->level : 'DESA';
            $rwId = in_array($level, ['RW', 'RT']) ? $request->rw_id : null;
            $rtId = $level === 'RT' ? $request->rt_id : null;
        } elseif ($user->role === 'SUPER_ADMIN') {
            $level = $request->level ?? 'DESA';
            $villageId = $request->village_id ?? $villageId;
            $rwId = in_array($level, ['RW', 'RT']) ? $request->rw_id : null;
            $rtId = $level === 'RT' ? $request->rt_id : null;
        }

        $poll = Poll::create([
            'title' => $request->title,
            'description' => $request->description,
            'created_by' => $user->id,
            'status' => 'ACTIVE',
            'level' => $level,
            'village_id' => $villageId,
            'rw_id' => $rwId,
            'rt_id' => $rtId,
            'expires_at' => $request->expires_at,
        ]);

        foreach ($request->options as $optionText) {
            if (trim($optionText) !== '') {
                $poll->options()->create(['text' => trim($optionText)]);
            }
        }

        // Kirim Notifikasi ke Seluruh Warga di Wilayah Terkait
        $creatorTitle = match ($user->role) {
            'RT' => 'Ketua RT '.($poll->rt?->number ?? ''),
            'RW' => 'Ketua RW '.($poll->rw?->number ?? ''),
            'ADMIN_DESA' => 'Pemerintah Desa',
            default => 'Pengurus Wilayah'
        };

        $targetCitizens = $poll->getTargetCitizenUsers();
        foreach ($targetCitizens as $citizen) {
            AppNotification::create([
                'user_id' => $citizen->id,
                'title' => 'Musyawarah / Polling Baru: '.$poll->title,
                'message' => "Ada musyawarah / polling baru '{$poll->title}' yang diselenggarakan oleh {$creatorTitle}. Silakan berikan suara Anda.",
                'type' => 'POLL',
                'reference_id' => $poll->id,
                'url' => route('polls.show', $poll->id),
            ]);
        }

        // Siarkan notifikasi polling ke WhatsApp, Email, & Telegram warga di wilayah bersangkutan
        $this->whatsAppService->sendPollCreatedNotification($poll);
        $this->emailNotificationService->sendPollCreatedNotification($poll);
        $this->telegramService->sendPollCreatedNotification($poll);

        if ($request->header('referer') && str_contains($request->header('referer'), 'dashboard') && ! str_contains($request->header('referer'), 'polls/create')) {
            return redirect()->route('dashboard')->with('success', 'Musyawarah / Polling berhasil dibuat dan notifikasi telah disiarkan ke warga.');
        }

        return redirect()->route('polls.index')->with('success', 'Musyawarah / Polling berhasil dibuat dan notifikasi telah disiarkan ke warga.');
    }

    public function show(Poll $poll)
    {
        $poll->load(['options.votes', 'creator', 'rt', 'rw', 'village']);
        $userVoted = $poll->votes()->where('user_id', auth()->id())->first();
        $totalVotes = $poll->votes()->count();

        return view('dashboard.polls.show', compact('poll', 'userVoted', 'totalVotes'));
    }

    public function vote(Request $request, Poll $poll)
    {
        $request->validate([
            'option_id' => 'required|exists:poll_options,id',
        ]);

        if ($poll->status !== 'ACTIVE' || ($poll->expires_at && $poll->expires_at->isPast())) {
            return redirect()->route('polls.show', $poll->id)->with('error', 'Musyawarah ini telah selesai.');
        }

        if ($poll->votes()->where('user_id', auth()->id())->exists()) {
            return redirect()->route('polls.show', $poll->id)->with('error', 'Anda sudah memberikan suara pada musyawarah ini.');
        }

        // Pastikan opsi yang dipilih memang milik polling ini
        $option = $poll->options()->where('id', $request->option_id)->first();
        if (! $option) {
            return redirect()->route('polls.show', $poll->id)->with('error', 'Pilihan tidak valid untuk polling ini.');
        }

        $poll->votes()->create([
            'poll_option_id' => $request->option_id,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('polls.show', $poll->id)->with('success', 'Terima kasih atas partisipasi suara Anda!');
    }

    public function close(Poll $poll)
    {
        $user = auth()->user();
        if ($poll->created_by !== $user->id && ! in_array($user->role, ['SUPER_ADMIN', 'ADMIN_DESA'])) {
            abort(403, 'Anda tidak memiliki hak untuk menutup musyawarah ini.');
        }

        if ($poll->status === 'CLOSED') {
            return redirect()->route('polls.show', $poll->id)->with('info', 'Polling ini sudah diselesaikan sebelumnya.');
        }

        $poll->update(['status' => 'CLOSED']);
        $poll->load(['options.votes']);

        $winningOption = $poll->winning_option;
        $totalVotes = $poll->votes()->count();
        $winVotes = $winningOption ? $winningOption->votes->count() : 0;
        $winPercent = $totalVotes > 0 ? round(($winVotes / $totalVotes) * 100) : 0;

        $resultMessage = ($winningOption && $winVotes > 0)
            ? "Musyawarah '{$poll->title}' telah selesai dengan hasil suara terbanyak: '{$winningOption->text}' ({$winVotes} suara / {$winPercent}%)."
            : "Musyawarah '{$poll->title}' telah resmi ditutup dan diselesaikan.";

        // Otomatis terbitkan hasil musyawarah ke Pengumuman & Informasi Desa
        $voteDetails = $poll->options->map(function ($opt) use ($totalVotes) {
            $vCount = $opt->votes->count();
            $pct = $totalVotes > 0 ? round(($vCount / $totalVotes) * 100) : 0;

            return "• {$opt->text}: {$vCount} suara ({$pct}%)";
        })->implode("\n");

        Announcement::create([
            'title' => 'Hasil Musyawarah: '.$poll->title,
            'content' => $resultMessage."\n\nTotal Partisipasi: {$totalVotes} warga berpartisipasi.\n\nRincian Perolehan Suara:\n".$voteDetails,
            'author_id' => $user->id,
            'is_active' => true,
            'published_at' => now(),
        ]);

        // Siarkan Notifikasi Hasil Polling ke Seluruh Warga di Target Wilayah
        $targetCitizens = $poll->getTargetCitizenUsers();
        foreach ($targetCitizens as $citizen) {
            AppNotification::create([
                'user_id' => $citizen->id,
                'title' => 'Hasil Musyawarah: '.$poll->title,
                'message' => $resultMessage,
                'type' => 'POLL',
                'reference_id' => $poll->id,
                'url' => route('polls.show', $poll->id),
            ]);
        }

        // Siarkan notifikasi hasil musyawarah ke WhatsApp, Email, & Telegram warga
        $this->whatsAppService->sendPollClosedNotification($poll, $resultMessage);
        $this->emailNotificationService->sendPollClosedNotification($poll, $resultMessage);
        $this->telegramService->sendPollClosedNotification($poll, $resultMessage);

        return redirect()->route('polls.show', $poll->id)->with('success', 'Musyawarah / Polling berhasil diselesaikan. Hasil telah dipublikasikan ke Informasi Desa dan disiarkan ke warga.');
    }
}
