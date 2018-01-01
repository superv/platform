<?php

namespace SuperV\Platform\Domains\Droplet;

use Illuminate\Foundation\Application;
use Illuminate\Routing\Router;
use SuperV\Platform\Contracts\Dispatcher;
use SuperV\Platform\Domains\Console\Features\RegisterConsoleCommands;
use SuperV\Platform\Domains\Feature\ServesFeaturesTrait;
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

    public function __construct(Dispatcher $events, Application $app, Router $router)
    {
        $this->events = $events;
        $this->app = $app;
        $this->router = $router;
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

        $this->disperseRoutes($provider->getRoutes(),
            function ($data) use ($provider) {
                array_set($data, 'superv::droplet', $provider->getDroplet()->getSlug());

                return $data;
            }
        );

        $this->dispatch(new RegisterConsoleCommands($provider));

        collect($provider->getFeatures())
            ->map(function ($feature) {
                superv('features')->push($feature);
            });

        collect($provider->getListeners())
            ->map(function ($listeners, $event) {
                if (! is_array($listeners)) {
                    $listeners = [$listeners];
                }
                collect($listeners)->map(function ($listener) use ($event) {
                    $this->events->listen($event, $listener);
                });
            });

        if (method_exists($provider, 'boot')) {
            $this->app->call([$provider, 'boot'], ['provider' => $this]);
        }
    }
}
