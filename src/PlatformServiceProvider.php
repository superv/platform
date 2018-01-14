<?php

namespace SuperV\Platform;

use SuperV\Platform\Contracts\ServiceProvider;
use SuperV\Platform\Domains\Asset\Asset;
use SuperV\Platform\Domains\Droplet\DropletCollection;
use SuperV\Platform\Domains\Droplet\DropletManager;
use SuperV\Platform\Domains\Droplet\DropletServiceProviderInterface;
use SuperV\Platform\Domains\Droplet\Feature\IntegrateDroplet;
use SuperV\Platform\Domains\Droplet\Jobs\GetPortRoutes;
use SuperV\Platform\Domains\Droplet\Module\Jobs\ActivatePort;
use SuperV\Platform\Domains\Droplet\Module\Jobs\DetectActivePort;
use SuperV\Platform\Domains\Droplet\Port\Ports;
use SuperV\Platform\Domains\Droplet\Port\Routes;
use SuperV\Platform\Domains\Feature\FeatureCollection;
use SuperV\Platform\Domains\Feature\ServesFeaturesTrait;
use SuperV\Platform\Domains\View\Twig\Bridge\TwigBridgeServiceProvider;
use SuperV\Platform\Domains\View\ViewTemplate;
use SuperV\Platform\Events\DropletsBooted;
use SuperV\Platform\Events\PlatformReady;
use SuperV\Platform\Support\Inflator;
use SuperV\Platform\Support\Parser;
use SuperV\Platform\Support\UrlGenerator;
use SuperV\Platform\Traits\BindsToContainer;
use SuperV\Platform\Traits\RegistersRoutes;

/**
 * Class PlatformServiceProvider.
 *
 * https://www.draw.io/#G0Byi-qvl6eS2ySW45cFAtVWVZVTQ
 */
class PlatformServiceProvider extends ServiceProvider implements DropletServiceProviderInterface
{
    use ServesFeaturesTrait;
    use BindsToContainer;

    /** @var  Platform */
    protected $platform;

    protected $providers = [
        TwigBridgeServiceProvider::class,
    ];

    protected $singletons = [
        'droplets'      => DropletCollection::class,
        'features'      => FeatureCollection::class,
        'ports'         => Ports::class,
        'routes'        => Routes::class,
        'view.template' => ViewTemplate::class,
    ];

    protected $bindings = [
        'Illuminate\Contracts\Routing\UrlGenerator' => UrlGenerator::class,
    ];

    public function register()
    {
        if (config('superv.clockwork')) {
            $this->app->register(\Clockwork\Support\Laravel\ClockworkServiceProvider::class);
        }

        if ($this->app->runningInConsole()) {
            $this->commands(require base_path(platform_path("routes/console.php")));
        }

        if (! config('superv.installed', false)) {
            return;
        }
        $this->mergeConfigs();

        $this->registerBindings($this->bindings);
        $this->registerProviders($this->providers);
        $this->registerSingletons($this->singletons);

        $this->app->singleton('superv.parser', function ($app) { return $app->make(Parser::class); });
        $this->app->singleton('superv.inflator', function ($app) { return $app->make(Inflator::class); });
        $this->app->singleton('superv.assets', function ($app) {
            return $app->make(Asset::class);
        });
    }

    public function boot(DropletManager $dropletManager)
    {
        if (! config('superv.installed', false)) {
            return;
        }

        $this->loadViewsFrom(__DIR__.'/../resources/views/', 'superv');

        /**
         * Refactor idea: instead of registering routes views etc
         * by looping all droplets, first collect the droplets
         * then perform registeration depending on port, cli
         */
        $dropletManager->load();

        $port = $this->dispatch(new DetectActivePort());

        if ($port) {
            superv('routes')->disperse($this->dispatch(new GetPortRoutes(platform_path())));

            $this->dispatch(new ActivatePort($port));
            $this->dispatch(new IntegrateDroplet($port));

            $routes = superv('routes')->byPort($port->getSlug());
            $port->registerRoutes($routes);
        }

        $dropletManager->boot();
        DropletsBooted::dispatch();
        PlatformReady::dispatch();
    }

    protected function mergeConfigs()
    {
        foreach (glob(__DIR__.'/../config/*') as $path) {
            $key = pathinfo($path, PATHINFO_FILENAME);
            $config = (array)config()->get("superv.{$key}", []);

            $fromFile = (array)require $path;
            $merged = array_replace($fromFile, $config);
            config()->set('platform::'.$key, $merged);
        }
    }

    public function getResourcePath($path = null)
    {
        return $this->getPath('resource'.DIRECTORY_SEPARATOR.$path);
    }

    public function getPath($path = null)
    {
        return platform_path($path);
    }

    public function getCommands()
    {
        return $this->commands;
    }
}
