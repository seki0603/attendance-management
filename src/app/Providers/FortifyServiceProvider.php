<?php

namespace App\Providers;

use App\Models\User;
use App\Http\Requests\LoginRequest;
use App\Http\Responses\CustomLoginResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class FortifyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->bind(FortifyLoginRequest::class, LoginRequest::class);
        Fortify::username('email');

        Fortify::authenticateUsing(function (LoginRequest $request) {
            $user = User::where('email', $request->email)->first();

            if (! $user || ! Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    Fortify::username() => [__('auth.failed')],
                ]);
            }

            return $user;
        });
    }

    public function register()
    {
        $this->app->singleton(LoginResponseContract::class, CustomLoginResponse::class);
    }
}
