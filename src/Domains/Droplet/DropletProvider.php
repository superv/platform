<?php

namespace SuperV\Platform\Domains\Droplet;

use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Foundation\Application;
use SuperV\Platform\Contracts\Dispatcher;
use Illuminate\Console\Scheduling\Schedule;
use SuperV\Platform\Traits\RegistersRoutes;
use Illuminate\Console\Events\ArtisanStarting;
use SuperV\Platform\Domains\Feature\FeatureCollection;
use SuperV\Platform\Domains\Feature\ServesFeaturesTrait;
use SuperV\Platform\Domains\Droplet\Types\PortCollection;
use SuperV\Platform\Domains\Manifest\Features\ManifestDroplet;

class DropletProvider
{
    use ServesFeaturesTrait;
    use RegistersRoutes;

    /**
     * @var Dispatcher
     */
    private $events;

    private $app;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var Schedule
     */
    private $schedule;

    /**
     * @var PortCollection
     */
    private $ports;

    public function __construct(
        Dispatcher $events,
        Application $app,
        Router $router,
        Schedule $schedule,
        PortCollection $ports
    ) {
        $this->events = $events;
        $this->app = $app;
        $this->router = $router;
        $this->schedule = $schedule;
        $this->ports = $ports;
    }

    public function register(Droplet $droplet)
    {
        if (! $provider = $droplet->newServiceProvider()) {
            return;
        }

        $this->registerProviders($provider);
        if (method_exists($provider, 'register')) {
            $this->app->call([$provider, 'register'], ['provider' => $this]);
        }

        $this->bindAliases($provider);
        $this->bindClasses($provider);
        $this->bindSingletons($provider);

        $this->registerRoutes(
            $provider->getRoutes(),
            function (Route $route) use ($provider) {
                $route->setAction(array_merge([
                    'superv::droplet' => $provider->getDroplet()->getSlug(),
                ], $route->getAction()));
            }
        );
//        $this->registerRoutes($provider);

        $this->registerCommands($provider);
        $this->registerFeatures($provider);

        $this->registerListeners($provider);
        \Debugbar::startMeasure('registerManifests', 'Register Manifests');

        $this->dispatch(new ManifestDroplet($droplet));
        \Debugbar::stopMeasure('registerManifests');
    }

//    protected function registerRoutesxxx(DropletServiceProvider $provider)
//    {
//        if (!$routes = $provider->getRoutes()) {
//            return;
//        }
//
//        foreach ($routes as $uri => $route) {
//            $route = !is_array($route) ? ['uses' => $route] : $route;
//
//            $this->dispatch(new RegisterDropletRouteJob($provider->getDroplet(), $uri, $route));
//        }
//    }

    protected function registerCommands(DropletServiceProvider $provider)
    {
        if ($commands = $provider->getCommands()) {
            $this->events->listen(
                'Illuminate\Console\Events\ArtisanStarting',
                function (ArtisanStarting $event) use ($commands) {
                    $event->artisan->resolveCommands($commands);
                }
            );
        }
    }

    protected function bindAliases(DropletServiceProvider $provider)
    {
        if ($aliases = $provider->getAliases()) {
            AliasLoader::getInstance($aliases)->register();
        }
    }

    protected function bindClasses(DropletServiceProvider $provider)
    {
        foreach ($provider->getBindings() as $abstract => $concrete) {
            $this->app->bind($abstract, $concrete);
        }
    }

    protected function bindSingletons(DropletServiceProvider $provider)
    {
        foreach ($provider->getSingletons() as $abstract => $concrete) {
            $this->app->singleton($abstract, $concrete);
        }
    }

    protected function registerListeners(DropletServiceProvider $provider)
    {
        if (! $listen = $provider->getListeners()) {
            return;
        }

        foreach ($listen as $event => $listeners) {
            if (! is_array($listeners)) {
                $listeners = [$listeners];
            }
            foreach ($listeners as $key => $listener) {
                if ($listener) {
                    $this->events->listen($provider->getDroplet()->getSlug().'::'.$event, $listener);
                }
            }
        }
    }

    protected function registerFeatures(DropletServiceProvider $provider)
    {
        $features = app(FeatureCollection::class);
        foreach ($provider->getFeatures() as $key => $feature) {
            $features->push($feature);
        }
    }

    protected function registerProviders(DropletServiceProvider $provider)
    {
        foreach ($provider->getProviders() as $provider) {
            $this->app->register($provider);
        }
    }
}
