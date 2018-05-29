<?php

namespace Tests\Platform\Domains\Auth;

use Auth;
use Illuminate\Auth\AuthManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Platform\Domains\Auth\AuthenticatesUsers;
use SuperV\Platform\Domains\Auth\Client;
use SuperV\Platform\Domains\Auth\PlatformUserProvider;
use SuperV\Platform\Domains\Auth\User;
use Tests\Platform\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function authenticates_valid_user_successfully()
    {
        $this->setUpPort('web', 'localhost', null, ['client']);
        $this->makeRoute('web');
        $user = $this->makeUser('user@superv.io')->assign('client');

        $response = $this->login('user@superv.io', 'secret');

        $response->assertStatus(302);
        $response->assertRedirect((new LoginControllerStub)->redirectTo());
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    function only_authenticates_allowed_roles()
    {
        $this->setUpPort('acp', 'localhost', null, ['admin']);
        $this->makeRoute('acp');
        $this->makeUser('user@superv.io')->assign('client');

        $response = $this->login('user@superv.io', 'secret');

        $response->assertStatus(302);
        $response->assertRedirect('login');
        $this->assertNotAuthenticated();
    }

    /** @test */
    function resolves_port_model_upon_authentication()
    {
        $this->setUpPort('web', 'localhost', null, ['client'], Client::class);
        $this->makeRoute('web');
        $user = $this->makeUser('user@superv.io')->assign('client');
        Client::create(['user_id' => $user->id]);

        $this->login('user@superv.io', 'secret');

        // Reset AuthManager
        $this->app->instance('auth', $auth = new AuthManager($this->app));
        $auth->provider('platform', function ($app) {
            return new PlatformUserProvider($app['hash'], config('superv.auth.user.model'));
        });

        $this->assertAuthenticated();
        $this->assertInstanceOf(Client::class, $auth->user());
        $this->assertEquals($user->id, $auth->user()->user->id);
    }

    /** @test */
    function does_not_authenticate_a_user_with_invalid_credentials()
    {
        $this->setUpPort('web', 'localhost', null, ['client']);
        $this->makeRoute('web');
        $this->makeUser('user@superv.io');

        $response = $this->login('user@superv.io', 'not-the-right-password');

        $response->assertStatus(302);
        $response->assertRedirect('login');
        $this->assertNotAuthenticated();
    }

    /** @test */
    function can_authenticate_multiple_user_types()
    {
        $this->setUpPort('api', 'localhost', null, ['client', 'admin']);
        $this->makeRoute('api');
        $user = $this->makeUser('user@superv.io')->assign('client');
        $admin = $this->makeUser('admin@superv.io')->assign('admin');

        $this->login('user@superv.io', 'secret');

        $this->assertAuthenticatedAs($user);

        Auth::logout();

        $this->login('admin@superv.io', 'secret');

        $this->assertAuthenticatedAs($admin);
    }

    protected function login($email, $password)
    {
        return $this->from('/login')->post('/login', [
            'email'    => $email,
            'password' => $password,
        ]);
    }

    protected function assertNotAuthenticated()
    {
        $this->assertNull(Auth::user());
        $this->assertFalse(Auth::check());
    }

    protected function makeRoute($port)
    {
        $this->route('post@login', LoginControllerStub::class.'@login', $port);
    }

    /**
     * @param $email
     * @return \SuperV\Platform\Domains\Auth\Contracts\User
     */
    protected function makeUser($email)
    {
        return factory(User::class)->create([
            'id'    => rand(9, 999),
            'email' => $email,
        ]);
    }
}

class LoginControllerStub
{
    use AuthenticatesUsers;
}