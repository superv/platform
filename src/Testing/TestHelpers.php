<?php

namespace SuperV\Platform\Testing;

use Hub;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factory as ModelFactory;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Http\Request;
use PHPUnit\Framework\Assert;
use SuperV\Platform\Domains\Auth\Contracts\Users;
use SuperV\Platform\Domains\Auth\User;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Port\Port;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Routing\RouteRegistrar;

trait TestHelpers
{
    protected $platformInstalled = false;

    protected $postInstallCallbacks = [];

    /** @var \SuperV\Platform\Domains\Auth\User */
    protected $testUser;

    public function postJsonUser($uri, array $data = []): TestResponse
    {
        if (! $this->testUser) {
            $this->newUser();
        }

        return $this->postJson($uri, $data, $this->getHeaderWithAccessToken());
    }

    public function getJsonUser($uri): TestResponse
    {
        if (! $this->testUser) {
            $this->newUser();
        }

        return $this->getJson($uri, $this->getHeaderWithAccessToken());
    }

    public function deleteJsonUser($uri): TestResponse
    {
        if (! $this->testUser) {
            $this->newUser();
        }

        return $this->json('DELETE', $uri, [], $this->getHeaderWithAccessToken());
    }

    /**
     * Load model factories from path.
     *
     * @param string $path
     * @return $this
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function withFactories(string $path)
    {
        return $this->loadFactoriesUsing($this->app, $path);
    }

    /**
     * Load model factories from path using Application.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     * @param string                                       $path
     * @return $this
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function loadFactoriesUsing($app, string $path)
    {
        $app->make(ModelFactory::class)->load($path);

        return $this;
    }

    protected function afterPlatformInstalled(callable $callback)
    {
        $this->postInstallCallbacks[] = $callback;

        if ($this->platformInstalled) {
            call_user_func($callback);
        }
    }

    protected function handlePostInstallCallbacks()
    {
        if (! empty($this->postInstallCallbacks)) {
            foreach ($this->postInstallCallbacks as $callback) {
                $callback();
            }
        }
    }

    protected function getListComponent($resource): ListComponent
    {
        return ListComponent::get($resource, $this);
    }

    protected function getFormComponent($resource, $entry = null): FormComponent
    {
        if ($resource instanceof Resource) {
            $resource = $resource->getIdentifier();
        }

        if ($resource instanceof EntryContract) {
            $entry = $resource;
            $resource = $resource->getResourceIdentifier();
        }

        return FormComponent::get($resource.'.forms:default', $this, $entry);
    }

    protected function getComponentFromUrl($url): HelperComponent
    {
        $response = $this->getJsonUser($url)->assertOk();

        return HelperComponent::fromArray($response->decodeResponseJson('data'));
    }

    protected function assertArrayContains($needle, array $haystack)
    {
        $needle = wrap_array($needle);

        if ($needle === $haystack) {
            $this->addToAssertionCount(1);

            return true;
        }

        $this->assertEquals($needle, array_intersect($needle, $haystack), 'Failed asserting array contains');

//        if (is_numeric(array_keys($needle)[0])) {
//            $actual = array_intersect($needle, $haystack);
//        } else {
//            $actual = array_intersect_key($needle, $haystack);
//        }
//        $this->assertEquals($needle, $actual, 'Failed asserting array contains');
    }

    protected function assertColumnNotExists(string $table, string $column)
    {
        $this->assertFalse(in_array($column, \Schema::getColumnListing($table)));
    }

    protected function assertColumnExists(string $table, string $column)
    {
        $this->assertColumnsExist($table, [$column]);
    }

    protected function assertColumnsExist(string $table, array $columns)
    {
        $this->assertArrayContains($columns, \Schema::getColumnListing($table));
    }

    protected function assertTableDoesNotExist($table)
    {
        $this->assertFalse(\Schema::hasTable($table), 'Failed asserting table '.$table.' does not exist');
    }

    protected function assertTableExists($table)
    {
        $this->assertTrue(\Schema::hasTable($table));
    }

    protected function assertProviderRegistered($provider)
    {
        $this->assertArrayContains($provider, array_keys($this->app->getLoadedProviders()));
    }

    protected function assertProviderNotRegistered($provider)
    {
        $this->assertNotContains($provider, array_keys($this->app->getLoadedProviders()));
    }

    protected function assertValidationErrors(TestResponse $response, array $errorKeys = [])
    {
        $response->assertStatus(422);

        if ($errorKeys) {
            $this->assertEquals($errorKeys, array_keys($response->decodeResponseJson('errors')));
        }
    }

    protected function bindMock($abstract, $instance = null): \Mockery\MockInterface
    {
        $this->app->instance($abstract, $mockInstance = \Mockery::mock($instance ?? $abstract));

        return $mockInstance;
    }

    protected function bindPartialMock($abstract, $partial = null): \Mockery\MockInterface
    {
        $this->app->instance($abstract, $mockInstance = \Mockery::mock($partial ?? app($abstract)));

        return $mockInstance->makePartial();
    }

    /**
     * @param       $port
     * @param       $hostname
     * @param null  $theme
     * @param array $roles
     * @param null  $model
     */
    protected function setUpPort($port, $hostname = null, $theme = null, $roles = [], $model = null): Port
    {
        $concentrate = is_array($port) ? $port : [
            'slug'     => $port,
            'hostname' => $hostname,
            'theme'    => $theme,
            'roles'    => $roles,
            'model'    => $model,
        ];
        Hub::register($port = (new Port)->hydrate($concentrate));

        return $port;
    }

    protected function route($uri, $action, $port)
    {
        $port = \Hub::get($port);
        app(RouteRegistrar::class)->setPort($port)->registerRoute($uri, $action);
    }

    protected function setUpMacros()
    {
        TestResponse::macro('data', function ($key) {
            return $this->original->getData()[$key];
        });

        EloquentCollection::macro('assertContains', function ($value) {
            Assert::assertTrue($this->contains($value), "Failed asserting that the collection contains the specified value.");
        });

        EloquentCollection::macro('assertNotContains', function ($value) {
            Assert::assertFalse($this->contains($value), "Failed asserting that the collection does not contain the specified value.");
        });

        EloquentCollection::macro('assertEquals', function ($items) {
            Assert::assertEquals(count($this), count($items));

            $this->zip($items)->each(function ($pair) {
                list($a, $b) = $pair;
                Assert::assertTrue($a->is($b));
            });
        });
    }

    /**
     * Create new test user with ROOT access
     *
     * @param array $overrides
     * @return \SuperV\Platform\Domains\Auth\User $user
     */
    protected function newUser(array $overrides = [])
    {
        $allow = array_pull($overrides, 'allow', '*');
        $this->testUser = app(Users::class)->create(array_merge([
            'name'     => 'test user',
            'email'    => sprintf("user-%s@superv.io", str_random(6)),
            'password' => '$2y$10$lEElUpT9ssdSw4XVVEUt5OaJnBzgcmcE6MJ2Rrov4dKPEjuRD6dd.',
        ], $overrides));
        $this->testUser->assign('user');
        $this->testUser->allow($allow);

        return $this->testUser->fresh();
    }

    protected function getHeaderWithAccessToken($user = null)
    {
        return ['HTTP_Authorization' => 'Bearer '.$this->getAccessToken($user ?? $this->testUser)];
    }

    protected function getAccessToken(User $user)
    {
        return app('tymon.jwt')->fromUser($user);
    }

    protected function makePostRequest($uri = '', array $data = []): Request
    {
        if (is_array($uri) && empty($data)) {
            $data = $uri;
            $uri = '';
        }

        if (is_array($uri) && is_array($data)) {
            $uri = '?'.http_build_query($uri);
        }

        return Request::create($uri, 'POST', $data);
    }

    protected function makeGetRequest($uri = '', array $data = []): Request
    {
        if (is_array($uri) && empty($data)) {
            $data = $uri;
            $uri = '';
        }

        return Request::create($uri, 'GET', $data);
    }
}
