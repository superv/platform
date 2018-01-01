<?php

namespace SuperV\Platform\Domains\Droplet\Feature;

use Illuminate\View\Factory;
use SuperV\Platform\Domains\Config\Jobs\EnableConfigFiles;
use SuperV\Platform\Domains\Console\Features\RegisterConsoleCommands;
use SuperV\Platform\Domains\Droplet\Droplet;
use SuperV\Platform\Domains\Droplet\DropletProvider;
use SuperV\Platform\Domains\Droplet\Model\DropletCollection;
use SuperV\Platform\Domains\Feature\Feature;
use SuperV\Platform\Domains\Feature\ServesFeaturesTrait;
use SuperV\Platform\Traits\BindsToContainer;
use SuperV\Platform\Traits\RegistersRoutes;

/**
 * Class IntegrateDroplet.
 */
class IntegrateDroplet extends Feature
{
    use ServesFeaturesTrait;
    use RegistersRoutes;
    use BindsToContainer;

    /**
     * @var Droplet
     */
    private $droplet;

    public function __construct(Droplet $droplet)
    {
        $this->droplet = $droplet;
    }

    public function handle(DropletCollection $droplets, Factory $views)
    {
        $droplet = $this->droplet;

        $this->dispatch(new EnableConfigFiles($droplet->getModel()));

        $droplets->put($droplet->getSlug(), $droplet);

        $this->register($droplet);

        /**
         *  Add namespaces for view and config,
         *  Both for "name::" and "superv.type.name::"
         */
        $viewsPath = [base_path($droplet->getPath('resources/views'))];
        $views->addNamespace($droplet->getSlug(), $viewsPath);
        $views->addNamespace($droplet->getName(), $viewsPath);
    }

    public function register(Droplet $droplet)
    {
        if (! $provider = $droplet->newServiceProvider()) {
            return;
        }

        $this->registerProviders($provider->getProviders());
        if (method_exists($provider, 'register')) {
            app()->call([$provider, 'register'], ['provider' => $this]);
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
                    app('events')->listen($event, $listener);
                });
            });

        if (method_exists($provider, 'boot')) {
            app()->call([$provider, 'boot'], ['provider' => $this]);
        }
    }
}
