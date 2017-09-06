<?php

namespace SuperV\Platform\Domains\Droplet;

use Illuminate\Console\Events\ArtisanStarting;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use SuperV\Platform\Contracts\Dispatcher;
use SuperV\Platform\Domains\Droplet\Port\PortCollection;
use SuperV\Platform\Domains\Feature\ServesFeaturesTrait;
use SuperV\Platform\Domains\Manifest\Features\ManifestDroplet;
use SuperV\Platform\Traits\BindsToContainer;
use SuperV\Platform\Traits\RegistersRoutes;

class DropletProvider
{
    use ServesFeaturesTrait;
    use RegistersRoutes;
    use BindsToContainer;

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

        $this->registerProviders($provider->getProviders());
        if (method_exists($provider, 'register')) {
            $this->app->call([$provider, 'register'], ['provider' => $this]);
        }

        $this->registerAliases($provider->getAliases());
        $this->registerBindings($provider->getBindings());
        $this->registerSingletons($provider->getSingletons());

        //
        // Register Routes
        //

        $this->registerRoutes(
            $provider->getRoutes(),
            function (Route $route) use ($provider) {
                $action = $route->getAction();
                array_set($action, 'superv::droplet', $provider->getDroplet()->getSlug());
                $route->setAction($action);
            });

        $this->registerCommands($provider);
        $this->registerFeatures($provider);

        $this->registerListeners($provider);

        \Debugbar::startMeasure('registerManifests', 'Register Manifests');
        $this->dispatch(new ManifestDroplet($droplet));
        \Debugbar::stopMeasure('registerManifests');
    }

    protected function registerCommands(DropletServiceProvider $provider)
    {
        if ($commands = $provider->getCommands()) {
            $this->events->listen('Illuminate\Console\Events\ArtisanStarting', function (ArtisanStarting $event) use (
                $commands
            ) {
                $event->artisan->resolveCommands($commands);
            });
        }
    }

    protected function registerFeatures(DropletServiceProvider $provider)
    {
        foreach ($provider->getFeatures() as $key => $feature) {
            superv('features')->push($feature);
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
                    $this->events->listen($event, $listener);
                    //$this->events->listen($provider->getDroplet()->getSlug().'::'.$event, $listener);
                }
            }
        }
    }
}
