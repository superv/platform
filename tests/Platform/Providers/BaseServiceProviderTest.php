<?php

namespace Tests\Platform\Providers;

use Illuminate\Foundation\AliasLoader;
use SuperV\Platform\Providers\BaseServiceProvider;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Tests\Platform\TestCase;

class BaseServiceProviderTest extends TestCase
{
    function test__registers_bindings()
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

    function test__registers_singletons()
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

    function test__registers_aliases()
    {
        (new TestServiceProvider($this->app))->registerAliases([
            'Foo' => Foo::class,
            'Bar' => Bar::class,
        ]);

        $aliases = AliasLoader::getInstance()->getAliases();
        $this->assertEquals(Foo::class, $aliases['Foo']);
        $this->assertEquals(Bar::class, $aliases['Bar']);
    }

    function test__registers_event_listeners()
    {
        unset($_SERVER['__event.class']);
        (new TestServiceProvider($this->app))->registerListeners([
            TestEvent::class    => TestListener::class,
            AnotherEvent::class => [
                TestListener::class,
            ],
        ]);

        $this->app['events']->dispatch(new TestEvent());
        $this->assertEquals(TestEvent::class, $_SERVER['__event.class']);

        $this->app['events']->dispatch(new AnotherEvent());
        $this->assertEquals(AnotherEvent::class, $_SERVER['__event.class']);
    }

    function test__registers_console_commands()
    {
        (new TestServiceProvider($this->app))->registerCommands(
            [FooTestCommand::class, BarTestCommand::class]
        );

        try {
            $this->artisan('test:foo');
            $this->artisan('test:bar');

            $this->addToAssertionCount(1);
        } catch (CommandNotFoundException $e) {
            $this->fail('Failed to register console commands');
        }
    }

    function test__adds_view_namespaces()
    {
        (new TestServiceProvider($this->app))->addViewNamespaces([
            'hintA' => 'path/A',
            'hintB' => 'path/B',
        ]);

        $hints = $this->app['view']->getFinder()->getHints();
        $this->assertArrayContains('path/A', $hints['hintA']);
        $this->assertArrayContains('path/B', $hints['hintB']);
    }
}

class TestServiceProvider extends BaseServiceProvider
{
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

class BarTestCommand extends \Illuminate\Console\Command
{
    protected $signature = 'test:bar';

    public function handle() { }
}

class FooTestCommand extends \Illuminate\Console\Command
{
    protected $signature = 'test:foo';

    public function handle() { }
}