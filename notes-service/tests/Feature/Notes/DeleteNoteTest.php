<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Notes\Domain\Contracts\AuthClientInterface;
use Modules\Notes\Infrastructure\Persistence\Models\Note;

uses(RefreshDatabase::class);

it('deletes a note successfully', function () {

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

    // Create an existing note
    $note = Note::create([
        'title' => 'Shopping List',
        'content' => 'Buy milk',
        'user_id' => 10,
    ]);

    // Delete it
    $response = $this->deleteJson(
        "/api/v1/notes/{$note->id}",
        [],
        [
            'Authorization' => 'Bearer fake-token',
        ]
    );

    $response
        ->assertOk()
        ->assertJson([
            'status' => 'success',
        ]);

    // Verify it's gone
    $this->assertDatabaseMissing('notes', [
        'id' => $note->id,
    ]);
});
