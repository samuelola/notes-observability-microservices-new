<?php

namespace Modules\Notes\Application\Contracts;

use Modules\Notes\Application\DTOs\CreateNoteDTO;
use Modules\Notes\Application\DTOs\updateNoteDTO;

interface NoteServiceInterface
{
    public function create(CreateNoteDTO $dto);

    public function update(updateNoteDTO $dto);

    public function getUserNotes(
        int $userId,
        int $page = 1
    );

    public function showUserNote(
        int $noteId,
        int $userId
    );

    public function delete(
        int $noteId,
        int $userId
    );
}
