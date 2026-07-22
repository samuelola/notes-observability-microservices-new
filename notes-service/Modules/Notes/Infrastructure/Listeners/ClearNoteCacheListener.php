<?php

namespace Modules\Notes\Infrastructure\Listeners;

use Illuminate\Support\Facades\Cache;
use Modules\Notes\Domain\Events\NoteCreated;

class ClearNoteCacheListener
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
        Cache::forget(
            "notes_user_{$event->note->user_id}_page_1"
        );

    }
}
