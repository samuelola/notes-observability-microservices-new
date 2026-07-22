<?php

namespace Modules\Auth\Infrastructure\Services;

use Illuminate\Support\Facades\Hash;
use Modules\Auth\Application\Contracts\UserServiceInterface;
use Modules\Auth\Infrastructure\Persistence\Models\User;

class UserService implements UserServiceInterface
{
    public function exists($userId)
    {
        return User::where('id', $userId)->exists();
    }

    public function Create($registerdto)
    {
        return User::create([
            'name' => $registerdto->name,
            'email' => $registerdto->email,
            'password' => Hash::make($registerdto->password),
        ]);

    }

    public function Login($logindto)
    {
        $user = User::where('email', $logindto->email)->first();

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
