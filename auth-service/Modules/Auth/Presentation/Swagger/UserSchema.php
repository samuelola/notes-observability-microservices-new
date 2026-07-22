<?php

namespace Modules\Auth\Presentation\Swagger;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'User')]
final class UserSchema
{
    #[OA\Property(example: 1)]
    public int $id;

    #[OA\Property(example: 'John Doe')]
    public string $name;

    #[OA\Property(example: 'john@example.com')]
    public string $email;
}
