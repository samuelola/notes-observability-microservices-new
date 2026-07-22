<?php

namespace Modules\Notes\Presentation\Swagger;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CreateNoteRequest',
    required: ['title', 'content']
)]
class CreateNoteRequestSchema
{
    #[OA\Property(
        property: 'title',
        type: 'string',
        example: 'Shopping List'
    )]
    public string $title;

    #[OA\Property(
        property: 'content',
        type: 'string',
        example: 'Buy milk'
    )]
    public string $content;
}
