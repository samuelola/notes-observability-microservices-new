<?php

namespace Modules\Notes\Presentation\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Notes\Application\Contracts\ImageStorageInterface;

class NoteResource extends JsonResource
{
    public function toArray(Request $request): array
    {

        $imageStorage = app(ImageStorageInterface::class);

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'content' => $this->content,
            'image_path' => $this->image_path,
            'image_url' => $this->image_path
                ? $imageStorage->temporaryUrl(
                    $this->image_path,
                    10
                )
                : null,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
