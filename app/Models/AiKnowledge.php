<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AiKnowledge extends Model
{
    use HasUuids;

    protected $table = 'ai_knowledge';

    protected $guarded = [];

    public function updater()
    {
        return $this->belongsTo(User::class, 'last_updated_by');
    }
}
