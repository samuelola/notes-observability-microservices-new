<?php

namespace Modules\Notes\Presentation\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class NoteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'content' => $this->content,
            'image_path' => $this->image_path,
            'image_url' => $this->image_path
            ? Storage::disk('s3')->temporaryUrl(
                $this->image_path,
                now()->addMinutes(10)
            )
            : null,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
