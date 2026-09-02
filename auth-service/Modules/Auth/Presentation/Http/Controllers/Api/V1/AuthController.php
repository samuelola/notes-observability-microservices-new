<?php

namespace Modules\Auth\Presentation\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Auth\Application\CommandHandlers\LoginHandler;
use Modules\Auth\Application\CommandHandlers\RegisterHandler;
use Modules\Auth\Application\Commands\LoginCommand;
use Modules\Auth\Application\Commands\RegisterCommand;
use Modules\Auth\Application\DTOs\CreateAuthDTO;
use Modules\Auth\Application\DTOs\LoginAuthDTO;
use Modules\Auth\Infrastructure\Tracing\OpenTelemetryTracer;
use Modules\Auth\Presentation\Http\Controllers\Controller;
use Modules\Auth\Presentation\Http\Requests\LoginRequest;
use Modules\Auth\Presentation\Http\Requests\RegisterRequest;

class AuthController extends Controller
{
    // REGISTER
    public function register(RegisterRequest $request, RegisterHandler $handler)
    {

        $dto = CreateAuthDTO::fromArray(
            $request->validated()
        );

        $newdto = new RegisterCommand(
            $dto->name,
            $dto->email,
            $dto->password,
            $request->header('X-Correlation-ID')
        );

        $user = $handler->handle($newdto);

        Log::info('auth.regisiter.success', [
            'service' => 'auth',
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'User registered successfully',
            'user' => $user,

        ], 201);
    }

    // LOGIN
    public function login(LoginRequest $request, LoginHandler $handler)
    {
        // $start = microtime(true);
        // Log::info('Controller started');

        $logindto = LoginAuthDTO::fromArray(
            $request->validated()
        );

        $newlogindto = new LoginCommand(
            $logindto->email,
            $logindto->password,
            $request->header('X-Correlation-ID')
        );

        $user = $handler->handle($newlogindto);

        $token = $user->createToken('notes-token')->plainTextToken;

        Log::info('auth.login.success', [
            'service' => 'auth',
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        // Log::info('Controller finished', [
        //     'time_ms' => (microtime(true) - $start) * 1000,
        // ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    // LOGOUT
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully',
        ], 200);
    }

    public function metrics(Request $request)
    {
        return response('
            # HELP app_up
            # TYPE app_up gauge
            app_up 1
            ', 200)->header('Content-Type', 'text/plain');
    }

    // public function test(OpenTelemetryTracer $otel)
    // {
    //     $tracer = $otel->tracer();

    //     $span = $tracer
    //         ->spanBuilder('Test Span')
    //         ->startSpan();

    //     sleep(1);

    //     $span->end();

    //     $otel->shutdown();

    //     return response()->json([
    //         'status' => 'sent'
    //     ]);
    // }
}
