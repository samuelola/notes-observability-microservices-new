<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Notes\Application\Contracts\ImageStorageInterface;
use Modules\Notes\Domain\Contracts\AuthClientInterface;
use Modules\Notes\Infrastructure\Persistence\Models\Note;
use Mockery;

uses(RefreshDatabase::class);

it('deletes a note and its image successfully', function () {

    /*
     * Fake authenticated user
     */
    $authClient = Mockery::mock(AuthClientInterface::class);

    $authClient
        ->shouldReceive('userFromToken')
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

    /*
     * Mock image storage
     *
     * We don't want the test to actually delete
     * anything from AWS S3.
     */
    $imageStorage = Mockery::mock(ImageStorageInterface::class);

    $imageStorage
        ->shouldReceive('delete')
        ->once()
        ->with('notes/10/test-image.jpg')
        ->andReturn(true);

    $this->app->instance(
        ImageStorageInterface::class,
        $imageStorage
    );

    /*
     * Create an existing note with an image
     */
    $note = Note::create([
        'title' => 'Shopping List',
        'content' => 'Buy milk',
        'user_id' => 10,
        'image_path' => 'notes/10/test-image.jpg',
    ]);

    /*
     * Delete the note
     */
    $response = $this->deleteJson(
        "/api/v1/notes/{$note->id}",
        [],
        [
            'Authorization' => 'Bearer fake-token',
        ]
    );

    /*
     * Verify API response
     */
    $response
        ->assertOk()
        ->assertJson([
            'status' => 'success',
        ]);

    /*
     * Verify the image storage was asked to delete
     * the correct S3 object.
     */
    $imageStorage
        ->shouldHaveReceived('delete')
        ->once()
        ->with('notes/10/test-image.jpg');

    /*
     * Verify the note was deleted from the database
     */
    $this->assertDatabaseMissing('notes', [
        'id' => $note->id,
    ]);
});

