<?php

namespace Modules\Notes\Presentation\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class NoteCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection->map(fn ($note) => [
                'id' => $note->id,
                'user_id' => $note->user_id,
                'title' => $note->title,
                'content' => $note->content,
                'image_path' => $this->image_path,
                'created_at' => $note->created_at?->toDateTimeString(),
            ]),
        ];
    }
}
