<?php

namespace Modules\Notes\Infrastructure\External;

use Modules\Notes\Application\DTOs\UserDTO;

class LocalAuthClient
{
    // public function getUser(int $userId)
    // {

    //     $user = User::find($userId);

    //     if (! $user) {
    //         return null;
    //     }

    //     return UserDTO::fromArray($user);

    //     return [
    //         'id' => $user->id,
    //         'name' => $user->name,
    //         'email' => $user->email,
    //     ];
    // }

    // public function userExists(int $userId): bool
    // {
    //     return User::where('id', $userId)->exists();
    // }
}
