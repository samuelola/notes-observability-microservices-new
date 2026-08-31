<?php

namespace Modules\Email\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

class EmailUser extends Model
{
    protected $table = 'email_users';

    protected $guarded = [];
}
