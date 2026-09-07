<?php

namespace Modules\Email\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class ProcessedEvent extends Model
{
    protected $table = 'processed_events';

    protected $fillable = [
        'event_id',
        'event_name',
        'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];
}
