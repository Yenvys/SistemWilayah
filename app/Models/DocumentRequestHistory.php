<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentRequestHistory extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    public function documentRequest()
    {
        return $this->belongsTo(DocumentRequest::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
