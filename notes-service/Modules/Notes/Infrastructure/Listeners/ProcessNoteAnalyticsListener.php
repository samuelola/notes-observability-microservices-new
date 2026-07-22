<?php

namespace Modules\Notes\Infrastructure\Listeners;

use Modules\Notes\Domain\Events\NoteCreated;
use Modules\Notes\Infrastructure\Queue\ProcessNoteAnalytics;

class ProcessNoteAnalyticsListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(NoteCreated $event): void
    {
        // dispatch heavy job
        ProcessNoteAnalytics::dispatch($event->noteId);
    }
}
