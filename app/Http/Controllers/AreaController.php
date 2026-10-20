<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Resident;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\Village;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AreaController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Hak akses wilayah per role: RT hanya berwenang atas warganya sendiri, bukan RW atau Desa
        $canAccessDesa = in_array($user->role, ['SUPER_ADMIN', 'ADMIN_DESA']);
        $canAccessRwRt = in_array($user->role, ['SUPER_ADMIN', 'ADMIN_DESA', 'RW']);
        $canAccessPenduduk = in_array($user->role, ['SUPER_ADMIN', 'ADMIN_DESA', 'RW', 'RT']);

        // ================= 1. DATA DESA =================
        $villageQuery = Village::withCount(['rws', 'users']);
        if ($user->role === 'ADMIN_DESA' && $user->village_id) {
            $villageQuery->where('id', $user->village_id);
        }
        if ($request->filled('search_village')) {
            $search = $request->search_village;
            $villageQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }
        $villages = $villageQuery->latest()->paginate(5, ['*'], 'desa_page')->withQueryString();
        $allVillages = Village::orderBy('name')->get();

        // ================= 2. DATA RW & RT =================
        $rwQuery = Rw::with(['village'])->withCount(['rts']);
        $rtQuery = Rt::with(['rw.village']);

        if ($user->role === 'ADMIN_DESA' && $user->village_id) {
            $rwQuery->where('village_id', $user->village_id);
            $rtQuery->whereHas('rw', fn ($q) => $q->where('village_id', $user->village_id));
        } elseif ($user->role === 'RW') {
            $rwId = $user->rw_id ?? ($user->resident->rt->rw_id ?? null);
            if ($rwId) {
                $rwQuery->where('id', $rwId);
                $rtQuery->where('rw_id', $rwId);
            }
        } elseif ($user->role === 'RT') {
            $rtId = $user->rt_id ?? ($user->resident?->rt_id ?? ($user->resident?->family?->rt_id ?? null));
            if ($rtId) {
                $rwQuery->whereHas('rts', fn ($q) => $q->where('id', $rtId));
                $rtQuery->where('id', $rtId);
            } else {
                $rwQuery->whereRaw('1 = 0');
                $rtQuery->whereRaw('1 = 0');
            }
        }

        if ($request->filled('search_area')) {
            $search = $request->search_area;
            $rwQuery->where('number', 'like', "%{$search}%");
            $rtQuery->where('number', 'like', "%{$search}%");
        }

        if ($request->filled('village_filter')) {
            $rwQuery->where('village_id', $request->village_filter);
            $rtQuery->whereHas('rw', fn ($q) => $q->where('village_id', $request->village_filter));
        }

        $rws = $rwQuery->orderBy('number')->paginate(5, ['*'], 'rw_page')->withQueryString();
        $rts = $rtQuery->orderBy('number')->paginate(5, ['*'], 'rt_page')->withQueryString();

        // Objek RT / RW spesifik untuk user RT / RW
        $rtObj = null;
        if ($user->role === 'RT') {
            $rtId = $user->rt_id ?? ($user->resident?->rt_id ?? ($user->resident?->family?->rt_id ?? null));
            $rtObj = $rtId ? Rt::with('rw.village')->find($rtId) : null;
        }

        $rwObj = null;
        if ($user->role === 'RW') {
            $rwId = $user->rw_id ?? ($user->resident?->rw_id ?? ($user->resident?->rt?->rw_id ?? null));
            $rwObj = $rwId ? Rw::with('village')->find($rwId) : null;
        }

        // ================= 3. DATA KEPENDUDUKAN =================
        $residentQuery = Resident::with(['family.rt.rw.village', 'rt.rw.village', 'user'])
            ->accessibleBy($user);

        if ($request->filled('search_resident')) {
            $search = $request->search_resident;
            $residentQuery->where(function ($q) use ($search) {
                $q->where('nik', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('username', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone_number', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('rw_filter')) {
            $rwFilter = $request->rw_filter;
            $residentQuery->where(function ($q) use ($rwFilter) {
                $q->whereHas('rt', fn ($rtq) => $rtq->where('rw_id', $rwFilter))
                  ->orWhereHas('family.rt', fn ($rtq) => $rtq->where('rw_id', $rwFilter));
            });
        }

        if ($request->filled('rt_filter')) {
            $rtFilter = $request->rt_filter;
            $residentQuery->where(function ($q) use ($rtFilter) {
                $q->where('rt_id', $rtFilter)
                  ->orWhereHas('family', fn ($fq) => $fq->where('rt_id', $rtFilter));
            });
        }

        if ($request->filled('status_filter')) {
            $residentQuery->where('status', $request->status_filter);
        }

        if ($request->filled('gender_filter')) {
            $residentQuery->where('gender', $request->gender_filter);
        }

        if ($request->filled('account_filter')) {
            if ($request->account_filter === 'HAS_ACCOUNT') {
                $residentQuery->has('user');
            } elseif ($request->account_filter === 'NO_ACCOUNT') {
                $residentQuery->doesntHave('user');
            } elseif (in_array($request->account_filter, ['pending', 'active', 'inactive', 'rejected'])) {
                $residentQuery->whereHas('user', fn ($uq) => $uq->where('status', $request->account_filter));
            }
        }

        $residents = $residentQuery->latest()->paginate(10, ['*'], 'penduduk_page')->withQueryString();

        // Dropdown list RT & RW untuk filter kependudukan sesuai wewenang user
        $filterRwQuery = Rw::with('village')->orderBy('number');
        $filterRtQuery = Rt::with('rw')->orderBy('number');

        if ($user->role === 'ADMIN_DESA' && $user->village_id) {
            $filterRwQuery->where('village_id', $user->village_id);
            $filterRtQuery->whereHas('rw', fn ($q) => $q->where('village_id', $user->village_id));
        } elseif ($user->role === 'RW') {
            $rwId = $user->rw_id ?? ($user->resident?->rw_id ?? ($user->resident?->rt?->rw_id ?? null));
            if ($rwId) {
                $filterRwQuery->where('id', $rwId);
                $filterRtQuery->where('rw_id', $rwId);
            }
        } elseif ($user->role === 'RT') {
            $rtId = $user->rt_id ?? ($user->resident?->rt_id ?? ($user->resident?->family?->rt_id ?? null));
            if ($rtId) {
                $filterRwQuery->whereHas('rts', fn ($q) => $q->where('id', $rtId));
                $filterRtQuery->where('id', $rtId);
            }
        }

        $filterRws = $filterRwQuery->get();
        $filterRts = $filterRtQuery->get();

        // ================= RINGKASAN STATISTIK =================
        $totalResidents = Resident::accessibleBy($user)->count();
        $totalMale = Resident::accessibleBy($user)->where('gender', 'LAKI_LAKI')->count();
        $totalFemale = Resident::accessibleBy($user)->where('gender', 'PEREMPUAN')->count();
        $totalFamilies = Resident::accessibleBy($user)->where('is_head_of_family', true)->count();
        $totalUsers = Resident::accessibleBy($user)->has('user')->count();
        $activeUsers = Resident::accessibleBy($user)->whereHas('user', fn ($uq) => $uq->where('status', 'active'))->count();
        $pendingUsers = Resident::accessibleBy($user)->whereHas('user', fn ($uq) => $uq->where('status', 'pending'))->count();

        $stats = [
            'villages' => $canAccessDesa ? Village::count() : 0,
            'rws' => $canAccessRwRt ? (clone $rwQuery)->count() : 0,
            'rts' => $canAccessRwRt ? (clone $rtQuery)->count() : 1,
            'residents' => $totalResidents,
            'male' => $totalMale,
            'female' => $totalFemale,
            'families' => $totalFamilies,
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'pending_users' => $pendingUsers,
        ];

        return view('dashboard.areas.index', compact(
            'villages',
            'allVillages',
            'rws',
            'rts',
            'residents',
            'filterRws',
            'filterRts',
            'stats',
            'canAccessDesa',
            'canAccessRwRt',
            'canAccessPenduduk',
            'rtObj',
            'rwObj'
        ));
    }

    // ---- RW CRUD ----
    public function storeRw(Request $request)
    {
        $validated = $request->validate([
            'number' => 'required|string|max:10',
            'village_id' => 'required|exists:villages,id',
        ]);

        $rw = Rw::create($validated);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'CREATE',
            'entity_name' => 'RW',
            'entity_id' => $rw->id,
            'details' => ['number' => $rw->number],
        ]);

        return redirect()->route('areas.index')->with('success', 'Data RW berhasil ditambahkan.');
    }

    public function destroyRw(Rw $rw)
    {
        $number = $rw->number;
        $rw->delete();

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'DELETE',
            'entity_name' => 'RW',
            'entity_id' => null,
            'details' => ['number' => $number],
        ]);

        return redirect()->route('areas.index')->with('success', 'Data RW berhasil dihapus.');
    }

    // ---- RT CRUD ----
    public function storeRt(Request $request)
    {
        $validated = $request->validate([
            'number' => 'required|string|max:10',
            'rw_id' => 'required|exists:rws,id',
        ]);

        $rt = Rt::create($validated);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'CREATE',
            'entity_name' => 'RT',
            'entity_id' => $rt->id,
            'details' => ['number' => $rt->number],
        ]);

        return redirect()->route('areas.index')->with('success', 'Data RT berhasil ditambahkan.');
    }

    public function destroyRt(Rt $rt)
    {
        $number = $rt->number;
        $rt->delete();

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'DELETE',
            'entity_name' => 'RT',
            'entity_id' => null,
            'details' => ['number' => $number],
        ]);

        return redirect()->route('areas.index')->with('success', 'Data RT berhasil dihapus.');
    }
}
