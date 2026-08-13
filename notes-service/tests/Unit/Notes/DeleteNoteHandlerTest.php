<?php

use Modules\Notes\Application\CommandHandlers\DeleteNoteHandler;
use Modules\Notes\Application\Commands\DeleteNoteCommand;
use Modules\Notes\Application\Contracts\CacheInterface;
use Modules\Notes\Application\Contracts\ImageStorageInterface;
use Modules\Notes\Domain\Contracts\AuthClientInterface;
use Modules\Notes\Domain\Repositories\NoteRepositoryInterface;

it('deletes a note and its image successfully', function () {

    $repo = Mockery::mock(NoteRepositoryInterface::class);

    $repo
        ->shouldReceive('findForUser')
        ->once()
        ->with(5, 10)
        ->andReturn((object) [
            'id' => 5,
            'user_id' => 10,
            'image_path' => 'notes/10/test-image.jpg',
        ]);

    $repo
        ->shouldReceive('delete')
        ->once()
        ->with(5, 10)
        ->andReturn(true);

    $cache = Mockery::mock(CacheInterface::class);

    $cache
        ->shouldReceive('forget')
        ->once()
        ->with('notes:user:10');

    $imageStorage = Mockery::mock(ImageStorageInterface::class);

    $imageStorage
        ->shouldReceive('delete')
        ->once()
        ->with('notes/10/test-image.jpg')
        ->andReturn(true);

    $authClient = Mockery::mock(AuthClientInterface::class);

    $handler = new DeleteNoteHandler(
        $repo,
        $authClient,
        $cache,
        $imageStorage
    );

    $command = new DeleteNoteCommand(
        noteId: 5,
        userId: 10
    );

    $result = $handler->handle($command);

    expect($result)->toBeTrue();
});
