<?php

use Modules\Auth\Application\CommandHandlers\LoginHandler;
use Modules\Auth\Application\Commands\LoginCommand;
use Modules\Auth\Application\Contracts\EventPublisherInterface;
use Modules\Auth\Domain\Repositories\AuthRepositoryInterface;

it('logs in successfully', function () {

    $user = (object) [
        'id' => 1,
        'name' => 'john',
        'email' => 'john@example.com',
    ];

    $repo = Mockery::mock(AuthRepositoryInterface::class);

    $repo
        ->shouldReceive('loginUser')
        ->once()
        ->andReturn($user);

    $publisher = Mockery::mock(EventPublisherInterface::class);

    $publisher
        ->shouldReceive('publish')
        ->once()
        ->with(
            'auth.loggedin',
            Mockery::type('array')
        );

    $handler = new LoginHandler(
        $repo,
        $publisher
    );

    $result = $handler->handle(
        new LoginCommand(
            'john@example.com',
            'password'
        )
    );

    expect($result)->toBe($user);
});
