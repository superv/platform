<?php

namespace Tests\Platform\Domains\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Route;
use SuperV\Platform\Domains\Routing\RouteRegistrar;
use Tests\Platform\TestCase;

class ApiAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /** @var \SuperV\Platform\Domains\Port\Port */
    protected $port;

    protected $shouldBootPlatform = true;

//    protected $user;

    function test__returns_proper_token_response()
    {
        $user = $this->newUser();
        $response = $this->postJson(route('sv.login'), [
            'email'    => $user->getEmail(),
            'password' => 'secret',
        ]);

        $this->assertEquals([
            'access_token',
            'token_type',
            'expires_in',
        ], array_keys($response->json('data')));
    }

    function test__authenticates_with_the_valid_access_token()
    {
        $token = app('tymon.jwt')->fromUser($this->newUser());

        app(RouteRegistrar::class)->setPort($this->port)
                                  ->registerRoute('me', function () {
                                      return response()->json(['me' => auth()->user()]);
                                  });

        $response = $this->json('get', 'me', [],
            ['HTTP_Authorization' => 'Bearer '.$token]
        );
        $response->assertStatus(200);

        $me = $response->json('me');
        $this->assertNotNull($me);
        $this->assertEquals($this->testUser->getEmail(), $me['email']);
    }

    function test__authentication_fails_with_invalid_credentials()
    {
        $response = $this->postJson(route('sv.login'), [
            'email'    => 'user@superv.io',
            'password' => 'invalid-password',
        ]);

        $response->assertStatus(401);

        $this->assertNotNull($response->json('error'));
    }

    function test__unauthenticated_users_handled_properly()
    {
        $this->route('me', function () {
            return response()->json(['me' => auth()->user()]);
        }, 'api');

        $response = $this->json('get', 'me');
        $response->assertStatus(401);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->port = $this->setUpPort([
            'slug'        => 'api',
            'hostname'    => 'localhost',
            'guard'       => 'sv-api',
            'middlewares' => ['sv.auth:sv-api'],
        ]);

//        $this->user = factory(User::class)->create([
//            'id'         => rand(9, 999),
//            'email'      => 'user@superv.io',
//            'password'   => bcrypt('secret'),
//        ]);

        Route::get('login', ['as' => 'login', 'uses' => function () { return 'login'; }]);
    }
}
