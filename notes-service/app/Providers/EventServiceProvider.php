<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Notes\Events\NoteCreated;
use Modules\Notes\Listeners\ProcessNoteAnalyticsListener;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        NoteCreated::class => [
            ProcessNoteAnalyticsListener::class,
        ],
    ];
}
