<?php

namespace Modules\Auth\Infrastructure\Persistence\Repositories;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\Domain\Repositories\AuthRepositoryInterface;
use Modules\Auth\Infrastructure\Persistence\Models\User;
use Modules\Auth\Infrastructure\Tracing\TraceManager;

class AuthRepository implements AuthRepositoryInterface
{
    public function __construct(
        private TraceManager $trace,
    ) {}

    public function findUser($userId)
    {
        return User::where('id', $userId)->exists();
    }

    public function createUser($registerdto)
    {
        return User::create([
            'name' => $registerdto->name,
            'email' => $registerdto->email,
            'password' => Hash::make($registerdto->password),
        ]);

    }

    public function loginUser($logindto)
    {

        $user = $this->trace->span(
            'MySQL: Find User',
            function () use ($logindto) {

                return User::where(
                    'email',
                    $logindto->email
                )->first();

            },
            [
                'db.system' => 'mysql',
                'db.operation' => 'SELECT',
                'db.table' => 'users',
            ]
        );

        if (! $user) {
            throw new AuthenticationException('No account exists with this credentials.');
        }

        if (! password_verify($logindto->password, $user->password)) {
            throw new AuthenticationException('Invalid email or password.');
        }

        if (! $user || ! Hash::check($logindto->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // user logs in starting point of their session
        $user->update([
            'last_activity' => now(),
        ]);

        return $user;
    }
}
