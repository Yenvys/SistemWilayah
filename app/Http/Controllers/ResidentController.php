<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreResidentRequest;
use App\Http\Requests\UpdateResidentRequest;
use App\Models\Resident;
use App\Models\Rt;
use App\Services\ResidentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResidentController extends Controller
{
    protected ResidentService $residentService;

    public function __construct(ResidentService $residentService)
    {
        $this->residentService = $residentService;
    }

    public function index(Request $request)
    {
        $params = array_merge(['tab' => 'penduduk'], $request->all());
        if ($request->filled('search')) {
            $params['search_resident'] = $request->search;
            unset($params['search']);
        }

        return redirect()->route('areas.index', $params);
    }

    public function create()
    {
        $user = Auth::user();
        $rtsQuery = Rt::with('rw.village');

        if ($user->role === 'RT') {
            $rtId = $user->rt_id ?? ($user->resident?->rt_id ?? ($user->resident?->family?->rt_id ?? null));
            $rtsQuery->where('id', $rtId);
        } elseif ($user->role === 'RW') {
            $rwId = $user->rw_id ?? ($user->resident?->rw_id ?? ($user->resident?->rt?->rw_id ?? null));
            $rtsQuery->where('rw_id', $rwId);
        } elseif ($user->role === 'ADMIN_DESA') {
            $villageId = $user->village_id ?? ($user->resident?->village_id ?? ($user->resident?->rt?->rw?->village_id ?? null));
            $rtsQuery->whereHas('rw', fn ($q) => $q->where('village_id', $villageId));
        }

        $rts = $rtsQuery->get();

        return view('dashboard.residents.create', compact('rts'));
    }

    public function store(StoreResidentRequest $request)
    {
        $this->residentService->store($request->validated(), Auth::user());

        return redirect()->route('residents.index')->with('success', 'Data warga berhasil ditambahkan.');
    }

    public function show(Resident $resident)
    {
        $this->authorizeResidentAccess(Auth::user(), $resident);

        $resident->load(['family.rt.rw.village', 'rt.rw.village']);

        return view('dashboard.residents.show', compact('resident'));
    }

    public function edit(Resident $resident)
    {
        $user = Auth::user();
        $this->authorizeResidentAccess($user, $resident);

        $resident->load('user');

        $rtsQuery = Rt::with('rw.village');
        if ($user->role === 'RT') {
            $rtId = $user->rt_id ?? ($user->resident?->rt_id ?? ($user->resident?->family?->rt_id ?? null));
            $rtsQuery->where('id', $rtId);
        } elseif ($user->role === 'RW') {
            $rwId = $user->rw_id ?? ($user->resident?->rw_id ?? ($user->resident?->rt?->rw_id ?? null));
            $rtsQuery->where('rw_id', $rwId);
        } elseif ($user->role === 'ADMIN_DESA') {
            $villageId = $user->village_id ?? ($user->resident?->village_id ?? ($user->resident?->rt?->rw?->village_id ?? null));
            $rtsQuery->whereHas('rw', fn ($q) => $q->where('village_id', $villageId));
        }
        $rts = $rtsQuery->get();

        $roles = $this->residentService->getAllowedRoles($user);
        $currentRole = $resident->user ? $resident->user->role : 'WARGA';
        if (! in_array($currentRole, $roles)) {
            $roles[] = $currentRole;
        }

        return view('dashboard.residents.edit', compact('resident', 'rts', 'roles', 'currentRole'));
    }

    public function update(UpdateResidentRequest $request, Resident $resident)
    {
        $user = Auth::user();
        $this->authorizeResidentAccess($user, $resident);

        $validated = $request->validated();
        if ($user->role === 'RT') {
            $rtId = $user->rt_id ?? ($user->resident?->rt_id ?? ($user->resident?->family?->rt_id ?? null));
            $validated['rt_id'] = $rtId;
        }

        $allowedRoles = $this->residentService->getAllowedRoles($user);
        $currentRole = $resident->user ? $resident->user->role : 'WARGA';
        if (! in_array($currentRole, $allowedRoles)) {
            $allowedRoles[] = $currentRole;
        }

        if ($request->filled('role') && ! in_array($request->role, $allowedRoles)) {
            return redirect()->back()->withErrors(['role' => 'Role tidak valid untuk pengguna ini.']);
        }

        $this->residentService->update($resident, $validated);

        return redirect()->route('residents.index')->with('success', 'Data warga berhasil diperbarui.');
    }

    protected function authorizeResidentAccess($user, Resident $resident): void
    {
        if ($user->role === 'SUPER_ADMIN') {
            return;
        }

        $residentRtId = $resident->rt_id ?? ($resident->family?->rt_id ?? null);
        $residentRwId = $resident->rt?->rw_id ?? ($resident->family?->rt?->rw_id ?? null);
        $residentVillageId = $resident->rt?->rw?->village_id ?? ($resident->family?->rt?->rw?->village_id ?? null);

        if ($user->role === 'RT') {
            $myRtId = $user->rt_id ?? ($user->resident?->rt_id ?? ($user->resident?->family?->rt_id ?? null));
            if (! $myRtId || $residentRtId !== $myRtId) {
                abort(403, 'Akses Ditolak: Anda hanya berwenang melihat atau mengubah data warga di RT Anda sendiri.');
            }
            if ($resident->user && in_array($resident->user->role, ['SUPER_ADMIN', 'ADMIN_DESA', 'RW'])) {
                abort(403, 'Akses Ditolak: Anda tidak memiliki wewenang untuk melihat atau mengubah data pejabat RW maupun Desa.');
            }
        } elseif ($user->role === 'RW') {
            $myRwId = $user->rw_id ?? ($user->resident?->rw_id ?? ($user->resident?->rt?->rw_id ?? null));
            if (! $myRwId || $residentRwId !== $myRwId) {
                abort(403, 'Akses Ditolak: Anda hanya berwenang melihat atau mengubah data warga di RW Anda sendiri.');
            }
            if ($resident->user && in_array($resident->user->role, ['SUPER_ADMIN', 'ADMIN_DESA'])) {
                abort(403, 'Akses Ditolak: Anda tidak memiliki wewenang untuk melihat atau mengubah data perangkat Desa.');
            }
        } elseif ($user->role === 'ADMIN_DESA') {
            $myVillageId = $user->village_id ?? ($user->resident?->village_id ?? ($user->resident?->rt?->rw?->village_id ?? null));
            if (! $myVillageId || $residentVillageId !== $myVillageId) {
                abort(403, 'Akses Ditolak: Anda hanya berwenang melihat atau mengubah data warga di Desa Anda sendiri.');
            }
        } elseif ($user->role === 'WARGA') {
            if ($resident->nik !== $user->username) {
                abort(403, 'Akses Ditolak: Anda hanya dapat mengakses data Anda sendiri.');
            }
        }
    }
}
