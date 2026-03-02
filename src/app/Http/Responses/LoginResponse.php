<?php

namespace App\Http\Responses;

use App\Models\User;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        if (auth()->user()->role === User::ROLE_ADMIN) {
            return redirect()->intended('/admin/attendance/list');
        }

        return redirect()->intended('/attendance');
    }
}