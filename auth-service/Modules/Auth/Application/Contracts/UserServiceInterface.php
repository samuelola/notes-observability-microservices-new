<?php

namespace Modules\Auth\Application\Contracts;

interface UserServiceInterface
{
    public function exists($userId);

    public function Create($registerdto);

    public function Login($logindto);
}
