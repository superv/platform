<?php

namespace SuperV\Platform;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;
use Platform;
use SuperV\Platform\Listeners\PortDetectedListener;
use SuperV\Platform\Packs\Database\Migrations\MigrationServiceProvider;
use SuperV\Platform\Facades\PlatformFacade;
use SuperV\Platform\Listeners\RouteMatchedListener;
use SuperV\Platform\Packs\Port\PortDetectedEvent;
use SuperV\Platform\Providers\ThemeServiceProvider;

class PlatformServiceProvider extends ServiceProvider
{
    protected $providers = [
        MigrationServiceProvider::class,
        ThemeServiceProvider::class
    ];
    protected $bindings = [];

    protected $aliases = [
        'Platform' => PlatformFacade::class
    ];

    protected $singletons = [];

    protected $listeners = [
        'Illuminate\Routing\Events\RouteMatched' => RouteMatchedListener::class,
        PortDetectedEvent::class => PortDetectedListener::class
    ];

    public function register()
    {
        $this->registerProviders($this->providers);
        $this->registerBindings($this->bindings);
        $this->registerSingletons($this->singletons);
        $this->registerAliases($this->aliases);
        $this->registerListeners($this->listeners);
    }

    public function boot()
    {
        if (config('superv.installed') === true) {
            Platform::boot();
        }
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

    public function addViewNamespaces($namespaces)
    {
        collect($namespaces)->map(function ($path, $hint) {
            $this->app['view']->addNamespace($hint, $path);
        });
    }
}