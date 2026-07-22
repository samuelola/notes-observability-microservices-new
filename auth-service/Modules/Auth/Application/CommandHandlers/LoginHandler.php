<?php

namespace Modules\Auth\Application\CommandHandlers;

use Modules\Auth\Application\Commands\LoginCommand;
use Modules\Auth\Application\Contracts\EventPublisherInterface;
use Modules\Auth\Domain\Repositories\AuthRepositoryInterface;

class LoginHandler
{
    public function __construct(
        private AuthRepositoryInterface $repo,
        private EventPublisherInterface $publisher
    ) {}

    public function handle(LoginCommand $command)
    {

        $user = $this->repo->loginUser($command);
        $this->publisher->publish('user.loggedin', [

            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,

        ]);

        return $user;

    }
}
