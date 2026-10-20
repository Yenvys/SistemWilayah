<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Complaint;
use App\Models\DocumentRequest;
use App\Models\Emergency;
use App\Models\Family;
use App\Models\Poll;
use App\Models\Resident;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\User;
use App\Models\Village;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->hasRole('WARGA')) {
            $resident = Resident::with(['rt.rw.village'])->where('nik', $user->username)->first() ?? $user->resident;

            // 1. Pengajuan Surat Mandiri
            $documentRequests = DocumentRequest::accessibleBy($user)
                ->with(['resident'])
                ->latest()
                ->take(6)
                ->get();

            $totalDocuments = DocumentRequest::accessibleBy($user)->count();
            $pendingDocuments = DocumentRequest::accessibleBy($user)
                ->whereIn('status', ['PENDING', 'DIAJUKAN_RT', 'DIAJUKAN_RW', 'DIAJUKAN_DESA'])
                ->count();
            $completedDocuments = DocumentRequest::accessibleBy($user)
                ->whereIn('status', ['SELESAI', 'DISETUJUI', 'COMPLETED'])
                ->count();

            // 2. Laporan & Pengaduan
            $myComplaints = Complaint::where('user_id', $user->id)
                ->latest()
                ->take(6)
                ->get();

            $totalComplaints = Complaint::where('user_id', $user->id)->count();
            $processingComplaints = Complaint::where('user_id', $user->id)
                ->whereIn('status', ['PENDING', 'PROCESSING', 'DIPROSES'])
                ->count();
            $resolvedComplaints = Complaint::where('user_id', $user->id)
                ->whereIn('status', ['RESOLVED', 'SELESAI'])
                ->count();

            // 3. Musyawarah & Polling Warga
            $polls = Poll::accessibleBy($user)
                ->with(['options.votes', 'creator', 'votes'])
                ->latest()
                ->take(3)
                ->get();

            $activePollsCount = Poll::accessibleBy($user)
                ->where('status', 'ACTIVE')
                ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
                ->count();

            $completedPolls = Poll::accessibleBy($user)
                ->where('status', 'CLOSED')
                ->with(['options.votes', 'creator', 'rt', 'rw'])
                ->latest('updated_at')
                ->take(3)
                ->get();

            // 4. Informasi Publik & Pengumuman
            $announcements = Announcement::where('is_active', true)
                ->latest('published_at')
                ->take(4)
                ->get();

            $hasActiveEmergency = Emergency::where('user_id', $user->id)->where('status', 'ACTIVE')->exists();

            return view('dashboard.warga', compact(
                'user',
                'resident',
                'documentRequests',
                'totalDocuments',
                'pendingDocuments',
                'completedDocuments',
                'myComplaints',
                'totalComplaints',
                'processingComplaints',
                'resolvedComplaints',
                'polls',
                'activePollsCount',
                'completedPolls',
                'announcements',
                'hasActiveEmergency'
            ));
        }

        if ($user->hasRole('RT') || $user->hasRole('RW')) {
            $resident = $user->resident;
            $complaintsQuery = Complaint::with(['user', 'rt', 'rw'])->latest();

            if ($user->hasRole('RT')) {
                $rtId = $user->rt_id ?? ($resident->rt_id ?? ($resident->family->rt_id ?? null));
                if ($rtId) {
                    $complaintsQuery->where(function ($q) use ($rtId) {
                        $q->where('rt_id', $rtId)
                            ->orWhereHas('user', fn ($u) => $u->where('rt_id', $rtId)->orWhereHas('resident', fn ($r) => $r->where('rt_id', $rtId)));
                    });
                    $residentsQuery = Resident::accessibleBy($user);
                    $familiesCount = Family::where('rt_id', $rtId)->count();
                } else {
                    $complaintsQuery->whereRaw('1 = 0');
                    $residentsQuery = Resident::whereRaw('1 = 0');
                    $familiesCount = 0;
                }
                $residentsCount = (clone $residentsQuery)->count();
                $maleCount = (clone $residentsQuery)->where('gender', 'LAKI_LAKI')->count();
                $femaleCount = (clone $residentsQuery)->where('gender', 'PEREMPUAN')->count();
            } else {
                $rwId = $user->rw_id ?? ($resident->rw_id ?? ($resident->rt->rw_id ?? ($resident->family->rt->rw_id ?? null)));
                if ($rwId) {
                    $complaintsQuery->where('rw_id', $rwId);
                    $residentsQuery = Resident::accessibleBy($user);
                    $familiesCount = Family::whereHas('rt', fn ($q) => $q->where('rw_id', $rwId))->count();
                } else {
                    $complaintsQuery->whereRaw('1 = 0');
                    $residentsQuery = Resident::whereRaw('1 = 0');
                    $familiesCount = 0;
                }
                $residentsCount = (clone $residentsQuery)->count();
                $maleCount = (clone $residentsQuery)->where('gender', 'LAKI_LAKI')->count();
                $femaleCount = (clone $residentsQuery)->where('gender', 'PEREMPUAN')->count();
            }

            // 1. Dokumen / Layanan Pengajuan Surat Warga
            $documentsQuery = DocumentRequest::with(['resident.rt.rw.village', 'resident.family.rt.rw.village'])
                ->accessibleBy($user)
                ->latest();
            $recentDocuments = (clone $documentsQuery)->take(6)->get();
            $totalDocuments = (clone $documentsQuery)->count();
            $pendingDocuments = (clone $documentsQuery)->whereIn('status', ['DIAJUKAN_RT', 'PENDING', 'DIAJUKAN_RW'])->count();

            // 2. Pengaduan Warga
            $recentComplaints = (clone $complaintsQuery)->take(6)->get();
            $totalComplaints = (clone $complaintsQuery)->count();
            $pendingComplaints = (clone $complaintsQuery)->whereIn('status', ['PENDING', 'PROCESSING'])->count();

            // 3. Polling / Musyawarah
            $polls = Poll::accessibleBy($user)
                ->with(['creator', 'options.votes', 'votes'])
                ->latest()
                ->take(4)
                ->get();
            $activePollsCount = Poll::accessibleBy($user)
                ->where('status', 'ACTIVE')
                ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
                ->count();
            $completedPolls = Poll::accessibleBy($user)
                ->where('status', 'CLOSED')
                ->with(['options.votes', 'creator', 'rt', 'rw'])
                ->latest('updated_at')
                ->take(3)
                ->get();

            $stats = [
                'residents' => $residentsCount,
                'male' => $maleCount,
                'female' => $femaleCount,
                'families' => $familiesCount,
                'complaints' => $totalComplaints,
                'pending_complaints' => $pendingComplaints,
                'documents' => $totalDocuments,
                'pending_documents' => $pendingDocuments,
                'active_polls' => $activePollsCount,
            ];

            $activeEmergencies = Emergency::with('user')->where('status', 'ACTIVE')->get();

            return view('dashboard.rt_rw', compact('user', 'stats', 'recentComplaints', 'recentDocuments', 'polls', 'completedPolls', 'activeEmergencies'));
        }

        // SuperAdmin / Admin Desa
        $villageScope = Village::query();
        if ($user->role === 'ADMIN_DESA' && $user->village_id) {
            $villageScope->where('id', $user->village_id);
        }

        $territoryTree = (clone $villageScope)->with([
            'rws' => fn ($rwQ) => $rwQ->orderBy('number'),
            'rws.rts' => fn ($rtQ) => $rtQ->orderBy('number')->withCount(['residents', 'families']),
        ])->get();

        $stats = [
            'villages' => (clone $villageScope)->count(),
            'rws' => Rw::when($user->role === 'ADMIN_DESA' && $user->village_id, fn ($q) => $q->where('village_id', $user->village_id))->count(),
            'rts' => Rt::when($user->role === 'ADMIN_DESA' && $user->village_id, fn ($q) => $q->whereHas('rw', fn ($rw) => $rw->where('village_id', $user->village_id)))->count(),
            'residents' => Resident::accessibleBy($user)->count(),
            'male' => Resident::accessibleBy($user)->where('gender', 'LAKI_LAKI')->count(),
            'female' => Resident::accessibleBy($user)->where('gender', 'PEREMPUAN')->count(),
            'families' => Resident::accessibleBy($user)->where('is_head_of_family', true)->count(),
            'users' => User::count(),
            'complaints' => Complaint::count(),
            'active_polls' => Poll::accessibleBy($user)->where('status', 'ACTIVE')->count(),
            'closed_polls' => Poll::accessibleBy($user)->where('status', 'CLOSED')->count(),
        ];

        $completedPolls = Poll::accessibleBy($user)
            ->where('status', 'CLOSED')
            ->with(['options.votes', 'creator', 'rt', 'rw', 'village'])
            ->latest('updated_at')
            ->take(4)
            ->get();
        $recentAnnouncements = Announcement::latest('published_at')->take(4)->get();

        $recentResidents = Resident::accessibleBy($user)->latest()->take(5)->get();
        $activeEmergencies = Emergency::with('user')->where('status', 'ACTIVE')->get();

        return view('dashboard.index', compact('stats', 'recentResidents', 'activeEmergencies', 'territoryTree', 'completedPolls', 'recentAnnouncements'));
    }
}
