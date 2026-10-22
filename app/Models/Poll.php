<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Poll extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = ['expires_at' => 'datetime'];

    public function options()
    {
        return $this->hasMany(PollOption::class);
    }

    public function votes()
    {
        return $this->hasMany(PollVote::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function village()
    {
        return $this->belongsTo(Village::class);
    }

    public function rw()
    {
        return $this->belongsTo(Rw::class);
    }

    public function rt()
    {
        return $this->belongsTo(Rt::class);
    }

    /**
     * Get winning option based on highest vote count.
     */
    public function getWinningOptionAttribute()
    {
        return $this->options->sortByDesc(fn ($opt) => $opt->votes->count())->first();
    }

    /**
     * Get all citizen users who are in the target territory of this poll.
     * Rules:
     * - If creator role is ADMIN_DESA, SUPER_ADMIN, or RW: notification is UNIVERSAL for all warga.
     * - If creator role is RT: notification is restricted ONLY to warga within that specific RT.
     */
    public function getTargetCitizenUsers()
    {
        $query = User::where('role', 'WARGA');

        $creator = $this->creator ?? ($this->created_by ? User::find($this->created_by) : auth()->user());
        $creatorRole = $creator?->role;

        // Jika dibuat oleh RT: notifikasi HANYA diberikan ke warga yang masuk ke dalam RT tersebut saja
        if ($creatorRole === 'RT') {
            $rtId = $this->rt_id ?? ($creator?->rt_id ?? ($creator?->resident?->rt_id ?? ($creator?->resident?->family?->rt_id ?? null)));
            if ($rtId) {
                $query->where(function ($q) use ($rtId) {
                    $q->where('rt_id', $rtId)
                        ->orWhereHas('resident', fn ($r) => $r->where('rt_id', $rtId)->orWhereHas('family', fn ($f) => $f->where('rt_id', $rtId)));
                });
            }

            return $query->get();
        }

        // Jika dibuat oleh role ADMIN_DESA, SUPER_ADMIN, atau RW: berikan notifikasi UNIVERSAL ke seluruh warga
        if ($this->village_id) {
            $villageId = $this->village_id;
            $query->where(function ($q) use ($villageId) {
                $q->where('village_id', $villageId)
                    ->orWhereHas('resident', fn ($r) => $r->whereHas('rt.rw', fn ($rw) => $rw->where('village_id', $villageId))->orWhereHas('family.rt.rw', fn ($rw) => $rw->where('village_id', $villageId)))
                    ->orWhereNull('village_id');
            });
        }

        return $query->get();
    }

    /**
     * Scope poll query by user access.
     */
    public function scopeAccessibleBy($query, User $user)
    {
        if ($user->role === 'SUPER_ADMIN') {
            return $query;
        }

        $rtId = $user->rt_id ?? ($user->resident?->rt_id ?? ($user->resident?->family?->rt_id ?? null));
        $rwId = $user->rw_id ?? ($user->resident?->rw_id ?? ($user->resident?->rt?->rw_id ?? ($user->resident?->family?->rt?->rw_id ?? null)));
        $villageId = $user->village_id ?? ($user->resident?->village_id ?? ($user->resident?->rt?->rw?->village_id ?? ($user->resident?->family?->rt?->rw?->village_id ?? null)));

        if ($user->role === 'ADMIN_DESA') {
            if ($villageId) {
                $query->where(function ($q) use ($villageId) {
                    $q->where('village_id', $villageId)
                        ->orWhereNull('village_id');
                });
            }
        } elseif ($user->role === 'RW') {
            $query->where(function ($q) use ($villageId, $rwId) {
                $q->where(function ($sub) use ($rwId) {
                    $sub->where('rw_id', $rwId);
                })->orWhere(function ($sub) use ($villageId) {
                    $sub->where('level', 'DESA')->where('village_id', $villageId);
                })->orWhereNull('level')
                    ->orWhereHas('creator', fn ($cq) => $cq->whereIn('role', ['ADMIN_DESA', 'SUPER_ADMIN', 'RW']));
            });
        } elseif ($user->role === 'RT') {
            $query->where(function ($q) use ($villageId, $rwId, $rtId) {
                $q->where(function ($sub) use ($rtId) {
                    $sub->where('rt_id', $rtId);
                })->orWhere(function ($sub) use ($rwId) {
                    $sub->where('level', 'RW')->where('rw_id', $rwId);
                })->orWhere(function ($sub) use ($villageId) {
                    $sub->where('level', 'DESA')->where('village_id', $villageId);
                })->orWhereNull('level')
                    ->orWhereHas('creator', fn ($cq) => $cq->whereIn('role', ['ADMIN_DESA', 'SUPER_ADMIN', 'RW']));
            });
        } elseif ($user->role === 'WARGA') {
            $query->where(function ($q) use ($villageId, $rwId, $rtId) {
                $q->where(function ($sub) use ($rtId) {
                    $sub->where('level', 'RT')->where('rt_id', $rtId);
                })->orWhere(function ($sub) use ($rwId) {
                    $sub->where('level', 'RW')->where('rw_id', $rwId);
                })->orWhere(function ($sub) use ($villageId) {
                    $sub->where('level', 'DESA')->where('village_id', $villageId);
                })->orWhere(function ($sub) {
                    $sub->whereNull('level');
                })->orWhereHas('creator', function ($cq) {
                    // Polling yang dibuat oleh ADMIN_DESA, SUPER_ADMIN, atau RW dapat diakses universal oleh warga
                    $cq->whereIn('role', ['ADMIN_DESA', 'SUPER_ADMIN', 'RW']);
                });
            });
        }

        return $query;
    }
}
