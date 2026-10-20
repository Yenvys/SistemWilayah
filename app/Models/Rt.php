<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rt extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    public function rw()
    {
        return $this->belongsTo(Rw::class);
    }

    public function families()
    {
        return $this->hasMany(Family::class);
    }

    public function residents()
    {
        return $this->hasMany(Resident::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
