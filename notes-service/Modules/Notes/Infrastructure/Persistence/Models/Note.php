<?php

namespace Modules\Notes\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    // protected $connection = 'notes_db';
    protected $guarded = [];

    // scope for optimized queries
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
