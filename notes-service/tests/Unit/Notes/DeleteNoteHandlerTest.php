<?php

use Modules\Notes\Application\CommandHandlers\DeleteNoteHandler;
use Modules\Notes\Application\Commands\DeleteNoteCommand;
use Modules\Notes\Application\Contracts\CacheInterface;
use Modules\Notes\Domain\Contracts\AuthClientInterface;
use Modules\Notes\Domain\Repositories\NoteRepositoryInterface;

it('deletes a note and clears the cache', function () {

    $repo = Mockery::mock(NoteRepositoryInterface::class);

    $repo->shouldReceive('delete')
        ->once()
        ->with(
            5,      // note id
            10      // user id
        )
        ->andReturn(true);

    $cache = Mockery::mock(CacheInterface::class);

    $cache->shouldReceive('forget')
        ->once()
        ->with('notes:user:10');

    $authClient = Mockery::mock(AuthClientInterface::class);

    $handler = new DeleteNoteHandler(
        $repo,
        $authClient,
        $cache
    );

    $command = new DeleteNoteCommand(
        noteId: 5,
        userId: 10
    );

    $result = $handler->handle($command);

    expect($result)->toBeTrue();
});
