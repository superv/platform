<?php

namespace SuperV\Platform;

use SuperV\Platform\Contracts\ServiceProvider;
use SuperV\Platform\Domains\Asset\Asset;
use SuperV\Platform\Domains\Droplet\DropletCollection;
use SuperV\Platform\Domains\Droplet\DropletServiceProviderInterface;
use SuperV\Platform\Domains\Droplet\Port\Ports;
use SuperV\Platform\Domains\Droplet\Port\Routes;
use SuperV\Platform\Domains\Feature\FeatureCollection;
use SuperV\Platform\Domains\Feature\ServesFeaturesTrait;
use SuperV\Platform\Domains\View\Twig\Bridge\TwigBridgeServiceProvider;
use SuperV\Platform\Domains\View\ViewTemplate;
use SuperV\Platform\Support\Inflator;
use SuperV\Platform\Support\Parser;
use SuperV\Platform\Support\UrlGenerator;
use SuperV\Platform\Traits\BindsToContainer;

/**
 * Class PlatformServiceProvider.
 *
 * https://www.draw.io/#G0Byi-qvl6eS2ySW45cFAtVWVZVTQ
 */
class PlatformServiceProvider extends ServiceProvider implements DropletServiceProviderInterface
{
    use ServesFeaturesTrait;
    use BindsToContainer;

    protected $providers = [
        TwigBridgeServiceProvider::class,
    ];

    protected $singletons = [
        Platform::class,
        'droplets'      => DropletCollection::class,
        'features'      => FeatureCollection::class,
        'ports'         => Ports::class,
        'routes'        => Routes::class,
        'view.template' => ViewTemplate::class,
        'assets'        => Asset::class,
        'inflator'      => Inflator::class,
        'parser'        => Parser::class,
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

        $this->mergeConfigs();

        $this->registerBindings($this->bindings);
        $this->registerProviders($this->providers);
        $this->registerSingletons($this->singletons);
    }

    public function boot()
    {
        if (! config('superv.installed', false)) {
            return;
        }
        $this->loadViewsFrom(__DIR__.'/../resources/views/', 'superv');

        $this->app->booted(function () {
            app(Platform::class)->boot();
        });
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
