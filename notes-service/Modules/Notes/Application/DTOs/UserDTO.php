<?php

namespace Modules\Notes\Application\DTOs;

class UserDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
    ) {}

    public static function fromArray($user): self
    {
        if ($user === null) {
            throw new \InvalidArgumentException('unauthenticated.');
        }

        return new self(
            $user['id'],
            $user['name'],
            $user['email'],
        );

    }
}
