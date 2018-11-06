<?php

namespace Tests\Platform\Domains\Auth\Guard;

use Hub;
use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Platform\Domains\Auth\User;
use SuperV\Platform\Domains\Port\Port;
use Tests\Platform\TestCase;

class AuthenticationTest
{
    use RefreshDatabase;

    protected function setUp()
    {
        $this->afterPlatformInstalled(function () {
            Hub::register(ApiV1Port::class);
            Hub::register(ApiV2Port::class);
        });

        parent::setUp();
    }

    function ensures_tokens_are_valid_per_port()
    {
        $this->withoutExceptionHandling();
        $user = $this->newUser(['id' => 5]);

        $response = $this->postJson('http://localhost/api/v1/login', ['email' => $user->email, 'password' => 'secret']);

        $this->assertEquals('ok', $response->decodeResponseJson('status'));

        $accessToken = $response->decodeResponseJson('data.access_token');
        $this->assertNotNull($accessToken);

        auth()->logout();

        $response = $this->getJson('http://localhost/api/v2/guard', ['HTTP_Authorization' => 'Bearer '.$accessToken]);

        $response->assertStatus(401);
    }

    /**
     * @param array $overrides
     * @return \SuperV\Platform\Domains\Auth\User $user
     */
    protected function newUser(array $overrides = [])
    {
        $user = factory(User::class)->create($overrides);
        $user->assign('user');

        return $user->fresh();
    }
}

class ApiV1Port extends Port
{
    protected $slug = 'api-v1';

    protected $guard = 'superv-api';

    protected $prefix = 'api/v1';

    public function hostname()
    {
        return 'localhost';
    }
}

class ApiV2Port extends Port
{
    protected $slug = 'api-v2';

    protected $guard = 'superv-api';

    protected $prefix = 'api/v2';

    public function hostname()
    {
        return 'localhost';
    }
}