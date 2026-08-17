<?php

namespace Modules\Notes\Application\QueryHandlers;

use Illuminate\Support\Facades\Cache;
use Modules\Notes\Application\Queries\GetUserNotesQuery;
use Modules\Notes\Domain\Contracts\AuthClientInterface;
use Modules\Notes\Domain\Repositories\NoteRepositoryInterface;
use Illuminate\Support\Facades\Storage;

class GetUsersNoteHandler
{
    public function __construct(

        private NoteRepositoryInterface $repo,
        private AuthClientInterface $authClient,
    ) {}

    public function handle(GetUserNotesQuery $query)
    {
        $cacheKey = "notes:user:{$query->userId}:page:{$query->page}";

        return Cache::remember($cacheKey, now()->addMinutes(3), function () use ($query) {

            $paginator = $this->repo->allUserNotes(
                $query->userId,
                $query->page
            );

            return [
                'items' => collect($paginator->items())
                    ->map(function ($note) {
                        return [
                            'id' => $note->id,
                            'title' => $note->title,
                            'content' => $note->content,
                            'user_id' => $note->user_id,
                            'image_path' => $note->image_path,
                            'image_url' => $note->image_path
                            ? Storage::disk('s3')->temporaryUrl(
                                $note->image_path,
                                now()->addMinutes(10)
                            )
                            : null,
                            'created_at' => optional($note->created_at)->toDateTimeString(),
                            'updated_at' => optional($note->updated_at)->toDateTimeString(),
                        ];
                    })
                    ->toArray(),

                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'last_page' => $paginator->lastPage(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                    'has_more_pages' => $paginator->hasMorePages(),
                ],
            ];
        });
    }
}
