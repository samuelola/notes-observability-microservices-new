<?php

namespace App\Jobs;

use App\Models\Note;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessNoteAnalytics implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(public int $noteId) {}

    public function handle(): void
    {
        $note = Note::find($this->noteId);

        if (! $note) {
            return;
        }

        // simulate heavy work (AI processing, tagging, etc.)
        sleep(2);

        $wordCount = str_word_count($note->content);

        // pretend saving analytics (could be another table)
        logger()->info("Note {$note->id} has {$wordCount} words");
    }
}
