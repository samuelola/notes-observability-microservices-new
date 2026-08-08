<?php

namespace Modules\Notes\Infrastructure\External;

use Illuminate\Support\Facades\Http;
use Modules\Notes\Domain\Contracts\AuthClientInterface;

class HttpAuthClient implements AuthClientInterface
{
     public function userFromToken(string $token)
    {
        
        $response = Http::withToken($token)
            ->acceptJson()
            ->get(
                config('services.auth.url').'/api/internal/me'
            );

        if (! $response->successful()) {
            return null;
        }

        return $response->json();
    }

    
}