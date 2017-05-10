<?php namespace SuperV\Platform\Domains\Droplet;

use Illuminate\Console\Events\ArtisanStarting;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Application;
use Illuminate\Routing\Router;
use SuperV\Platform\Domains\Feature\FeatureCollection;

class DropletProvider
{
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
    /**
     * @var Application
     */
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

    protected function registerEvents(DropletServiceProvider $provider)
    {
        if (!$listen = $provider->getListeners()) {
            return;
        }

        foreach ($listen as $event => $listeners) {
            foreach ($listeners as $key => $listener) {
                if (is_integer($listener)) {
                    $listener = $key;
                    $priority = $listener;
                } else {
                    $priority = 0;
                }

                $this->events->listen($event, $listener, $priority);
            }
        }
    }

    protected function registerRoutes(DropletServiceProvider $provider, Droplet $droplet)
    {

        if (!$routes = $provider->getRoutes()) {
            return;
        }

        foreach ($routes as $uri => $route) {

            /*
             * If the route definition is an
             * not an array then let's make it one.
             * Array type routes give us more control
             * and allow us to pass information in the
             * request's route action array.
             */
            if (!is_array($route)) {
                $route = [
                    'uses' => $route,
                ];
            }

            $verb        = array_pull($route, 'verb', 'any');
            $middleware  = array_pull($route, 'middleware', []);
            $constraints = array_pull($route, 'constraints', []);

            array_set($route, 'superv::droplet', $droplet->getSlug());

            if (is_string($route['uses']) && !str_contains($route['uses'], '@')) {
                $this->router->resource($uri, $route['uses']);
            } else {

                $route = $this->router->{$verb}($uri, $route)->where($constraints);

                if ($middleware) {
                    call_user_func_array([$route, 'middleware'], (array)$middleware);
                }
            }
        }
    }
    
    protected function registerFeatures(DropletServiceProvider $provider)
    {
        $features = app(FeatureCollection::class);
        foreach($provider->getFeatures() as $key => $feature) {
            $features->put($key."@".$provider->getNamespace(), $feature);
        }
    }
}