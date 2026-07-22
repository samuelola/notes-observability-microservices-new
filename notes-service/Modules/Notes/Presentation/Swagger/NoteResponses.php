<?php

namespace Modules\Notes\Presentation\Swagger;

use OpenApi\Attributes as OA;

#[OA\Response(
    response: 'NoteCreated',
    description: 'Note created successfully',
    content: new OA\JsonContent(
        properties: [
            new OA\Property(
                property: 'status',
                type: 'string',
                example: 'success'
            ),
            new OA\Property(
                property: 'message',
                type: 'string',
                example: 'Note created successfully'
            ),
            new OA\Property(
                property: 'data',
                ref: '#/components/schemas/Note'
            ),
        ]
    )
)]
#[OA\Response(
    response: 'NoteRetrieved',
    description: 'Note retrieved successfully',
    content: new OA\JsonContent(
        properties: [
            new OA\Property(
                property: 'status',
                type: 'string',
                example: 'success'
            ),
            new OA\Property(
                property: 'message',
                type: 'string',
                example: 'Note retrieved successfully'
            ),
            new OA\Property(
                property: 'data',
                ref: '#/components/schemas/Note'
            ),
        ]
    )
)]
#[OA\Response(
    response: 'NoteUpdated',
    description: 'Note updated successfully',
    content: new OA\JsonContent(
        properties: [
            new OA\Property(
                property: 'status',
                type: 'string',
                example: 'success'
            ),
            new OA\Property(
                property: 'message',
                type: 'string',
                example: 'Note updated successfully'
            ),
            new OA\Property(
                property: 'data',
                ref: '#/components/schemas/Note'
            ),
        ]
    )
)]
#[OA\Response(
    response: 'NoteDeleted',
    description: 'Note deleted successfully',
    content: new OA\JsonContent(
        properties: [
            new OA\Property(
                property: 'status',
                type: 'string',
                example: 'success'
            ),
            new OA\Property(
                property: 'message',
                type: 'string',
                example: 'Note deleted successfully'
            ),
            new OA\Property(
                property: 'data',
                type: 'object',
                example: new \stdClass
            ),
        ]
    )
)]
#[OA\Response(
    response: 'NotesList',
    description: 'List of notes',
    content: new OA\JsonContent(
        properties: [
            new OA\Property(
                property: 'status',
                type: 'string',
                example: 'success'
            ),
            new OA\Property(
                property: 'message',
                type: 'string',
                example: 'Notes retrieved successfully'
            ),
            new OA\Property(
                property: 'data',
                type: 'array',
                items: new OA\Items(
                    ref: '#/components/schemas/Note'
                )
            ),
            new OA\Property(
                property: 'pagination',
                properties: [
                    new OA\Property(property: 'current_page', type: 'integer', example: 1),
                    new OA\Property(property: 'last_page', type: 'integer', example: 3),
                    new OA\Property(property: 'per_page', type: 'integer', example: 10),
                    new OA\Property(property: 'total', type: 'integer', example: 24),
                ],
                type: 'object'
            ),
        ]
    )
)]
#[OA\Response(
    response: 'Unauthorized',
    description: 'Unauthenticated',
    content: new OA\JsonContent(
        properties: [
            new OA\Property(
                property: 'message',
                type: 'string',
                example: 'Unauthenticated'
            ),
        ]
    )
)]
#[OA\Response(
    response: 'NotFound',
    description: 'Resource not found',
    content: new OA\JsonContent(
        properties: [
            new OA\Property(
                property: 'message',
                type: 'string',
                example: 'Note not found'
            ),
        ]
    )
)]
#[OA\Response(
    response: 'ValidationError',
    description: 'Validation failed',
    content: new OA\JsonContent(
        properties: [
            new OA\Property(
                property: 'message',
                type: 'string',
                example: 'The given data was invalid.'
            ),
            new OA\Property(
                property: 'errors',
                type: 'object'
            ),
        ]
    )
)]
class NoteResponses {}
