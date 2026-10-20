<?php

namespace App\Services;

use App\Models\Resident;
use App\Models\Rt;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ResidentService
{
    public function store(array $data, User $user): Resident
    {
        if ($user->role === 'RT') {
            $data['rt_id'] = $user->rt_id ?? ($user->resident?->rt_id ?? ($user->resident?->family?->rt_id ?? null));
        }

        $data['id'] = (string) Str::uuid();

        return Resident::create($data);
    }

    public function update(Resident $resident, array $data): Resident
    {
        $resident->update([
            'nik' => $data['nik'],
            'full_name' => $data['full_name'],
            'gender' => $data['gender'],
            'birth_place' => $data['birth_place'] ?? null,
            'birth_date' => $data['birth_date'] ?? null,
            'status' => $data['status'],
            'rt_id' => $data['rt_id'] ?? null,
        ]);

        if (isset($data['role'])) {
            $this->promoteToUser($resident, $data['role'], $data['rt_id'] ?? null);
        }

        return $resident;
    }

    private function promoteToUser(Resident $resident, string $role, ?string $rtId): void
    {
        $villageId = null;
        $rwId = null;

        if ($rtId) {
            $rt = Rt::with('rw')->find($rtId);
            if ($rt) {
                $rwId = $rt->rw_id;
                $villageId = $rt->rw->village_id ?? null;
            }
        }

        $user = User::where('username', $resident->nik)->first();
        if ($user) {
            $user->update([
                'role' => $role,
                'village_id' => $villageId,
                'rw_id' => $rwId,
                'rt_id' => $rtId,
            ]);
        } else {
            User::create([
                'username' => $resident->nik,
                'password' => Hash::make('password'),
                'role' => $role,
                'village_id' => $villageId,
                'rw_id' => $rwId,
                'rt_id' => $rtId,
            ]);
        }
    }

    public function getAllowedRoles(User $user): array
    {
        $role = $user->role;
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
}
