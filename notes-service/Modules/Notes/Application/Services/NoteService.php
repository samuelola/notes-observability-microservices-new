<?php

namespace Modules\Notes\Application\Services;

use Illuminate\Support\Facades\Cache;
use Modules\Notes\Application\Contracts\NoteServiceInterface;
use Modules\Notes\Application\DTOs\CreateNoteDTO;
use Modules\Notes\Application\DTOs\UpdateNoteDTO;
use Modules\Notes\Domain\Events\NoteCreated;
use Modules\Notes\Domain\Repositories\NoteRepositoryInterface;

class NoteService implements NoteServiceInterface
{
    protected $repo;

    public function __construct(NoteRepositoryInterface $noterepositoryinterface)
    {
        $this->repo = $noterepositoryinterface;
    }

    // public function __construct(
    //     protected NoteRepositoryInterface $repo
    // ) {}

    public function getUserNotes(int $userId, int $page = 1)
    {

        // $cacheKey = "notes_user_{$userId}_page_{$page}";

        // $notes = Cache::remember($cacheKey, 60, function () use ($userId) {

        //        return $this->repo->allUserNotes($userId);
        // });

        // return $notes;

        return $this->repo->allUserNotes($userId, $page);
    }

    public function create(CreateNoteDTO $dto)
    {

        $note = $this->repo->create([
            'title' => $dto->title,
            'content' => $dto->content,
            'user_id' => $dto->userId,
        ]);

        // Domain event (decoupled from controller)
        event(new NoteCreated($note->id));

        return $note;
    }

    public function showUserNote(int $id, int $userId)
    {
        // return Cache::remember("note_{$id}_user_{$userId}", 60, function () use ($id, $userId) {
        //     return $this->repo->findForUser($id, $userId);
        // });

        return $this->repo->findForUser($id, $userId);
    }

    public function update(UpdateNoteDTO $dto)
    {
        $note = $this->repo->update(
            $dto->noteId,
            $dto->userId,
            [
                'title' => $dto->title,
                'content' => $dto->content,
            ]);

        // Domain event (decoupled from controller)
        event(new NoteCreated($note->id));

        return $note;
    }

    public function delete(int $id, int $userId)
    {
        return $this->repo->delete($id, $userId);
    }
}
