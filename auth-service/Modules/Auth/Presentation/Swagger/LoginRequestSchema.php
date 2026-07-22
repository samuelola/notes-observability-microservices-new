<?php

namespace Modules\Auth\Presentation\Swagger;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'LoginRequest',
    required: ['email', 'password']
)]
final class LoginRequestSchema
{
    #[OA\Property(example: 'john@example.com')]
    public string $email;

    #[OA\Property(example: 'password123')]
    public string $password;
}
