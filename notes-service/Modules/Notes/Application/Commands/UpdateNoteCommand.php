<?php

namespace Modules\Notes\Application\Commands;

class UpdateNoteCommand
{
    public function __construct(
        public string $title,
        public string $content,
        public int $userId,
        public int $noteId,
        public $image
    ) {}
}
