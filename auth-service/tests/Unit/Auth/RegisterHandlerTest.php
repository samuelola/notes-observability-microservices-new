<?php

use Modules\Auth\Application\CommandHandlers\RegisterHandler;
use Modules\Auth\Application\Commands\RegisterCommand;
use Modules\Auth\Application\Contracts\EventPublisherInterface;
use Modules\Auth\Domain\Repositories\AuthRepositoryInterface;

it('registers a new user successfully', function () {

    $user = (object) [
        'id' => 1,
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ];

    $command = new RegisterCommand(
        'John Doe',
        'john@example.com',
        'password123',
        'test-correlation-id'
    );

    $repo = Mockery::mock(AuthRepositoryInterface::class);

    $repo
        ->shouldReceive('createUser')
        ->once()
        ->with($command)
        ->andReturn($user);

    $publisher = Mockery::mock(EventPublisherInterface::class);

    $publisher
        ->shouldReceive('publish')
        ->once()
        ->with(
            'auth.registered',
            [
                'id' => 1,
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'correlation_id' => 'test-correlation-id',
            ]
        );

    $handler = new RegisterHandler(
        $repo,
        $publisher
    );

    $result = $handler->handle($command);

    expect($result)->toBe($user);

});
