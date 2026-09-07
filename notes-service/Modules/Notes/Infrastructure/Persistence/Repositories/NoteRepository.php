<?php

namespace Modules\Notes\Infrastructure\Persistence\Repositories;

use Modules\Notes\Domain\Repositories\NoteRepositoryInterface;
use Modules\Notes\Infrastructure\Persistence\Models\Note;

class NoteRepository implements NoteRepositoryInterface
{
    public function create(array $data)
    {
        return Note::create($data);
    }

    public function findForUser(int $id, int $userId)
    {
        return Note::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();
    }

    public function allUserNotes(int $userId, int $page)
    {
        return Note::forUser($userId)
            ->select(['id', 'user_id', 'title', 'content', 'image_path', 'created_at'])
            ->latest()
            ->paginate(5);
    }

    public function getByUser(int $userId)
    {
        return Note::where('user_id', $userId)
            ->latest()
            ->paginate(10);
    }

    public function update(int $id, int $userId, array $data)
    {
        $note = $this->findForUser($id, $userId);
        $note->update($data);

        return $note->fresh();
    }

    public function delete(int $id, int $userId)
    {
        $note = $this->findForUser($id, $userId);

        return $note->delete();
    }
}
