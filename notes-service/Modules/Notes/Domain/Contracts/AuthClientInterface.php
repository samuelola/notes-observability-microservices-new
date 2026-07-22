<?php

namespace Modules\Notes\Domain\Contracts;

interface AuthClientInterface
{
    public function userFromToken(string $token);

    // public function userExists(int $userId);
}
