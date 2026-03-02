<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;

class LogoutResponse implements LogoutResponseContract
{
    public function toResponse($request)
    {
        $user = (int) $request->user();

        return redirect(
            $user && $user->role === User::ROLE_ADMIN
                ? '/admin/login'
                : '/login'
        );
    }
}