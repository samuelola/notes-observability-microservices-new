<?php

namespace Modules\Notes\Presentation\Swagger;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateNoteRequest',
    required: ['title', 'content']
)]
class UpdateNoteRequestSchema
{
    #[OA\Property(
        property: 'title',
        type: 'string',
        example: 'Updated Shopping List'
    )]
    public string $title;

    #[OA\Property(
        property: 'content',
        type: 'string',
        example: 'Buy milk and bread'
    )]
    public string $content;
}
