<?php

namespace Modules\Notes\Application\CommandHandlers;

use Modules\Notes\Application\Commands\DeleteNoteCommand;
use Modules\Notes\Application\Contracts\CacheInterface;
use Modules\Notes\Application\Contracts\ImageStorageInterface;
use Modules\Notes\Domain\Contracts\AuthClientInterface;
use Modules\Notes\Domain\Repositories\NoteRepositoryInterface;

class DeleteNoteHandler
{
    public function __construct(
        private NoteRepositoryInterface $repo,
        private AuthClientInterface $authClient,
        private CacheInterface $cache,
        private ImageStorageInterface $imageStorage
    ) {}

    public function handle(DeleteNoteCommand $command)
    {

        // Get the note before deleting it so we know its image path
        $note = $this->repo->findForUser(
            $command->noteId,
            $command->userId
        );

        // Delete image from S3 if one exists
        if ($note->image_path) {
            $this->imageStorage->delete($note->image_path);
        }

        // Delete note from database
        $result = $this->repo->delete(
            $command->noteId,
            $command->userId
        );

        $this->cache->forget(
            "notes:user:{$command->userId}"
        );

        return $result;
    }
}
