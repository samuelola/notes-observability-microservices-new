<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Modules\Notes\Application\Contracts\EventDispatcherInterface;
use Modules\Notes\Application\Contracts\ImageStorageInterface;
use Modules\Notes\Domain\Contracts\AuthClientInterface;
use Modules\Notes\Domain\Events\NoteCreated;

uses(RefreshDatabase::class);

it('creates a note successfully with an image', function () {

    /*
     * Mock authentication
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
     * We don't want this test to actually upload
     * anything to AWS S3.
     */
    $imageStorage = Mockery::mock(ImageStorageInterface::class);

    $imageStorage
        ->shouldReceive('store')
        ->once()
        ->with(
            Mockery::type(UploadedFile::class),
            'notes/10'
        )
        ->andReturn('notes/10/test-image.jpg');

    $imageStorage
        ->shouldReceive('temporaryUrl')
        ->once()
        ->with(
            'notes/10/test-image.jpg',
            10
        )
        ->andReturn(
            'https://example.com/temporary/test-image.jpg'
        );    

    $this->app->instance(
        ImageStorageInterface::class,
        $imageStorage
    );
    

    /*
     * Mock event dispatcher
     */
    $events = Mockery::mock(EventDispatcherInterface::class);

    $events
        ->shouldReceive('dispatch')
        ->once()
        ->with(Mockery::type(NoteCreated::class));

    $this->app->instance(
        EventDispatcherInterface::class,
        $events
    );

    /*
     * Fake uploaded image
     */
    $image = UploadedFile::fake()->image(
        'test-image.jpg',
        500,
        500
    );

    /*
     * Use multipart/form-data instead of postJson()
     *
     * postJson() cannot upload an actual file.
     */
    $response = $this->post(
        '/api/v1/notes',
        [
            'title' => 'Shopping List',
            'content' => 'Buy milk',
            'image' => $image,
        ],
        [
            'Authorization' => 'Bearer fake-token',
            'Accept' => 'application/json',
        ]
    );

    $response
        ->assertCreated()
        ->assertJson([
            'status' => 'success',
        ]);

    /*
     * Verify the database contains the S3 path.
     */
    $this->assertDatabaseHas('notes', [
        'title' => 'Shopping List',
        'content' => 'Buy milk',
        'user_id' => 10,
        'image_path' => 'notes/10/test-image.jpg',
    ]);
});
