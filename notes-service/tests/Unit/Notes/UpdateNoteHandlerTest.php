<?php

use Modules\Notes\Application\CommandHandlers\UpdateNoteHandler;
use Modules\Notes\Application\Commands\UpdateNoteCommand;
use Modules\Notes\Application\Contracts\CacheInterface;
use Modules\Notes\Application\Contracts\EventDispatcherInterface;
use Modules\Notes\Domain\Contracts\AuthClientInterface;
use Modules\Notes\Domain\Events\NoteCreated;
use Modules\Notes\Domain\Repositories\NoteRepositoryInterface;

it('updates a note successfully', function () {

    $note = (object) [
        'id' => 5,
        'title' => 'Updated title',
        'content' => 'Updated content',
        'user_id' => 10,
    ];

    $repo = Mockery::mock(NoteRepositoryInterface::class);

    $repo->shouldReceive('update')
        ->once()
        ->with(
            5,
            10,
            [
                'title' => 'Updated title',
                'content' => 'Updated content',
            ]
        )
        ->andReturn($note);

    $cache = Mockery::mock(CacheInterface::class);

    $cache->shouldReceive('forget')
        ->once()
        ->with('notes:user:10');

    $events = Mockery::mock(EventDispatcherInterface::class);
    // mock require expectation
    $events->shouldReceive('dispatch')
        ->once()
        ->with(Mockery::type(NoteCreated::class));

    $authClient = Mockery::mock(AuthClientInterface::class);

    $handler = new UpdateNoteHandler(
        $repo,
        $authClient,
        $cache,
        $events
    );

    $command = new UpdateNoteCommand(
        noteId: 5,
        userId: 10,
        title: 'Updated title',
        content: 'Updated content'
    );

    $result = $handler->handle($command);

    expect($result)->toBe($note);
});
