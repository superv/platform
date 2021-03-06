<?php

namespace SuperV\Platform\Providers;

use Illuminate\Console\Application as Artisan;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;
use SuperV\Platform\Platform;

abstract class BaseServiceProvider extends ServiceProvider
{
    protected $providers = [];

    protected $_bindings = [];

    protected $_singletons = [];

    protected $aliases = [];

    protected $listeners = [];

    protected $commands = [];

    /** @var \SuperV\Platform\Platform */
    protected $platform;

    public function __construct($app)
    {
        parent::__construct($app);
        $this->platform = Platform::resolve();
    }

    public function register()
    {
        $this->registerAll();
    }

    public function registerAll()
    {
        $this->registerProviders($this->providers);
        $this->registerBindings($this->_bindings);
        $this->registerSingletons($this->_singletons);
        $this->registerAliases($this->aliases);
        $this->registerListeners($this->listeners);
        $this->registerCommands($this->commands);
    }

    public function registerProviders(array $providers)
    {
        collect($providers)
            ->map(function ($provider) {
                $this->app->register($provider);
            });
    }

    public function registerBindings(array $bindings)
    {
        collect($bindings)
            ->map(function ($concrete, $abstract) {
                $this->app->bind($abstract, $concrete);
            });
    }

    public function registerSingletons(array $singletons)
    {
        collect($singletons)
            ->map(function ($concrete, $abstract) {
                if (is_numeric($abstract) && is_string($concrete)) {
                    $this->app->singleton($concrete, $concrete);
                } elseif (! preg_match('/[^A-Za-z._\-]/', $abstract)) {
                    $this->app->singleton("superv.{$abstract}", $concrete);
                    $this->app->singleton($concrete, $concrete);
                } else {
                    $this->app->singleton($abstract, $concrete);
                }
            });
    }

    public function registerAliases(array $aliases)
    {
        AliasLoader::getInstance($aliases)->register();
    }

    public function registerListeners(array $listeners)
    {
        collect($listeners)
            ->map(function ($listener, $event) {
                if (! is_array($listener)) {
                    $listener = [$listener];
                }
                collect($listener)->map(function ($listener) use ($event) {
                    $this->app['events']->listen($event, $listener);
                });
            });
    }

    public function registerCommands(array $commands)
    {
        Artisan::starting(function (Artisan $artisan) use ($commands) {
            $artisan->resolveCommands($commands);
        });
    }

    public function addViewNamespaces($namespaces)
    {
        collect($namespaces)->map(function ($path, $hint) {
            $this->app['view']->addNamespace($hint, $path);
        });
    }
}