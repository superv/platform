<?php

namespace Tests\SuperV\Platform\Packs\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use SuperV\Platform\Packs\Auth\PlatformUser;
use SuperV\Platform\PlatformServiceProvider;
use Tests\SuperV\Platform\BaseTestCase;

class AuthenticationTest extends BaseTestCase
{
    use RefreshDatabase;

    /** @test */
    function authenticates_user_based_on_ports()
    {

        config(['superv.installed' => true]);

        (new PlatformServiceProvider($this->app))->boot();

        $this->setUpPort('web', env('SV_HOSTNAME'));
        app('router')->post('login', [
            'uses' => LoginControllerStub::class.'@login',
            'port' => 'web',
        ]);
        $user = factory(PlatformUser::class)->create([
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
    function user_is_not_authenticated_on_a_port_that_is_not_allowed()
    {
        config(['superv.installed' => true]);

        (new PlatformServiceProvider($this->app))->boot();

        $this->setUpPort('web', env('SV_HOSTNAME'));
        app('router')->post('login', [
            'uses' => LoginControllerStub::class.'@login',
            'port' => 'web',
        ]);

        factory(PlatformUser::class)->create([
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
    protected $redirect = 'dashboard';

    public function login(Request $request)
    {
        $guard = auth()->guard('platform');
        if (! $guard->attempt($request->only(['email', 'password']))) {
            return redirect()->back()
                             ->withInput(request(['email']))
                             ->withErrors([
                                 'email' => 'Invalid credentials',
                             ]);;
        }

        return redirect()->to($this->redirectTo());
    }

    public function redirectTo()
    {
        return $this->redirect;
    }
}