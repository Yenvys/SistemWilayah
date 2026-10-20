<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\User;
use App\Models\Village;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    private function getAllowedRoles()
    {
        $role = Auth::user()->role;
        if ($role === 'SUPER_ADMIN') {
            return ['SUPER_ADMIN', 'ADMIN_DESA', 'RW', 'RT', 'WARGA'];
        }
        if ($role === 'ADMIN_DESA') {
            return ['RW', 'RT', 'WARGA'];
        }
        if ($role === 'RW') {
            return ['RT', 'WARGA'];
        }
        if ($role === 'RT') {
            return ['WARGA'];
        }

        return [];
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        // Bagi RT, data warga dan akun warga sudah disatukan pada menu Data & Akun Warga RT
        if ($user->role === 'RT') {
            return redirect()->route('areas.index');
        }

        $query = User::with(['village', 'rw', 'rt', 'resident']);

        // Role-based scoping
        if ($user->role === 'ADMIN_DESA') {
            $villageId = $user->village_id ?? ($user->resident?->village_id ?? ($user->resident?->rt?->rw?->village_id ?? null));
            if ($villageId) {
                $query->where('village_id', $villageId);
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($user->role === 'RW') {
            $rwId = $user->rw_id ?? ($user->resident?->rw_id ?? ($user->resident?->rt?->rw_id ?? null));
            if ($rwId) {
                $query->where('rw_id', $rwId);
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($user->role === 'RT') {
            $rtId = $user->rt_id ?? ($user->resident?->rt_id ?? ($user->resident?->family?->rt_id ?? null));
            if ($rtId) {
                $query->where(function ($q) use ($rtId) {
                    $q->where('rt_id', $rtId)
                        ->orWhereHas('resident', fn ($r) => $r->where('rt_id', $rtId)->orWhereHas('family', fn ($f) => $f->where('rt_id', $rtId)));
                });
            } else {
                $query->whereRaw('1 = 0');
            }
            $query->where('role', 'WARGA');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhereHas('resident', fn ($sub) => $sub->where('full_name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->latest()->paginate(10);
        $roles = $user->role === 'RT' ? ['WARGA'] : ['SUPER_ADMIN', 'ADMIN_DESA', 'RW', 'RT', 'WARGA'];
        $allowedRoles = $this->getAllowedRoles();
        $villages = Village::orderBy('name')->get();
        $rws = Rw::with('village')->orderBy('number')->get();

        $rtsQuery = Rt::with('rw.village')->orderBy('number');
        if ($user->role === 'RT') {
            $rtId = $user->rt_id ?? ($user->resident?->rt_id ?? ($user->resident?->family?->rt_id ?? null));
            $rtsQuery->where('id', $rtId);
        }
        $rts = $rtsQuery->get();

        return view('dashboard.users.index', compact('users', 'roles', 'allowedRoles', 'villages', 'rws', 'rts'));
    }

    public function create()
    {
        $user = Auth::user();
        $villages = Village::orderBy('name')->get();
        $rws = Rw::with('village')->orderBy('number')->get();

        $rtsQuery = Rt::with('rw.village')->orderBy('number');
        if ($user->role === 'RT') {
            $rtId = $user->rt_id ?? ($user->resident?->rt_id ?? ($user->resident?->family?->rt_id ?? null));
            $rtsQuery->where('id', $rtId);
        }
        $rts = $rtsQuery->get();

        $roles = $this->getAllowedRoles();

        return view('dashboard.users.create', compact('villages', 'rws', 'rts', 'roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:users,username',
            'full_name' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|regex:/^08[0-9]{8,11}$/|unique:users,phone_number',
            'address' => 'nullable|string|max:500',
            'email' => 'nullable|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => ['required', Rule::in($this->getAllowedRoles())],
            'village_id' => 'nullable|exists:villages,id',
            'rw_id' => 'nullable|exists:rws,id',
            'rt_id' => 'nullable|exists:rts,id',
        ], [
            'phone_number.regex' => 'Format nomor HP tidak valid (Harus diawali 08 dan 10-13 digit).',
            'phone_number.unique' => 'Nomor HP ini sudah digunakan oleh akun lain.',
        ]);

        $currentUser = Auth::user();
        if ($currentUser->role === 'RT') {
            $rtId = $currentUser->rt_id ?? ($currentUser->resident?->rt_id ?? ($currentUser->resident?->family?->rt_id ?? null));
            $validated['role'] = 'WARGA';
            $validated['rt_id'] = $rtId;
            $rt = Rt::with('rw')->find($rtId);
            if ($rt) {
                $validated['rw_id'] = $rt->rw_id;
                $validated['village_id'] = $rt->rw->village_id ?? null;
            }
        }

        $fullName = $validated['full_name'] ?? null;
        unset($validated['full_name']);

        $validated['password'] = Hash::make($validated['password']);

        $newUser = User::create($validated);

        if ($fullName && $newUser->resident) {
            $newUser->resident->update(['full_name' => $fullName]);
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'CREATE',
            'entity_name' => 'User',
            'entity_id' => $newUser->id,
            'details' => ['username' => $newUser->username, 'role' => $newUser->role],
        ]);

        $redirectTo = $request->input('redirect_to', $currentUser->role === 'RT' ? route('areas.index') : route('users.index'));

        return redirect($redirectTo)->with('success', 'Akun user berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $this->authorizeUserScope($user);

        $currentUser = Auth::user();
        $user->load('resident');
        $villages = Village::orderBy('name')->get();
        $rws = Rw::with('village')->orderBy('number')->get();

        $rtsQuery = Rt::with('rw.village')->orderBy('number');
        if ($currentUser->role === 'RT') {
            $rtId = $currentUser->rt_id ?? ($currentUser->resident?->rt_id ?? ($currentUser->resident?->family?->rt_id ?? null));
            $rtsQuery->where('id', $rtId);
        }
        $rts = $rtsQuery->get();

        $roles = $this->getAllowedRoles();

        // Allow keeping current role if higher priority
        if (! in_array($user->role, $roles)) {
            $roles[] = $user->role;
        }

        return view('dashboard.users.edit', compact('user', 'villages', 'rws', 'rts', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorizeUserScope($user);

        $allowedRoles = $this->getAllowedRoles();
        if (! in_array($user->role, $allowedRoles)) {
            $allowedRoles[] = $user->role;
        }

        $validated = $request->validate([
            'full_name' => ['nullable', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'regex:/^08[0-9]{8,11}$/', Rule::unique('users')->ignore($user->id)],
            'address' => ['nullable', 'string', 'max:500'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6|confirmed',
            'role' => ['required', Rule::in($allowedRoles)],
            'village_id' => 'nullable|exists:villages,id',
            'rw_id' => 'nullable|exists:rws,id',
            'rt_id' => 'nullable|exists:rts,id',
        ], [
            'phone_number.regex' => 'Format nomor HP tidak valid (Harus diawali 08 dan 10-13 digit).',
            'phone_number.unique' => 'Nomor HP ini sudah digunakan oleh akun lain.',
        ]);

        $currentUser = Auth::user();
        if ($currentUser->role === 'RT') {
            $rtId = $currentUser->rt_id ?? ($currentUser->resident?->rt_id ?? ($currentUser->resident?->family?->rt_id ?? null));
            $validated['role'] = 'WARGA';
            $validated['rt_id'] = $rtId;
        }

        unset($validated['username']);

        if (! empty($validated['full_name']) && $user->resident) {
            $user->resident->update(['full_name' => $validated['full_name']]);
        }
        unset($validated['full_name']);

        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'UPDATE',
            'entity_name' => 'User',
            'entity_id' => $user->id,
            'details' => ['username' => $user->username, 'role' => $user->role],
        ]);

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $this->authorizeUserScope($user);

        if ($user->id === Auth::id()) {
            return redirect()->route('users.index')->with('error', 'Anda tidak bisa menghapus akun sendiri.');
        }

        $username = $user->username;
        $user->delete();

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'DELETE',
            'entity_name' => 'User',
            'entity_id' => null,
            'details' => ['username' => $username],
        ]);

        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }

    public function verify(Request $request, User $user)
    {
        $this->authorizeUserScope($user);

        $validated = $request->validate([
            'status' => 'required|in:active,rejected',
        ]);

        $user->update(['status' => $validated['status']]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'UPDATE',
            'entity_name' => 'User',
            'entity_id' => $user->id,
            'details' => ['status_changed_to' => $validated['status']],
        ]);

        $message = $validated['status'] === 'active' ? 'User berhasil diverifikasi dan diaktifkan.' : 'Registrasi user ditolak.';

        return back()->with('success', $message);
    }

    protected function authorizeUserScope(User $targetUser): void
    {
        $currentUser = Auth::user();
        if ($currentUser->role === 'SUPER_ADMIN') {
            return;
        }

        if ($currentUser->role === 'RT') {
            $myRtId = $currentUser->rt_id ?? ($currentUser->resident?->rt_id ?? ($currentUser->resident?->family?->rt_id ?? null));
            $targetRtId = $targetUser->rt_id ?? ($targetUser->resident?->rt_id ?? ($targetUser->resident?->family?->rt_id ?? null));
            if (! $myRtId || $targetRtId !== $myRtId || $targetUser->role !== 'WARGA') {
                abort(403, 'Akses Ditolak: Anda hanya berwenang mengelola akun warga di RT Anda sendiri.');
            }
        } elseif ($currentUser->role === 'RW') {
            $myRwId = $currentUser->rw_id ?? ($currentUser->resident?->rw_id ?? ($currentUser->resident?->rt?->rw_id ?? null));
            $targetRwId = $targetUser->rw_id ?? ($targetUser->resident?->rw_id ?? ($targetUser->resident?->rt?->rw_id ?? null));
            if (! $myRwId || $targetRwId !== $myRwId || in_array($targetUser->role, ['SUPER_ADMIN', 'ADMIN_DESA', 'RW'])) {
                abort(403, 'Akses Ditolak: Anda tidak memiliki wewenang untuk mengelola akun di luar wilayah RW Anda.');
            }
        } elseif ($currentUser->role === 'ADMIN_DESA') {
            $myVillageId = $currentUser->village_id ?? ($currentUser->resident?->village_id ?? ($currentUser->resident?->rt?->rw?->village_id ?? null));
            $targetVillageId = $targetUser->village_id ?? ($targetUser->resident?->village_id ?? ($targetUser->resident?->rt?->rw?->village_id ?? null));
            if (! $myVillageId || $targetVillageId !== $myVillageId || in_array($targetUser->role, ['SUPER_ADMIN', 'ADMIN_DESA'])) {
                abort(403, 'Akses Ditolak: Anda tidak memiliki wewenang untuk mengelola akun di luar wilayah Desa Anda.');
            }
        }
    }
}
