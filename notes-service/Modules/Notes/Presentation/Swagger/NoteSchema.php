<?php

namespace Modules\Notes\Presentation\Swagger;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Note'
)]
class NoteSchema
{
    #[OA\Property(example: 1)]
    public int $id;

    #[OA\Property(example: 'Shopping List')]
    public string $title;

    #[OA\Property(example: 'Buy milk')]
    public string $content;

    #[OA\Property(example: 10)]
    public int $user_id;

    #[OA\Property(
        type: 'string',
        format: 'date-time',
        example: '2026-07-18T10:00:00Z'
    )]
    public string $created_at;

    #[OA\Property(
        type: 'string',
        format: 'date-time',
        example: '2026-07-18T10:00:00Z'
    )]
    public string $updated_at;
}
