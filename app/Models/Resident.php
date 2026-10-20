<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resident extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    protected $casts = [
        'birth_date' => 'date',
    ];

    public function family()
    {
        return $this->belongsTo(Family::class);
    }

    public function rt()
    {
        return $this->belongsTo(Rt::class);
    }

    public function requests()
    {
        return $this->hasMany(DocumentRequest::class);
    }

    public function user()
    {
        return $this->hasOne(User::class, 'username', 'nik');
    }

    /**
     * Scope a query to only include residents accessible by the given user role.
     */
    public function scopeAccessibleBy($query, User $user)
    {
        $villageId = $user->village_id ?? ($user->resident?->rt?->rw?->village_id ?? ($user->resident?->family?->rt?->rw?->village_id ?? null));
        $rwId = $user->rw_id ?? ($user->resident?->rw_id ?? ($user->resident?->rt?->rw_id ?? ($user->resident?->family?->rt?->rw_id ?? null)));
        $rtId = $user->rt_id ?? ($user->resident?->rt_id ?? ($user->resident?->family?->rt_id ?? null));

        if ($user->role === 'ADMIN_DESA') {
            if ($villageId) {
                $query->where(function ($q) use ($villageId) {
                    $q->whereHas('rt.rw.village', fn ($sub) => $sub->where('id', $villageId))
                        ->orWhereHas('family.rt.rw.village', fn ($sub) => $sub->where('id', $villageId));
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($user->role === 'RW') {
            if ($rwId) {
                $query->where(function ($q) use ($rwId) {
                    $q->whereHas('rt.rw', fn ($sub) => $sub->where('id', $rwId))
                        ->orWhereHas('family.rt.rw', fn ($sub) => $sub->where('id', $rwId));
                })->whereDoesntHave('user', function ($u) {
                    $u->whereIn('role', ['SUPER_ADMIN', 'ADMIN_DESA']);
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($user->role === 'RT') {
            if ($rtId) {
                $query->where(function ($q) use ($rtId) {
                    $q->where('residents.rt_id', $rtId)
                        ->orWhereHas('family', fn ($f) => $f->where('rt_id', $rtId));
                })->whereDoesntHave('user', function ($u) {
                    $u->whereIn('role', ['SUPER_ADMIN', 'ADMIN_DESA', 'RW']);
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($user->role === 'WARGA') {
            $query->where('nik', $user->username);
        }

        return $query;
    }
}
