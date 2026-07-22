<?php

namespace Modules\Notes\Presentation\Swagger;

use OpenApi\Attributes as OA;

class NoteApi
{
    #[OA\Get(
        path: '/api/v1/notes',
        summary: "List authenticated user's notes",
        tags: ['Notes'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Response(
        response: 200,
        description: 'List of notes'
    )]

    #[OA\Response(ref: '#/components/responses/Unauthorized', response: 401)]
    public function index() {}

    #[OA\Post(
        path: '/api/v1/notes',
        summary: 'Create a note',
        tags: ['Notes'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            ref: '#/components/schemas/CreateNoteRequest'
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Note created'
    )]
    #[OA\Response(
        response: 422,
        description: 'Validation error'
    )]

    #[OA\Response(ref: '#/components/responses/Unauthorized', response: 401)]
    public function store() {}

    #[OA\Get(
        path: '/api/v1/notes/{id}',
        summary: 'Get a note',
        tags: ['Notes'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Note details'
    )]
    #[OA\Response(
        response: 404,
        description: 'Note not found'
    )]
    public function show() {}

    #[OA\Put(
        path: '/api/v1/notes/{id}',
        summary: 'Update a note',
        tags: ['Notes'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            ref: '#/components/schemas/UpdateNoteRequest'
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Note updated'
    )]

    #[OA\Response(ref: '#/components/responses/NotFound', response: 404)]
    public function update() {}

    #[OA\Delete(
        path: '/api/v1/notes/{id}',
        summary: 'Delete a note',
        tags: ['Notes'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Note deleted'
    )]
    #[OA\Response(
        response: 404,
        description: 'Note not found'
    )]
    public function destroy() {}
}
