<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentRequest extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by_id');
    }

    public function rtApprovedBy()
    {
        return $this->belongsTo(User::class, 'rt_approved_by_id');
    }

    public function rwApprovedBy()
    {
        return $this->belongsTo(User::class, 'rw_approved_by_id');
    }

    public function desaApprovedBy()
    {
        return $this->belongsTo(User::class, 'desa_approved_by_id');
    }

    public function histories()
    {
        return $this->hasMany(DocumentRequestHistory::class);
    }

    public function getRtNumberAttribute(): string
    {
        return $this->resident->rt->number ?? ($this->resident->family->rt->number ?? '-');
    }

    public function getRwNumberAttribute(): string
    {
        return $this->resident->rt->rw->number ?? ($this->resident->family->rt->rw->number ?? '-');
    }

    public function getVillageNameAttribute(): string
    {
        return $this->resident->rt->rw->village->name ?? ($this->resident->family->rt->rw->village->name ?? 'Terpadu');
    }

    public function getQrCodeUrlAttribute(): string
    {
        $data = "VERIFIED_SIWIL_AI|NO:{$this->request_number}|TGL:{$this->updated_at->format('Y-m-d')}|STATUS:{$this->status}";

        return 'https://api.qrserver.com/v1/create-qr-code/?size=120x120&data='.urlencode($data);
    }

    public function getFinalApprovalLevelAttribute(): string
    {
        $type = $this->type;

        $rtLevel = [
            'IZIN_RENOVASI_RUMAH',
            'PENDATAAN_TAMU_MENGINAP',
            'IURAN_KAS_RT',
            'SURAT_REKOMENDASI_INTERNAL',
        ];

        $rwLevel = [
            'SURAT_IZIN_KERAMAIAN',
            'SURAT_PENGANTAR_KEHILANGAN',
            'MEDIASI_SENGKETA_WARGA',
        ];

        if (in_array($type, $rtLevel)) {
            return 'RT';
        }

        if (in_array($type, $rwLevel)) {
            return 'RW';
        }

        return 'DESA';
    }

    public function getNextActionStatusAttribute(): string
    {
        $currentStatus = $this->status;
        $finalLevel = $this->final_approval_level;

        if ($currentStatus === 'DIAJUKAN_RT' || $currentStatus === 'PENDING') {
            return $finalLevel === 'RT' ? 'DISETUJUI' : 'DIAJUKAN_RW';
        }

        if ($currentStatus === 'DIAJUKAN_RW') {
            return $finalLevel === 'RW' ? 'DISETUJUI' : 'DIAJUKAN_DESA';
        }

        if ($currentStatus === 'DIAJUKAN_DESA') {
            return 'DISETUJUI';
        }

        return $currentStatus;
    }

    public function getNextActionLabelAttribute(): string
    {
        $next = $this->next_action_status;
        if ($next === 'DISETUJUI') {
            return 'Setujui & Terbitkan Surat';
        }
        if ($next === 'DIAJUKAN_RW') {
            return 'Setujui (Teruskan ke RW)';
        }
        if ($next === 'DIAJUKAN_DESA') {
            return 'Setujui (Teruskan ke Desa)';
        }

        return 'Setujui Pengajuan';
    }

    public function getNextActionStatusFor(?User $user = null): string
    {
        $user = $user ?? auth()->user();
        if ($user && in_array($user->role, ['ADMIN_DESA', 'SUPER_ADMIN'])) {
            return 'DISETUJUI';
        }

        return $this->next_action_status;
    }

    public function getNextActionLabelFor(?User $user = null): string
    {
        $user = $user ?? auth()->user();
        if ($user && in_array($user->role, ['ADMIN_DESA', 'SUPER_ADMIN'])) {
            return 'Setujui & Terbitkan Surat';
        }

        return $this->next_action_label;
    }

    public function canBeApprovedBy(User $user): bool
    {
        if (in_array($this->status, ['DISETUJUI', 'SELESAI', 'DITOLAK'])) {
            return false;
        }

        if ($user->role === 'SUPER_ADMIN') {
            return true;
        }

        if ($user->role === 'RT' && in_array($this->status, ['DIAJUKAN_RT', 'PENDING'])) {
            return true;
        }

        if ($user->role === 'RW' && $this->status === 'DIAJUKAN_RW') {
            return true;
        }

        if (in_array($user->role, ['ADMIN_DESA', 'SUPER_ADMIN']) && in_array($this->status, ['DIAJUKAN_DESA', 'DIAJUKAN_RW', 'DIAJUKAN_RT', 'PENDING'])) {
            return true;
        }

        return false;
    }

    public function scopeAccessibleBy($query, User $user)
    {
        $rtId = $user->rt_id ?? ($user->resident?->rt_id ?? ($user->resident?->family?->rt_id ?? null));
        $rwId = $user->rw_id ?? ($user->resident?->rt?->rw_id ?? ($user->resident?->family?->rt?->rw_id ?? null));
        $villageId = $user->village_id ?? ($user->resident?->rt?->rw?->village_id ?? ($user->resident?->family?->rt?->rw?->village_id ?? null));

        if ($user->role === 'WARGA') {
            $query->whereHas('resident', function ($q) use ($user) {
                $q->where('nik', $user->username);
            });
        } elseif ($user->role === 'RT') {
            if (! $rtId) {
                return $query->whereRaw('1 = 0');
            }
            $query->where(function ($q) use ($rtId) {
                $q->whereHas('resident', function ($sub) use ($rtId) {
                    $sub->where('rt_id', $rtId)
                        ->orWhereHas('family', fn ($f) => $f->where('rt_id', $rtId));
                });
            });
        } elseif ($user->role === 'RW') {
            $query->where(function ($q) use ($rwId) {
                $q->whereHas('resident', function ($sub) use ($rwId) {
                    $sub->whereHas('rt', fn ($r) => $r->where('rw_id', $rwId))
                        ->orWhereHas('family.rt', fn ($f) => $f->where('rw_id', $rwId));
                });
            });
        } elseif ($user->role === 'ADMIN_DESA') {
            $query->where(function ($q) use ($villageId) {
                $q->whereHas('resident', function ($sub) use ($villageId) {
                    $sub->whereHas('rt.rw', fn ($r) => $r->where('village_id', $villageId))
                        ->orWhereHas('family.rt.rw', fn ($f) => $f->where('village_id', $villageId));
                });
            });
        }

        return $query;
    }
}
