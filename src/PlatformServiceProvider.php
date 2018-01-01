<?php

namespace SuperV\Platform;

use Illuminate\Console\Application as Artisan;
use SuperV\Platform\Contracts\ServiceProvider;
use SuperV\Platform\Domains\Application\Console\EnvSet;
use SuperV\Platform\Domains\Application\Console\InstallSuperVCommand;
use SuperV\Platform\Domains\Console\Features\RegisterConsoleCommands;
use SuperV\Platform\Domains\Droplet\Console\DropletInstallCommand;
use SuperV\Platform\Domains\Droplet\Console\DropletSeedCommand;
use SuperV\Platform\Domains\Droplet\DropletManager;
use SuperV\Platform\Domains\Droplet\DropletServiceProviderInterface;
use SuperV\Platform\Domains\Droplet\Jobs\GetPortRoutes;
use SuperV\Platform\Domains\Droplet\DropletCollection;
use SuperV\Platform\Domains\Droplet\Droplet;
use SuperV\Platform\Domains\Droplet\Module\Jobs\DetectActivePort;
use SuperV\Platform\Domains\Droplet\Port\Port;
use SuperV\Platform\Domains\Droplet\Port\PortCollection;
use SuperV\Platform\Domains\Feature\FeatureCollection;
use SuperV\Platform\Domains\Feature\ServesFeaturesTrait;
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
    use RegistersRoutes;
    use BindsToContainer;

    /** @var  Platform */
    protected $platform;

    protected $providers;

    protected $singletons = [
        'droplets'      => DropletCollection::class,
        'features'      => FeatureCollection::class,
        'ports'         => PortCollection::class,
        'view.template' => ViewTemplate::class,
    ];

    protected $bindings = [
        'Illuminate\Contracts\Routing\UrlGenerator' => UrlGenerator::class,
    ];

    protected $commands = [
        EnvSet::class,
        InstallSuperVCommand::class,
        DropletInstallCommand::class,
        DropletSeedCommand::class
    ];

    public function register()
    {
        if (config('superv.clockwork')) {
            $this->app->register(\Clockwork\Support\Laravel\ClockworkServiceProvider::class);
        }

        // commmands needed before the platform is installed
        if ($this->app->runningInConsole()) {
            $this->commands($this->commands);
        }

//        app(Bridge::class)->addExtension(app(AsseticExtension::class));

        if (! env('SUPERV_INSTALLED', false)) {
            return;
        }
        $this->mergeConfigs();

        $this->registerBindings($this->bindings);
        $this->registerProviders($this->providers);
        $this->registerSingletons($this->singletons);

        $this->app->singleton('superv.parser', function ($app) { return $app->make(Parser::class); });
        $this->app->singleton('superv.inflator', function ($app) { return $app->make(Inflator::class); });
    }

    public function boot(DropletManager $dropletManager)
    {
        if (! env('SUPERV_INSTALLED', false)) {
            return;
        }
        $this->loadViewsFrom(__DIR__.'/../resources/views/', 'superv');

        /**
         * Refactor idea: instead of registering routes views etc
         * by looping all droplets, first collect the droplets
         * then perform registeration depending on port, cli
         */
        $dropletManager->load();

        // Detect the active port and boot all ports
        $this->dispatch(new DetectActivePort());
        $dropletManager->bootPorts();

        // boot other droplets
        $dropletManager->bootAllButPorts();
        DropletsBooted::dispatch();


        $this->disperseRoutes($this->dispatch(new GetPortRoutes(platform_path())));
        $this->registerRoutes(app(Port::class));

        $this->dispatch(new RegisterConsoleCommands($this));

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
        return $this->getPath('resource' . DIRECTORY_SEPARATOR . $path);
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
