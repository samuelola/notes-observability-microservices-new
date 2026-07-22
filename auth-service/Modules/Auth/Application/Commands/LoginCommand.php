<?php

namespace Modules\Auth\Application\Commands;

class LoginCommand
{
    public function __construct(
        public string $email,
        public string $password
    ) {}

}
