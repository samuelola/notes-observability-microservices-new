<?php

use Illuminate\Http\UploadedFile;
use Modules\Notes\Application\CommandHandlers\UpdateNoteHandler;
use Modules\Notes\Application\Commands\UpdateNoteCommand;
use Modules\Notes\Application\Contracts\CacheInterface;
use Modules\Notes\Application\Contracts\EventDispatcherInterface;
use Modules\Notes\Application\Contracts\ImageStorageInterface;
use Modules\Notes\Domain\Contracts\AuthClientInterface;
use Modules\Notes\Domain\Events\NoteCreated;
use Modules\Notes\Domain\Repositories\NoteRepositoryInterface;

it('updates a note and replaces its image successfully', function () {

    $image = UploadedFile::fake()->create(
        'new-image.jpg',
        100,
        'image/jpeg'
    );

    /*
     * Existing note in database.
     */
    $existingNote = (object) [
        'id' => 5,
        'title' => 'Old title',
        'content' => 'Old content',
        'user_id' => 10,
        'image_path' => 'notes/10/old-image.jpg',
    ];

    /*
     * Note returned after update.
     */
    $updatedNote = (object) [
        'id' => 5,
        'title' => 'Updated title',
        'content' => 'Updated content',
        'user_id' => 10,
        'image_path' => 'notes/10/new-image.jpg',
    ];

    /*
     * Repository.
     */
    $repo = Mockery::mock(NoteRepositoryInterface::class);

    /*
     * Handler first calls findForUser().
     */
    $repo
        ->shouldReceive('findForUser')
        ->once()
        ->with(5, 10)
        ->andReturn($existingNote);

    /*
     * Then handler updates the note.
     */
    $repo
        ->shouldReceive('update')
        ->once()
        ->with(
            5,
            10,
            [
                'title' => 'Updated title',
                'content' => 'Updated content',
                'image_path' => 'notes/10/new-image.jpg',
            ]
        )
        ->andReturn($updatedNote);

    /*
     * Cache.
     */
    $cache = Mockery::mock(CacheInterface::class);

    $cache
        ->shouldReceive('forget')
        ->once()
        ->with('notes:user:10');

    /*
     * Auth client.
     */
    $authClient = Mockery::mock(AuthClientInterface::class);

    /*
     * Image storage.
     */
    $imageStorage = Mockery::mock(ImageStorageInterface::class);

    /*
     * New image gets uploaded.
     */
    $imageStorage
        ->shouldReceive('store')
        ->once()
        ->with(
            $image,
            'notes/10'
        )
        ->andReturn('notes/10/new-image.jpg');

    /*
     * Old image gets deleted.
     */
    $imageStorage
        ->shouldReceive('delete')
        ->once()
        ->with('notes/10/old-image.jpg')
        ->andReturn(true);

    /*
     * Event.
     */
    $events = Mockery::mock(EventDispatcherInterface::class);

    $events
        ->shouldReceive('dispatch')
        ->once()
        ->with(Mockery::type(NoteCreated::class));

    /*
     * Handler.
     */
    $handler = new UpdateNoteHandler(
        $repo,
        $authClient,
        $cache,
        $events,
        $imageStorage
    );

    /*
     * Command.
     */
    $command = new UpdateNoteCommand(
        noteId: 5,
        userId: 10,
        title: 'Updated title',
        content: 'Updated content',
        image: $image
    );

    /*
     * Execute.
     */
    $result = $handler->handle($command);

    /*
     * Verify.
     */
    expect($result)->toBe($updatedNote);
});
