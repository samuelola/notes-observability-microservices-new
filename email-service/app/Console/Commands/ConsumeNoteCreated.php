<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Modules\Email\Infrastructure\Messaging\NoteCreatedConsumer;

#[Signature('rabbitmq:consume-note-created')]
#[Description('Consume note.created event')]
class ConsumeNoteCreated extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(NoteCreatedConsumer $consumer)
    {
        $this->info('Waiting for messages... from Note Created');
        $consumer->consume();
    }
}
