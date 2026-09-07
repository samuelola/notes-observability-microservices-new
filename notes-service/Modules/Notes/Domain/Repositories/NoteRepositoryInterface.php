<?php

namespace Modules\Notes\Domain\Repositories;

interface NoteRepositoryInterface
{
    public function create(array $data);

    public function findForUser(int $id, int $userId);

    public function getByUser(int $userId);

    public function allUserNotes(int $userId);

    public function update(int $id, int $userId, array $data);

    public function delete(int $id, int $userId);
}
