<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_user_can_view_a_registration_form()
    {
        $response = $this->get('/register');

        $response->assertSuccessful();
        $response->assertViewIs('auth.register');
    }

    public function test_user_cannot_view_a_registration_form_when_authenticated()
    {
        $user = factory(User::class)->make();

        $response = $this->actingAs($user)->get('/register');

        $response->assertRedirect('/home');
    }

    public function test_user_can_register()
    {
        Event::fake();

        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'password' => 'i-love-testing-laravel',
            'password_confirmation' => 'i-love-testing-laravel',
        ]);

        $response->assertRedirect('/home');
        $this->assertCount(1, $users = User::all());
        $this->assertAuthenticatedAs($user = $users->first());
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('johndoe@example.com', $user->email);
        $this->assertTrue(Hash::check('i-love-testing-laravel', $user->password));
        Event::assertDispatched(Registered::class, function ($e) use ($user) {
            return $e->user->id === $user->id;
        });
    }

    public function test_user_cannot_register_without_name()
    {
        $response = $this->from('/register')->post('/register', [
            'name' => '',
            'email' => 'johndoe@example.com',
            'password' => 'i-love-testing-laravel',
            'password_confirmation' => 'i-love-testing-laravel',
        ]);

        $users = User::all();

        $this->assertCount(0, $users);
        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('name');
        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertGuest();
    }

    public function test_user_cannot_register_without_email()
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'John Doe',
            'email' => '',
            'password' => 'i-love-testing-laravel',
            'password_confirmation' => 'i-love-testing-laravel',
        ]);

        $users = User::all();

        $this->assertCount(0, $users);
        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('email');
        $this->assertTrue(session()->hasOldInput('name'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertGuest();
    }

    public function test_user_cannot_register_with_invalid_email()
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'password' => 'i-love-testing-laravel',
            'password_confirmation' => 'i-love-testing-laravel',
        ]);

        $users = User::all();

        $this->assertCount(0, $users);
        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('email');
        $this->assertTrue(session()->hasOldInput('name'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertGuest();
    }

    public function test_user_cannot_register_without_password()
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $users = User::all();

        $this->assertCount(0, $users);
        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('password');
        $this->assertTrue(session()->hasOldInput('name'));
        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertGuest();
    }

    public function test_user_cannot_register_without_password_confirmation()
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'password' => 'i-love-testing-laravel',
            'password_confirmation' => '',
        ]);

        $users = User::all();

        $this->assertCount(0, $users);
        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('password');
        $this->assertTrue(session()->hasOldInput('name'));
        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertGuest();
    }

    public function test_user_cannot_register_with_passwords_not_matching()
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'password' => 'i-love-testing-laravel',
            'password_confirmation' => 'i-love-testing-something-else',
        ]);

        $users = User::all();

        $this->assertCount(0, $users);
        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('password');
        $this->assertTrue(session()->hasOldInput('name'));
        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertGuest();
    }
}
