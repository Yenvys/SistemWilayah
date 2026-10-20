<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use HasUuids;

    protected $guarded = [];

    public function village()
    {
        return $this->belongsTo(Village::class);
    }
}
