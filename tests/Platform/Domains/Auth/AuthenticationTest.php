<?php

namespace Tests\SuperV\Platform\Domains\Auth;

use Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Platform\Domains\Auth\AuthenticatesUsers;
use SuperV\Platform\Domains\Auth\User;
use Tests\SuperV\Platform\BaseTestCase;

class AuthenticationTest extends BaseTestCase
{
    use RefreshDatabase;

    /** @test */
    function authenticates_valid_user_successfully()
    {
        $this->setUpPort('web', env('SV_HOSTNAME'), null, ['client']);
        $this->makeRoute('web');
        $user = $this->makeUser('user@superv.io', 'client');

        $response = $this->post('/login', [
            'email'    => 'user@superv.io',
            'password' => 'secret',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect((new LoginControllerStub)->redirectTo());
        $this->assertUserLoggedIn($user);
    }

    /** @test */
    function only_authenticates_allowed_user_types()
    {
        $this->setUpPort('acp', env('SV_HOSTNAME'), null, ['admin']);
        $this->makeRoute('acp');
        $this->makeUser('user@superv.io', 'secret');

        $response = $this->login('user@superv.io', 'secret');

        $response->assertStatus(302);
        $response->assertRedirect('login');
        $this->assertNotLoggedIn();
    }

    /** @test */
    function does_not_authenticate_a_user_with_invalid_credentials()
    {
        $this->setUpPort('web', env('SV_HOSTNAME'), null, ['client']);
        $this->makeRoute('web');
        $this->makeUser('user@superv.io');

        $response = $this->login('user@superv.io', 'not-the-right-password');

        $response->assertStatus(302);
        $response->assertRedirect('login');
        $this->assertNotLoggedIn();
    }

    /** @test */
    function can_authenticate_multiple_user_types()
    {
        $this->setUpPort('api', env('SV_HOSTNAME'), null, ['client', 'admin']);
        $this->makeRoute('api');
        $user = $this->makeUser('user@superv.io', 'client');
        $admin = $this->makeUser('admin@superv.io', 'admin');

        $this->login('user@superv.io', 'secret');

        $this->assertUserLoggedIn($user);

        Auth::logout();

        $this->login('admin@superv.io', 'secret');

        $this->assertUserLoggedIn($admin);
    }

    protected function login($email, $password)
    {
        return $this->from('/login')->post('/login', [
            'email'    => $email,
            'password' => $password,
        ]);
    }

    protected function assertUserLoggedIn($user)
    {
        $this->assertNotNull(Auth::user());
        $this->assertTrue(Auth::check());
        $this->assertTrue(Auth::user()->is($user));
    }

    protected function assertNotLoggedIn(): void
    {
        $this->assertNull(Auth::user());
        $this->assertFalse(Auth::check());
    }

    protected function makeRoute($port)
    {
        app('router')->post('login', [
            'uses' => LoginControllerStub::class.'@login',
            'port' => $port,
        ]);
    }

    protected function makeUser($email, $type = 'client')
    {
        return factory(User::class)->create([
            'email' => $email,
            'type'  => $type,
        ]);
    }
}

class LoginControllerStub
{
    use AuthenticatesUsers;
}