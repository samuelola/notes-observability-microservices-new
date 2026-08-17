<?php

namespace Modules\Notes\Application\QueryHandlers;

use Illuminate\Support\Facades\Cache;
use Modules\Notes\Application\Queries\GetNoteQuery;
use Modules\Notes\Domain\Contracts\AuthClientInterface;
use Modules\Notes\Domain\Repositories\NoteRepositoryInterface;

class GetNoteHandler
{
    public function __construct(

        private NoteRepositoryInterface $repo,
        private AuthClientInterface $authClient,
    ) {}

    public function handle(GetNoteQuery $query)
    {
        // return Cache::remember(
        //     "notes:user:{$query->userId}",
        //     now()->addMinutes(10),
        //     fn () => $this->repo->findForUser(
        //         $query->id,
        //         $query->userId
        //     )
        // );

        return $this->repo->findForUser(
            $query->id,
            $query->userId
        );
    }
}
