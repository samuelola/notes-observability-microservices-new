<?php

namespace Modules\Notes\Presentation\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserInactivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {

            // 30 minutes inactivity limit
            if (
                $user->last_activity &&
                now()->diffInMinutes($user->last_activity) >= 30
            ) {

                $user->currentAccessToken()?->delete();

                return response()->json([
                    'status' => 'error',
                    'message' => 'Session expired due to inactivity Login Again.',
                ], 401);
            }

            // Update activity timestamp
            $user->update([
                'last_activity' => now(),
            ]);
        }

        return $next($request);
    }
}
