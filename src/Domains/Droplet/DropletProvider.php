<?php namespace SuperV\Platform\Domains\Droplet;

use Illuminate\Console\Events\ArtisanStarting;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Foundation\Application;
use Illuminate\Routing\Router;
use SuperV\Platform\Contracts\Dispatcher;
use SuperV\Platform\Domains\Droplet\Jobs\RegisterDropletRouteJob;
use SuperV\Platform\Domains\Droplet\Types\PortCollection;
use SuperV\Platform\Domains\Feature\FeatureCollection;
use SuperV\Platform\Domains\Feature\ServesFeaturesTrait;
use SuperV\Platform\Domains\Manifest\Features\RegisterManifest;

class DropletProvider
{
    use ServesFeaturesTrait;

    /**
     * The registered providers.
     *
     * @var array
     */
    protected $providers = [];

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
        $provider = $droplet->getServiceProvider();
        if (!class_exists($provider)) {
            return;
        }

        $this->providers[] = $provider = $droplet->newServiceProvider();

        $this->registerProviders($provider);
        if (method_exists($provider, 'register')) {
            $this->app->call([$provider, 'register'], ['provider' => $this]);
        }

        $this->bindAliases($provider);
        $this->bindClasses($provider);
        $this->bindSingletons($provider);

        $this->registerRoutes($provider);
        $this->registerCommands($provider);
        $this->registerFeatures($provider);
        $this->registerEvents($provider);

        $this->registerManifests($provider);


    }

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

    protected function registerEvents(DropletServiceProvider $provider)
    {
        if (!$listen = $provider->getListeners()) {
            return;
        }

        foreach ($listen as $event => $listeners) {
            if (!is_array($listeners)) {
                $listeners = [$listeners];
            }
            foreach ($listeners as $key => $listener) {
                if ($listener) {
                    $this->events->listen($provider->getDroplet()->getSlug() . '::' . $event, $listener);
                }
            }
        }
    }

    protected function registerRoutes(DropletServiceProvider $provider)
    {
        if (!$routes = $provider->getRoutes()) {
            return;
        }

        foreach ($routes as $uri => $route) {
            $route = !is_array($route) ? ['uses' => $route] : $route;

            $this->dispatch(new RegisterDropletRouteJob($provider->getDroplet(), $uri, $route));
        }
    }

    protected function registerFeatures(DropletServiceProvider $provider)
    {
        $features = app(FeatureCollection::class);
        foreach ($provider->getFeatures() as $key => $feature) {
            $features->push($feature);
        }
    }

    protected function registerManifests(DropletServiceProvider $provider)
    {
        foreach ($provider->getManifests() as $key => $manifest) {
            $this->serve(new RegisterManifest(superv($manifest), $provider->getDroplet()));
        }
    }

    protected function registerProviders(DropletServiceProvider $provider)
    {
        foreach ($provider->getProviders() as $provider) {
            $this->app->register($provider);
        }
    }
}