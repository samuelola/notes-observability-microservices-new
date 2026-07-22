<?php

use Modules\Notes\Application\CommandHandlers\CreateNoteHandler;
use Modules\Notes\Application\Commands\CreateNoteCommand;
use Modules\Notes\Application\Contracts\CacheInterface;
use Modules\Notes\Application\Contracts\EventDispatcherInterface;
use Modules\Notes\Domain\Contracts\AuthClientInterface;
use Modules\Notes\Domain\Events\NoteCreated;
use Modules\Notes\Domain\Repositories\NoteRepositoryInterface;

it('creates a note, clears the cache and dispatches an event', function () {

    $note = (object) [
        'id' => 1,
        'title' => 'My first note',
        'content' => 'Hello world',
        'user_id' => 10,
    ];

    $repo = Mockery::mock(NoteRepositoryInterface::class);

    $repo->shouldReceive('create')
        ->once()
        ->with([
            'title' => 'My first note',
            'content' => 'Hello world',
            'user_id' => 10,
        ])
        ->andReturn($note);

    $authClient = Mockery::mock(AuthClientInterface::class);
    $cache = Mockery::mock(CacheInterface::class);
    $cache->shouldReceive('forget')
        ->once()
        ->with('notes:user:10');

    $events = Mockery::mock(EventDispatcherInterface::class);
    // mock require expectation
    $events->shouldReceive('dispatch')
        ->once()
        ->with(Mockery::type(NoteCreated::class));

    $handler = new CreateNoteHandler(
        $repo,
        $authClient,
        $cache,
        $events
    );

    $command = new CreateNoteCommand(
        title: 'My first note',
        content: 'Hello world',
        userId: 10,
    );

    $result = $handler->handle($command);

    expect($result)->toBe($note);

});
