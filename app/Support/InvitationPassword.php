<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Support\Facades\Password;

final class InvitationPassword
{
    public const BROKER = 'invitations';

    public static function createToken(CanResetPassword $user): string
    {
        return Password::broker(self::BROKER)->createToken($user);
    }

    public static function setPasswordUrl(User $user, string $token): string
    {
        return route('password.reset', ['token' => $token]).'?email='.urlencode($user->email);
    }

    /**
     * @param  array{token: string, email: string, password: string, password_confirmation: string}  $credentials
     */
    public static function reset(array $credentials, callable $callback): string
    {
        return Password::broker(self::BROKER)->reset($credentials, $callback);
    }

    public static function tokenExists(CanResetPassword $user, string $token): bool
    {
        return Password::broker(self::BROKER)->tokenExists($user, $token);
    }
}
