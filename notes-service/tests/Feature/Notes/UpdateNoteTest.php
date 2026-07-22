<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Notes\Application\Contracts\EventDispatcherInterface;
use Modules\Notes\Domain\Contracts\AuthClientInterface;
use Modules\Notes\Domain\Events\NoteCreated;
use Modules\Notes\Infrastructure\Persistence\Models\Note;

uses(RefreshDatabase::class);

it('updates a note successfully', function () {

    // Fake authenticated user
    $authClient = Mockery::mock(AuthClientInterface::class);

    $authClient->shouldReceive('userFromToken')
        ->once()
        ->andReturn([
            'id' => 10,
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

    $this->app->instance(
        AuthClientInterface::class,
        $authClient
    );

    // Mock event dispatcher
    $events = Mockery::mock(EventDispatcherInterface::class);

    $events->shouldReceive('dispatch')
        ->once()
        ->with(Mockery::type(NoteCreated::class));

    $this->app->instance(
        EventDispatcherInterface::class,
        $events
    );

    // Existing note
    $note = Note::create([
        'title' => 'Old title',
        'content' => 'Old content',
        'user_id' => 10,
    ]);

    // Update request
    $response = $this->putJson(
        "/api/v1/notes/{$note->id}",
        [
            'title' => 'New title',
            'content' => 'New content',
        ],
        [
            'Authorization' => 'Bearer fake-token',
        ]
    );

    $response
        ->assertOk()
        ->assertJson([
            'status' => 'success',
        ]);

    // Database assertion
    $this->assertDatabaseHas('notes', [
        'id' => $note->id,
        'title' => 'New title',
        'content' => 'New content',
        'user_id' => 10,
    ]);
});
