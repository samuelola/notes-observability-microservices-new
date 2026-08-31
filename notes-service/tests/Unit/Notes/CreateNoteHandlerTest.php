<?php

use Illuminate\Http\UploadedFile;
use Modules\Notes\Application\CommandHandlers\CreateNoteHandler;
use Modules\Notes\Application\Commands\CreateNoteCommand;
use Modules\Notes\Application\Contracts\CacheInterface;
use Modules\Notes\Application\Contracts\EventDispatcherInterface;
use Modules\Notes\Application\Contracts\EventPublisherInterface;
use Modules\Notes\Application\Contracts\ImageStorageInterface;
use Modules\Notes\Domain\Contracts\AuthClientInterface;
use Modules\Notes\Domain\Events\NoteCreated;
use Modules\Notes\Domain\Repositories\NoteRepositoryInterface;

it('creates a note, clears the cache and dispatches an event', function () {

    $image = UploadedFile::fake()->create(
        'new-image.jpg',
        100,
        'image/jpeg'
    );

    $note = (object) [
        'id' => 1,
        'title' => 'My first note',
        'content' => 'Hello world',
        'user_id' => 10,
        'image_path' => 'notes/10/test-image.jpg',
    ];

    $repo = Mockery::mock(NoteRepositoryInterface::class);

    $repo->shouldReceive('create')
        ->once()
        ->with([
            'title' => 'My first note',
            'content' => 'Hello world',
            'user_id' => 10,
            'image_path' => 'notes/10/test-image.jpg',
        ])
        ->andReturn($note);

    $authClient = Mockery::mock(AuthClientInterface::class);

    $cache = Mockery::mock(CacheInterface::class);

    $cache->shouldReceive('tags')
        ->once()
        ->with('notes:user:10');

    /*
     * Mock S3 image storage
     */
    $imageStorage = Mockery::mock(ImageStorageInterface::class);

    $imageStorage->shouldReceive('store')
        ->once()
        ->with(
            $image,
            'notes/10'
        )
        ->andReturn('notes/10/test-image.jpg');

    /*
     * Mock events
     */
    $events = Mockery::mock(EventDispatcherInterface::class);

    $events->shouldReceive('dispatch')
        ->once()
        ->with(Mockery::type(NoteCreated::class));

    $publisher = Mockery::mock(EventPublisherInterface::class);

    $publisher
        ->shouldReceive('publish')
        ->once()
        ->with(
            'note.created',
            Mockery::type('array')
        );

    /*
     * IMPORTANT:
     * ImageStorageInterface is now the 5th dependency.
     */
    $handler = new CreateNoteHandler(
        $repo,
        $authClient,
        $cache,
        $events,
        $imageStorage,
        $publisher
    );

    $command = new CreateNoteCommand(
        title: 'My first note',
        content: 'Hello world',
        userId: 10,
        image: $image,
    );

    $result = $handler->handle($command);

    expect($result)->toBe($note);
});
