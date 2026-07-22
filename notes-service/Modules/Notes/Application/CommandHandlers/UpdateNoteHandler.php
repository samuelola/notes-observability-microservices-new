<?php

namespace Modules\Notes\Application\CommandHandlers;

use Modules\Notes\Application\Commands\UpdateNoteCommand;
use Modules\Notes\Application\Contracts\CacheInterface;
use Modules\Notes\Application\Contracts\EventDispatcherInterface;
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
    ) {}

    public function handle(UpdateNoteCommand $command)
    {

        $note = $this->repo->update(
            $command->noteId,
            $command->userId,
            [
                'title' => $command->title,
                'content' => $command->content,
            ]);

        $this->cache->forget(
            "notes:user:{$command->userId}"
        );

        $this->events->dispatch(
            new NoteCreated($note->id)
        );

        return $note;

    }
}
