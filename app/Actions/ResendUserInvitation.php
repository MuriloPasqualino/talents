<?php

namespace App\Actions;

use App\Mail\UserInvitationMail;
use App\Models\Company;
use App\Models\User;
use App\Support\InvitationPassword;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;

class ResendUserInvitation
{
    public function execute(User $user, ?Company $company = null): void
    {
        if ($user->hasCompletedRegistration()) {
            throw new InvalidArgumentException('Este utilizador já concluiu o cadastro da senha.');
        }

        $token = InvitationPassword::createToken($user);
        $resetUrl = InvitationPassword::setPasswordUrl($user, $token);

        Mail::to($user->email)->send(new UserInvitationMail($user, $company, $resetUrl));
    }
}
