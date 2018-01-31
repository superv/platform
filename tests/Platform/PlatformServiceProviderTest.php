<?php

namespace Tests\SuperV\Platform;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Platform;
use SuperV\Platform\Domains\Database\Migrations\MigrationServiceProvider;
use SuperV\Platform\PlatformServiceProvider;

class PlatformServiceProviderTest extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    function boots_platform_if_superv_is_installed()
    {
        config(['superv.installed' => true]);

        Platform::shouldReceive('boot')->once();
        (new PlatformServiceProvider($this->app))->boot();
    }
    /**
     * @test
     */
    function does_not_boot_platform_if_superv_is_not_installed()
    {
        config(['superv.installed' => false]);

        Platform::shouldReceive('boot')->never();
        (new PlatformServiceProvider($this->app))->boot();
    }

    /**
     * @test
     */
    function registers_required_providers()
    {
        (new PlatformServiceProvider($this->app))->register();

        $this->assertContains(MigrationServiceProvider::class, array_keys($this->app->getLoadedProviders()));
    }


    /**
     * @test
     */
    function registers_bindings()
    {
        (new PlatformServiceProvider($this->app))->registerBindings([
            'foo' => Foo::class,
            'bar' => Bar::class,
        ]);

        $bindings = $this->app->getBindings();
        $foo = $bindings['foo'];
        $bar = $bindings['bar'];

        $this->assertInstanceOf(Foo::class, call_user_func($foo['concrete'], $this->app));
        $this->assertInstanceOf(Bar::class, call_user_func($bar['concrete'], $this->app));
        $this->assertFalse($foo['shared']);
        $this->assertFalse($bar['shared']);
    }

    /**
     * @test
     */
    function registers_singletons()
    {
        (new PlatformServiceProvider($this->app))->registerSingletons([
            Foo::class => Foo::class,
            'bar'      => Bar::class,
            Baz::class,
        ]);

        $bindings = $this->app->getBindings();
        $foo = $bindings[Foo::class];
        $bar = $bindings['superv.bar'];
        $baz = $bindings[Baz::class];

        $this->assertInstanceOf(Foo::class, call_user_func($foo['concrete'], $this->app));
        $this->assertInstanceOf(Bar::class, call_user_func($bar['concrete'], $this->app));
        $this->assertInstanceOf(Baz::class, call_user_func($baz['concrete'], $this->app));

        $this->assertTrue($foo['shared']);
        $this->assertTrue($bar['shared']);
        $this->assertTrue($baz['shared']);
    }

    /**
     * @test
     */
    function registers_aliases()
    {
        (new PlatformServiceProvider($this->app))->registerAliases([
            'Foo' => Foo::class,
            'Bar' => Bar::class,
        ]);

        $aliases = AliasLoader::getInstance()->getAliases();
        $this->assertEquals(Foo::class, $aliases['Foo']);
        $this->assertEquals(Bar::class, $aliases['Bar']);
    }

    /**
     * @test
     */
    function registers_event_listeners()
    {
        unset($_SERVER['__event.class']);
        (new PlatformServiceProvider($this->app))->registerListeners([
            TestEvent::class    => TestListener::class,
            AnotherEvent::class => [
                TestListener::class,
            ],
        ]);

        $this->app['events']->fire(new TestEvent());
        $this->assertEquals(TestEvent::class, $_SERVER['__event.class']);

        $this->app['events']->fire(new AnotherEvent());
        $this->assertEquals(AnotherEvent::class, $_SERVER['__event.class']);
    }

    /**
     * @test
     */
    function adds_view_namespaces()
    {
        (new PlatformServiceProvider($this->app))->addViewNamespaces([
            'hintA' => 'path/A',
            'hintB' => 'path/B',
        ]);

        $hints = $this->app['view']->getFinder()->getHints();
        $this->assertContains('path/A', $hints['hintA']);
        $this->assertContains('path/B', $hints['hintB']);
    }
}

class Foo
{
}

class Bar
{
}

class Baz
{
}

class TestEvent
{
}

class AnotherEvent
{
}

class TestListener
{
    public function handle($event)
    {
        $_SERVER['__event.class'] = get_class($event);
    }
}