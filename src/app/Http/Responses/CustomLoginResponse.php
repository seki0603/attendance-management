<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class CustomLoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = auth()->user();

        if ($user->role === 'admin') {
            return redirect()->intended('/admin/attendance/list');
        }

        return redirect()->intended('/attendance');
    }
}
