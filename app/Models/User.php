<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, HasUuids, Notifiable;

    protected $guarded = [];

    protected $fillable = [
        'name',
        'username',
        'email',
        'phone_number',
        'whatsapp_number',
        'whatsapp_lid',
        'address',
        'status',
        'kk_number',
        'password',
        'role',
        'village_id',
        'rw_id',
        'rt_id',
        'signature_path',
        'stamp_path',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function resident()
    {
        return $this->hasOne(Resident::class, 'nik', 'username');
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

    public function verifiedRequests()
    {
        return $this->hasMany(DocumentRequest::class, 'verified_by_id');
    }

    public function documentHistories()
    {
        return $this->hasMany(DocumentRequestHistory::class, 'actor_id');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function emergencies()
    {
        return $this->hasMany(Emergency::class);
    }

    public function appNotifications()
    {
        return $this->hasMany(AppNotification::class)->latest();
    }

    public function unreadNotificationsCount(): int
    {
        return $this->appNotifications()->where('is_read', false)->count();
    }
}
