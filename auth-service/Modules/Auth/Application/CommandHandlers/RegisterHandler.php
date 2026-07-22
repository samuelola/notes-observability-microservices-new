<?php

namespace Modules\Auth\Application\CommandHandlers;

use Modules\Auth\Application\Commands\RegisterCommand;
use Modules\Auth\Application\Contracts\EventPublisherInterface;
use Modules\Auth\Domain\Repositories\AuthRepositoryInterface;

class RegisterHandler
{
    public function __construct(
        private AuthRepositoryInterface $repo,
        private EventPublisherInterface $publisher,

    ) {}

    public function handle(RegisterCommand $command)
    {

        $user = $this->repo->createUser($command);
        $this->publisher->publish('user.registered', [

            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,

        ]);

        return $user;
    }
}
