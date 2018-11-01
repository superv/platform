<?php

namespace Tests\Platform\Domains\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Route;
use SuperV\Platform\Domains\Auth\Account;
use SuperV\Platform\Domains\Auth\User;
use SuperV\Platform\Domains\Routing\RouteRegistrar;
use Tests\Platform\TestCase;

class ApiAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /** @var \SuperV\Platform\Domains\Port\Port */
    protected $port;

    protected $user;

    protected $account;

    protected function setUp()
    {
        parent::setUp();

        $this->port = $this->setUpPort([
            'slug'        => 'api',
            'hostname'    => 'localhost',
            'guard'       => 'superv-api',
            'middlewares' => ['auth:superv-api'],
        ]);

        $this->account = Account::create(
            ['name' => 'Test Account']
        );

        $this->user = factory(User::class)->create([
            'account_id' => $this->account->id,
            'id'         => rand(9, 999),
            'email'      => 'user@superv.io',
            'password'   => bcrypt('secret'),
        ]);

        Route::get('login', ['as' => 'login', 'uses' => function() { return 'login'; }]);
    }

    /** @test */
    function returns_proper_token_response()
    {
        $this->withoutExceptionHandling();
        $response = $this->json('post', 'login', [
            'account_id' => $this->account->id,
            'email'      => 'user@superv.io',
            'password'   => 'secret',
        ]);

        $this->assertEquals([
            'access_token',
            'token_type',
            'expires_in',
        ], array_keys($response->decodeResponseJson('data')));
    }

    /** @test */
    function authenticates_with_the_valid_access_token()
    {
        $token = app('tymon.jwt')->fromUser($this->user);

        app(RouteRegistrar::class)->setPort($this->port)
                                  ->registerRoute('me', function () {
                                      return response()->json(['me' => auth()->user()]);
                                  });

        $response = $this->json('get', 'me', [],
            ['HTTP_Authorization' => 'Bearer '.$token]
        );
        $response->assertStatus(200);

        $me = $response->decodeResponseJson('me');
        $this->assertNotNull($me);
        $this->assertEquals('user@superv.io', $me['email']);
    }

    /** @test */
    function authentication_fails_with_invalid_credentials()
    {
        $response = $this->json('post', '/login', [
            'email'    => 'user@superv.io',
            'password' => 'invalid-password',
        ]);

        $response->assertStatus(401);

        $this->assertNotNull($response->decodeResponseJson('error'));
    }

    /** @test */
    function unauthenticated_users_handled_properly()
    {
        $this->route('me', function () {
            return response()->json(['me' => auth()->user()]);
        }, 'api');

        $response = $this->json('get', 'me');
        $response->assertStatus(401);
    }
}
