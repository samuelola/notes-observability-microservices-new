<?php

namespace Modules\Notes\Infrastructure\External;

use Illuminate\Support\Facades\Http;
use Modules\Notes\Domain\Contracts\AuthClientInterface;
use Modules\Notes\Infrastructure\Tracing\TraceManager;

class HttpAuthClient implements AuthClientInterface
{
    public function __construct(
        private TraceManager $trace,
    ) {}

    public function userFromToken(string $token)
    {
        return $this->trace->span(
            'HTTP GET Auth Service',
            function () use ($token) {

                $response = Http::withToken($token)
                    ->acceptJson()
                    ->get(
                        config('services.auth.url').'/api/internal/me'
                    );

                if (! $response->successful()) {
                    return null;
                }

                return $response->json();

            },
            [
                'http.method' => 'GET',
                'http.url' => config('services.auth.url').'/api/internal/me',
                'http.host' => parse_url(config('services.auth.url'), PHP_URL_HOST),
            ]
        );
    }
}
