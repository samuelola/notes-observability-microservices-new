<?php

namespace Modules\Auth\Presentation\Swagger;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Auth Service API',
    description: 'Authentication microservice'
)]
#[OA\Server(
    url: 'http://localhost:8000',
    description: 'Local server'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Sanctum'
)]
final class OpenApiSpec
{
    public const VERSION = '1.0';
}
