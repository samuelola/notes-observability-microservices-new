<?php

namespace Modules\Notes\Application\CommandHandlers;

use Modules\Notes\Application\Commands\CreateNoteCommand;
use Modules\Notes\Application\Contracts\CacheInterface;
use Modules\Notes\Application\Contracts\EventDispatcherInterface;
use Modules\Notes\Domain\Contracts\AuthClientInterface;
use Modules\Notes\Domain\Events\NoteCreated;
use Modules\Notes\Domain\Repositories\NoteRepositoryInterface;
use Modules\Notes\Infrastructure\Queue\ProcessNoteAnalytics;


class CreateNoteHandler
{
    public function __construct(
        private NoteRepositoryInterface $repo,
        private AuthClientInterface $authClient,
        private CacheInterface $cache,
        private EventDispatcherInterface $events,
    ) {}

    public function handle(CreateNoteCommand $command)
    {

        $note = $this->repo->create([
            'title' => $command->title,
            'content' => $command->content,
            'user_id' => $command->userId,
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
