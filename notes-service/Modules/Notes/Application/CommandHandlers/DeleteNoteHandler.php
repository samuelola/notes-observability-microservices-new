<?php

namespace Modules\Notes\Application\CommandHandlers;

use Modules\Notes\Application\Commands\DeleteNoteCommand;
use Modules\Notes\Application\Contracts\CacheInterface;
use Modules\Notes\Domain\Contracts\AuthClientInterface;
use Modules\Notes\Domain\Repositories\NoteRepositoryInterface;

class DeleteNoteHandler
{
    public function __construct(
        private NoteRepositoryInterface $repo,
        private AuthClientInterface $authClient,
        private CacheInterface $cache
    ) {}

    public function handle(DeleteNoteCommand $command)
    {
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
