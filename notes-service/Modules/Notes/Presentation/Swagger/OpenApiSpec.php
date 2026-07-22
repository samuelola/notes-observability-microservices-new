<?php

namespace Modules\Notes\Presentation\Swagger;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Notes Service API',
    description: 'Notes Microservice'
)]
#[OA\Server(
    url: 'http://localhost:8001',
    description: 'Local Notes Service'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Sanctum'
)]
final class OpenApiSpec {}
