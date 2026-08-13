<?php

namespace Modules\Notes\Application\CommandHandlers;

use Modules\Notes\Application\Commands\UpdateNoteCommand;
use Modules\Notes\Application\Contracts\CacheInterface;
use Modules\Notes\Application\Contracts\EventDispatcherInterface;
use Modules\Notes\Application\Contracts\ImageStorageInterface;
use Modules\Notes\Domain\Contracts\AuthClientInterface;
use Modules\Notes\Domain\Events\NoteCreated;
use Modules\Notes\Domain\Repositories\NoteRepositoryInterface;

class UpdateNoteHandler
{
    public function __construct(
        private NoteRepositoryInterface $repo,
        private AuthClientInterface $authClient,
        private CacheInterface $cache,
        private EventDispatcherInterface $events,
        private ImageStorageInterface $imageStorage,
    ) {}

    public function handle(UpdateNoteCommand $command)
    {
        // Get the existing note first
        $note = $this->repo->findForUser(
            $command->noteId,
            $command->userId
        );

        $data = [
            'title' => $command->title,
            'content' => $command->content,
        ];

        // If a new image was uploaded
        if ($command->image) {

            // Upload the new image
            $newImagePath = $this->imageStorage->store(
                $command->image,
                "notes/{$command->userId}"
            );

            // Delete the old image
            if ($note->image_path) {
                $this->imageStorage->delete($note->image_path);
            }

            // Save the new path
            $data['image_path'] = $newImagePath;
        }

        $note = $this->repo->update(
            $command->noteId,
            $command->userId,
            $data
        );

        $this->cache->forget(
            "notes:user:{$command->userId}"
        );

        $this->events->dispatch(
            new NoteCreated($note->id)
        );

        return $note;
    }
}

