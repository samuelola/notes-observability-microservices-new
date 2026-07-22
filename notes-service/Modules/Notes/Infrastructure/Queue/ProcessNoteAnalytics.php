<?php

namespace Modules\Notes\Infrastructure\Queue;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Notes\Infrastructure\Persistence\Models\Note;

class ProcessNoteAnalytics implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

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

        \Log::info("Processing analytics for note {$note->id}");

        // pretend saving analytics (could be another table)
        logger()->info('Note analytics processed', [
            'service' => 'notes',
            'note_id' => $note->id,
            'word_count' => $wordCount,
        ]);
    }
}
