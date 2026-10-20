<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Village extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    public function rws()
    {
        return $this->hasMany(Rw::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
