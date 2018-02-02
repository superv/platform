<?php

namespace Tests\SuperV\Platform\Providers;

use SuperV\Platform\Providers\BaseServiceProvider;
use Tests\SuperV\Platform\BaseTestCase;
use Illuminate\Foundation\AliasLoader;

class BaseServiceProviderTest extends BaseTestCase
{
    /**
     * @test
     */
    function registers_bindings()
    {
        (new TestServiceProvider($this->app))->registerBindings([
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
        (new TestServiceProvider($this->app))->registerSingletons([
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
        (new TestServiceProvider($this->app))->registerAliases([
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
        (new TestServiceProvider($this->app))->registerListeners([
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
        (new TestServiceProvider($this->app))->addViewNamespaces([
            'hintA' => 'path/A',
            'hintB' => 'path/B',
        ]);

        $hints = $this->app['view']->getFinder()->getHints();
        $this->assertContains('path/A', $hints['hintA']);
        $this->assertContains('path/B', $hints['hintB']);
    }
}

class TestServiceProvider extends BaseServiceProvider {}

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