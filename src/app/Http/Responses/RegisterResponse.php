<?php

namespace App\Http\Responses;

use App\Models\User;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
{
    public function toResponse($request)
    {
        $user = auth()->user();

        if ($user->role === User::ROLE_ADMIN) {
            return redirect()->intended('/admin/attendance/list');
        }

        return redirect()->intended('/attendance');
    }
}