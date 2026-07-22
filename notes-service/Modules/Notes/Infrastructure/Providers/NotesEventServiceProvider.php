<?php

namespace Modules\Notes\Infrastructure\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Notes\Domain\Events\NoteCreated;
use Modules\Notes\Infrastructure\Listeners\ProcessNoteAnalyticsListener;

class NotesEventServiceProvider extends ServiceProvider
{
    protected $listen = [
        NoteCreated::class => [
            ProcessNoteAnalyticsListener::class,
        ],
    ];
}
