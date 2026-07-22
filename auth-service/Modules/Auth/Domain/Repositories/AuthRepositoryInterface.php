<?php

namespace Modules\Auth\Domain\Repositories;

interface AuthRepositoryInterface
{
    public function findUser($userId);

    public function createUser($registerdto);

    public function loginUser($logindto);
}
