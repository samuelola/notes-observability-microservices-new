<?php

namespace Modules\Notes\Presentation\Http\Middleware;

use Closure;
use Modules\Notes\Application\DTOs\UserDTO;
// use Modules\Notes\Infrastructure\External\HttpAuthClient;
use Modules\Notes\Domain\Contracts\AuthClientInterface;

class AuthenticateWithAuthService
{
    public function __construct(
        private AuthClientInterface $authClient
    ) {}

    public function handle($request, Closure $next)
    {
        $token = $request->bearerToken();

        if (! $token) {

            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);

        }

        try {

            $user = UserDTO::fromArray(
                $this->authClient->userFromToken($token)
            );

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }

        if (! $user) {

            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);

        }

        $request->attributes->set('user', $user);

        return $next($request);
    }
}
