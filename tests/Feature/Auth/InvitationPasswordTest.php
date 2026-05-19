<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Support\InvitationPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class InvitationPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_invitation_token_does_not_expire_after_default_reset_window(): void
    {
        $user = User::factory()->create();
        $token = InvitationPassword::createToken($user);

        DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->update(['created_at' => Carbon::now()->subHours(2)]);

        $this->assertTrue(InvitationPassword::tokenExists($user, $token));
    }

    public function test_forgot_password_token_still_expires(): void
    {
        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->update(['created_at' => Carbon::now()->subHours(2)]);

        $this->assertFalse(Password::broker()->tokenExists($user, $token));
    }

    public function test_user_can_set_password_with_invitation_token(): void
    {
        $user = User::factory()->create();
        $token = InvitationPassword::createToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect(route('login'));

        $user->refresh();
        $this->assertTrue(Hash::check('new-password-123', $user->password));
    }
}
