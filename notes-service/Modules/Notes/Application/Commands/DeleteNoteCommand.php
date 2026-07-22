<?php

namespace Modules\Notes\Application\Commands;

class DeleteNoteCommand
{
    public function __construct(
        public int $noteId,
        public int $userId
    ) {}

}
