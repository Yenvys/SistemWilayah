<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rw extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    public function village()
    {
        return $this->belongsTo(Village::class);
    }

    public function rts()
    {
        return $this->hasMany(Rt::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
