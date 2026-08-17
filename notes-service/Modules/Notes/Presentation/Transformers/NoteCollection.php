<?php

namespace Modules\Notes\Presentation\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Storage;
use Modules\Notes\Application\Contracts\ImageStorageInterface;

class NoteCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        $imageStorage = app(ImageStorageInterface::class);
        return [
            'data' => $this->collection->map(fn ($note) => [
                'id' => $note->id,
                'user_id' => $note->user_id,
                'title' => $note->title,
                'content' => $note->content,
                'image_path' => $note->image_path,
                'image_url' => $this->image_path
                ? $imageStorage->temporaryUrl(
                    $this->image_path,
                    10
                )
                : null,
                'created_at' => $note->created_at?->toDateTimeString(),
            ]),
        ];
    }
}
