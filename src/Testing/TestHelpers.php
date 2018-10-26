<?php

namespace SuperV\Platform\Testing;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Testing\TestResponse;
use PHPUnit\Framework\Assert;

trait TestHelpers
{
    protected $platformInstalled = false;

    protected $postInstallCallbacks = [];

    public function afterPlatformInstalled(callable $callback)
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

    protected function assertArrayContains(array $needle, array $haystack)
    {
        $this->assertEquals($needle, array_intersect($needle, $haystack));
    }

    protected function assertDatabaseHasTable($table)
    {
        $this->assertTrue(\Schema::hasTable($table));
    }

    protected function assertDatabaseHasNotTable($table)
    {
        $this->assertFalse(\Schema::hasTable($table));
    }

    protected function assertProviderRegistered($provider)
    {
        $this->assertContains($provider, array_keys($this->app->getLoadedProviders()));
    }

    protected function assertProviderNotRegistered($provider)
    {
        $this->assertNotContains($provider, array_keys($this->app->getLoadedProviders()));
    }

    /**
     * @param $abstract
     * @return \Mockery\MockInterface
     */
    protected function bindMock($abstract)
    {
        $this->app->instance($abstract, $mockInstance = \Mockery::mock($abstract));

        return $mockInstance;
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
}