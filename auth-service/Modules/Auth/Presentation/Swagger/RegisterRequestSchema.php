<?php

namespace Modules\Auth\Presentation\Swagger;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'RegisterRequest',
    required: ['name', 'email', 'password']
)]
final class RegisterRequestSchema
{
    #[OA\Property(example: 'John Doe')]
    public string $name;

    #[OA\Property(example: 'john@example.com')]
    public string $email;

    #[OA\Property(example: 'password123')]
    public string $password;
}
