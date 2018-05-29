<?php

namespace Tests\Platform\Domains\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Controller;
use SuperV\Platform\Domains\Auth\Account;
use SuperV\Platform\Domains\Auth\User;
use Tests\Platform\TestCase;
use Tymon\JWTAuth\Providers\LaravelServiceProvider;

class ApiAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected $packageProviders = [LaravelServiceProvider::class];

    protected $appConfig = [
        'auth.guards.superv-api' => [
            'driver'   => 'jwt',
            'provider' => 'platform',
        ],
        'jwt.secret'             => 'jwt-secret',

        'superv.ports' => [
            'api' => [
                'hostname'    => 'localhost',
                'guard'       => 'superv-api',
                'middlewares' => ['api'],
            ],
        ],
    ];

    protected $user;

    protected $account;

    protected function setUp()
    {
        parent::setUp();

        $this->route('post@login', ApiAuthControllerStub::class.'@login', 'api');

        $this->account = Account::create(
            ['name' => 'Test Account']
        );

        $this->user = factory(User::class)->create([
            'account_id' => $this->account->id,
            'id'    => rand(9, 999),
            'email' => 'user@superv.io',
        ]);
    }

    /** @test */
    function returns_proper_token_response()
    {
        $response = $this->json('post', 'login', [
            'account_id' => $this->account->id,
            'email'    => 'user@superv.io',
            'password' => 'secret',
        ]);

        $this->assertEquals(['access_token', 'token_type', 'expires_in'], array_keys($response->decodeResponseJson()));
    }

    /** @test */
    function authenticates_with_the_valid_access_token()
    {
        $this->withoutExceptionHandling();

        $token = $this->json('post', 'login', [
            'email'    => 'user@superv.io',
            'password' => 'secret',
        ])->decodeResponseJson('access_token');

        $this->route('me', [
            'uses'       => function () {
                return response()->json(['me' => auth()->user()]);
            },
            'middleware' => ['auth:superv-api'],
        ], 'api');

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
//        $this->route('me', ApiAuthControllerStub::class. '@me', 'api');
        $this->route('me', [
            'uses'       => function () {
                return response()->json(['me' => auth()->user()]);
            },
            'middleware' => ['auth:superv-api'],
        ], 'api');

        $response = $this->json('get', 'me');
        $response->assertStatus(401);
    }
}

class ApiAuthControllerStub extends Controller
{
    public function login()
    {
        $credentials = request(['email', 'password']);

        $guard = $this->guard();
        if (! $token = $guard->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => $this->guard()->factory()->getTTL() * 60,
        ]);
    }

    protected function guard()
    {
        return auth()->guard();
    }
}