<?php namespace SuperV\Platform\Domains\Droplet;

use Illuminate\Console\Events\ArtisanStarting;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Router;
use SuperV\Platform\Contracts\Dispatcher;
use SuperV\Platform\Domains\Droplet\Jobs\RegisterDropletRouteJob;
use SuperV\Platform\Domains\Feature\FeatureCollection;

class DropletProvider
{
    use DispatchesJobs;

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

    public function __construct(
        Dispatcher $events,
        Application $app,
        Router $router,
        Schedule $schedule
    ) {
        $this->events = $events;
        $this->app = $app;
        $this->router = $router;
        $this->schedule = $schedule;
    }

    public function register(Droplet $droplet)
    {
        $provider = $droplet->getServiceProvider();

        if (!class_exists($provider)) {
            return;
        }

        $this->providers[] = $provider = $droplet->newServiceProvider();

        $this->bindAliases($provider);
        $this->bindClasses($provider);
        $this->bindSingletons($provider);

        $this->registerRoutes($provider, $droplet);
        $this->registerCommands($provider);
        $this->registerFeatures($provider);
        $this->registerEvents($provider, $droplet);

        if (method_exists($provider, 'register')) {
            $this->app->call([$provider, 'register'], ['provider' => $this]);
        }
    }

    protected function registerCommands(DropletServiceProvider $provider)
    {
        if ($commands = $provider->getCommands()) {
            // To register the commands with Artisan, we will grab each of the arguments
            // passed into the method and listen for Artisan "start" event which will
            // give us the Artisan console instance which we will give commands to.
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

    protected function registerEvents(DropletServiceProvider $provider, Droplet $droplet)
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
                    $this->events->listen($droplet->getSlug() . '::' . $event, $listener);
                }
            }
        }
    }

    protected function registerRoutes(DropletServiceProvider $provider, Droplet $droplet)
    {
        if (!$routes = $provider->getRoutes()) {
            return;
        }

        foreach ($routes as $uri => $route) {
            $route = !is_array($route) ? ['uses' => $route] : $route;
            array_set($route, 'superv::droplet', $droplet->getSlug());
            $this->dispatch(new RegisterDropletRouteJob($uri, $route));
        }
    }

    protected function registerFeatures(DropletServiceProvider $provider)
    {
        $features = app(FeatureCollection::class);
        foreach ($provider->getFeatures() as $key => $feature) {
            $features->push($feature);
        }
    }
}