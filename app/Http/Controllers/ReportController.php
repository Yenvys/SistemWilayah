<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\DocumentRequest;
use App\Models\Resident;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\User;
use App\Models\Village;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $rtId = $user->rt_id ?? ($user->resident?->rt_id ?? ($user->resident?->family?->rt_id ?? null));

        // ================= 1. LAPORAN & STATISTIK =================
        if ($user->role === 'RT') {
            $totalUsers = User::where('role', 'WARGA')
                ->where(function ($q) use ($rtId) {
                    $q->where('rt_id', $rtId)
                        ->orWhereHas('resident', fn ($r) => $r->where('rt_id', $rtId));
                })->count();

            $stats = [
                'total_residents' => Resident::accessibleBy($user)->count(),
                'total_villages' => 1,
                'total_rws' => 1,
                'total_rts' => 1,
                'total_users' => $totalUsers,
                'total_documents' => DocumentRequest::accessibleBy($user)->count(),
            ];
        } else {
            $stats = [
                'total_residents' => Resident::accessibleBy($user)->count(),
                'total_villages' => Village::count(),
                'total_rws' => Rw::count(),
                'total_rts' => Rt::count(),
                'total_users' => User::count(),
                'total_documents' => DocumentRequest::accessibleBy($user)->count(),
            ];
        }

        // Gender distribution
        $genderStats = Resident::accessibleBy($user)
            ->select('gender', DB::raw('count(*) as total'))
            ->groupBy('gender')
            ->pluck('total', 'gender')
            ->toArray();

        // Status distribution
        $statusStats = Resident::accessibleBy($user)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Document status distribution (clean categorized counts)
        $rawDocStats = DocumentRequest::accessibleBy($user)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $documentStats = [
            'PENDING' => ($rawDocStats['DIAJUKAN_RT'] ?? 0) + ($rawDocStats['PENDING'] ?? 0) + ($rawDocStats['DRAFT'] ?? 0),
            'PROCESSED' => ($rawDocStats['DIAJUKAN_RW'] ?? 0) + ($rawDocStats['DIAJUKAN_DESA'] ?? 0) + ($rawDocStats['DIPROSES'] ?? 0) + ($rawDocStats['PROCESSED'] ?? 0),
            'APPROVED' => ($rawDocStats['DISETUJUI'] ?? 0) + ($rawDocStats['APPROVED'] ?? 0),
            'DONE' => ($rawDocStats['SELESAI'] ?? 0) + ($rawDocStats['DONE'] ?? 0),
            'REJECTED' => ($rawDocStats['DITOLAK'] ?? 0) + ($rawDocStats['REJECTED'] ?? 0),
        ];

        // Recent documents
        $recentDocuments = DocumentRequest::accessibleBy($user)
            ->with('resident')
            ->latest()
            ->take(5)
            ->get();

        // Users per role
        $usersPerRoleQuery = User::query();
        if ($user->role === 'RT') {
            $usersPerRoleQuery->where('role', 'WARGA')
                ->where(function ($q) use ($rtId) {
                    $q->where('rt_id', $rtId)
                        ->orWhereHas('resident', fn ($r) => $r->where('rt_id', $rtId));
                });
        }
        $usersPerRole = $usersPerRoleQuery->select('role', DB::raw('count(*) as total'))
            ->groupBy('role')
            ->pluck('total', 'role')
            ->toArray();

        // ================= 2. AUDIT LOGS =================
        $auditQuery = AuditLog::with('user')->latest();

        if (in_array($user->role, ['RW', 'RT'])) {
            $auditQuery->where('user_id', $user->id);
        }

        if ($request->filled('search_log')) {
            $search = $request->search_log;
            $auditQuery->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                    ->orWhere('entity_name', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('username', 'like', "%{$search}%")->orWhere('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('action_filter')) {
            $auditQuery->where('action', $request->action_filter);
        }

        if ($request->filled('entity_filter')) {
            $auditQuery->where('entity_name', $request->entity_filter);
        }

        $logs = $auditQuery->paginate(10, ['*'], 'audit_page')->withQueryString();
        $actions = AuditLog::select('action')->distinct()->pluck('action');
        $entities = AuditLog::select('entity_name')->distinct()->pluck('entity_name');

        return view('dashboard.reports.index', compact(
            'stats',
            'genderStats',
            'statusStats',
            'documentStats',
            'recentDocuments',
            'usersPerRole',
            'logs',
            'actions',
            'entities'
        ));
    }
}
