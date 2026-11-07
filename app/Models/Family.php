<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Family extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    public function rt()
    {
        return $this->belongsTo(Rt::class);
    }

    public function members()
    {
        return $this->hasMany(Resident::class);
    }

    public function residents()
    {
        return $this->hasMany(Resident::class);
    }
}
