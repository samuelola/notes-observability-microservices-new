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
        $this->publisher->publish('auth.registered', [

            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'correlation_id' => $command->correlation_id,
        ]);

        return $user;
    }
}
