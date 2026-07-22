<?php

namespace Modules\Auth\Presentation\Http\Controllers\Internal;

use Illuminate\Http\Request;
use Modules\Auth\Presentation\Http\Controllers\controller;

class UserController extends controller
{
    public function me(Request $request)
    {
        return response()->json([
            'id' => $request->user()->id,
            'name' => $request->user()->name,
            'email' => $request->user()->email,
        ]);

    }
}
