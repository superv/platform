<?php

namespace Tests\SuperV\Platform\Domains\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Platform\Domains\Auth\AuthenticatesUsers;
use SuperV\Platform\Domains\Auth\User;
use SuperV\Platform\PlatformServiceProvider;
use Tests\SuperV\Platform\BaseTestCase;

class AuthenticationTest extends BaseTestCase
{
    use RefreshDatabase;

    /** @test */
    function authenticates_valid_user_on_an_allowed_port()
    {

        config(['superv.installed' => true]);

        (new PlatformServiceProvider($this->app))->boot();

        $this->setUpPort('web', env('SV_HOSTNAME'));
        app('router')->post('login', [
            'uses' => LoginControllerStub::class.'@login',
            'port' => 'web',
        ]);
        $user = factory(User::class)->create([
            'email' => 'user@superv.io',
            'ports' => ['web'],
        ]);

        $response = $this->post('/login', [
            'email'    => 'user@superv.io',
            'password' => 'secret',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect((new LoginControllerStub)->redirectTo());

        $this->assertNotNull(auth()->user());
        $this->assertTrue(\Auth::check());
        $this->assertTrue(\Auth::user()->is($user));
    }

    /** @test */
       function does_not_authenticate_a_user_with_invalid_credentials()
       {
           config(['superv.installed' => true]);

           (new PlatformServiceProvider($this->app))->boot();

           $this->setUpPort('web', env('SV_HOSTNAME'));
           app('router')->post('login', [
               'uses' => LoginControllerStub::class.'@login',
               'port' => 'web',
           ]);

           factory(User::class)->create([
               'email' => 'user@superv.io',
               'ports' => ['web'],
           ]);

           $response = $this->from('/login')->post('/login', [
               'email'    => 'user@superv.io',
               'password' => 'not-the-right-password',
           ]);

           $response->assertStatus(302);
           $response->assertRedirect('login');

           $this->assertNull(auth()->user());
           $this->assertFalse(\Auth::check());
       }

    /** @test */
    function does_not_authenticate_valid_user_on_a_disallowed_port()
    {
        config(['superv.installed' => true]);

        (new PlatformServiceProvider($this->app))->boot();

        $this->setUpPort('web', env('SV_HOSTNAME'));
        app('router')->post('login', [
            'uses' => LoginControllerStub::class.'@login',
            'port' => 'web',
        ]);

        factory(User::class)->create([
            'email' => 'user@superv.io',
            'ports' => ['acp'],
        ]);

        $response = $this->from('/login')->post('/login', [
            'email'    => 'user@superv.io',
            'password' => 'secret',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('login');

        $this->assertNull(auth()->user());
        $this->assertFalse(\Auth::check());
    }
}

class LoginControllerStub
{
    use AuthenticatesUsers;
}