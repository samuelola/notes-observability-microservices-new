<?php

namespace Modules\Auth\Application\Commands;

class RegisterCommand
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public ?string $correlationId = null,
    ) {}

}
