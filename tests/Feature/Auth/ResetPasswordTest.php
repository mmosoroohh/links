<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class ResetPasswordTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @param $token
     * @return void
     */
    protected function passwordResetGetRoute($token)
    {
        return route('password.reset', $token);
    }

    protected function getValidToken($user)
    {
        return Password::broker()->createToken($user);
    }

    protected function getInvalidToken()
    {
        return 'invalid-token';
    }

    protected function successfulPasswordResetRoute()
    {
        return route('home');
    }

    public function test_user_can_view_a_password_reset_form()
    {
        $user = factory(User::class)->create();

        $response = $this->get($this->passwordResetGetRoute($token = $this->getInvalidToken($user)));
        $response->assertSuccessful();
        $response->assertViewIs('auth.passwords.reset');
        $response->assertViewHas('token', $token);
    }

    public function test_user_can_view_a_password_reset_form_when_authenticated()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->get($this->passwordResetGetRoute($token = $this->getInvalidToken($user)));
        $response->assertSuccessful();
        $response->assertViewIs('auth.passwords.reset');
        $response->assertViewHas('token', $token);
    }

    public function test_user_can_reset_password_with_valid_token()
    {
        Event::fake();
        $user = factory(User::class)->create();

        $response = $this->post('/password/reset', [
            'token' => $this->getValidToken($user),
            'email' => $user->email,
            'password' => 'new-awesome-password',
            'password_confirmation' => 'new-awesome-password',
        ]);

        $response->assertRedirect('home');
        $this->assertEquals($user->email, $user->fresh()->email);
        $this->assertTrue(Hash::check('new-awesome-password', $user->refresh()->password));
        $this->assertAuthenticatedAs($user);
        Event::assertDispatched(PasswordReset::class, function ($e) use ($user) {
            return $e->user->id === $user->id;
        });
    }

    public function test_user_cannot_reset_password_with_invalid_token()
    {
        $user = factory(User::class)->create([
            'password' => Hash::make('old-password')
        ]);

        $response = $this->from($this->passwordResetGetRoute($this->getInvalidToken()))->post('/password/reset', [
            'token' => $this->getInvalidToken(),
            'email' => $user->email,
            'password' => 'new-awesome-password',
            'password_confirmation' => 'new-awesome-password'
        ]);

        $response->assertRedirect($this->passwordResetGetRoute($this->getInvalidToken()));
        $this->assertEquals($user->email, $user->refresh()->email);
        $this->assertTrue(Hash::check('old-password', $user->refresh()->password));
        $this->assertGuest();
    }

    public function test_user_cannot_reset_password_without_providing_a_new_password()
    {
        $user = factory(User::class)->create([
            'password' => Hash::make('old-password')
        ]);

        $response = $this->from($this->passwordResetGetRoute($token = $this->getValidToken($user)))->post('/password/reset', [
            'token' => $token,
            'email' => $user->email,
            'password' => '',
            'password_confirmation' => ''
        ]);

        $response->assertRedirect($this->passwordResetGetRoute($token));
        $response->assertSessionHasErrors('password');
        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertEquals($user->email, $user->fresh()->email);
        $this->assertTrue(Hash::check('old-password', $user->refresh()->password));
        $this->assertGuest();
    }

    public function test_user_cannot_reset_password_without_providing_an_email()
    {
        $user = factory(User::class)->create([
            'password' => Hash::make('old-password')
        ]);

        $response = $this->from($this->passwordResetGetRoute($token = $this->getValidToken($user)))->post('/password/reset', [
            'token' => $token,
            'email' => '',
            'password' => 'new-awesome-password',
            'password_confirmation' => 'new-awesome-password'
        ]);

        $response->assertRedirect($this->passwordResetGetRoute($token));
        $response->assertSessionHasErrors('email');
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertEquals($user->email, $user->refresh()->email);
        $this->assertTrue(Hash::check('old-password', $user->refresh()->password));
        $this->assertGuest();
    }
}
