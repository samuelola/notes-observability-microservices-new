<?php

namespace Modules\Auth\Presentation\Swagger;

use OpenApi\Attributes as OA;

final class AuthApi
{
    #[OA\Post(
        path: '/api/v1/register',
        summary: 'Register a new user',
        tags: ['Authentication']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: '#/components/schemas/RegisterRequest')
    )]
    #[OA\Response(
        response: 201,
        description: 'User registered successfully'
    )]
    #[OA\Response(
        response: 422,
        description: 'Validation error'
    )]
    public function register() {}

    #[OA\Post(
        path: '/api/v1/login',
        summary: 'Login user',
        tags: ['Authentication']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: '#/components/schemas/LoginRequest')
    )]
    #[OA\Response(
        response: 200,
        description: 'Login successful'
    )]
    #[OA\Response(
        response: 401,
        description: 'Invalid credentials'
    )]
    public function login() {}
}
