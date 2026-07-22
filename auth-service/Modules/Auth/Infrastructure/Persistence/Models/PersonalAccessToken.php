<?php

namespace Modules\Auth\Infrastructure\Persistence\Models;

use Laravel\Sanctum\PersonalAccessToken as SanctumToken;

class PersonalAccessToken extends SanctumToken
{
    protected $connection = 'auth_db';
}
