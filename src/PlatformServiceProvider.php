<?php

namespace SuperV\Platform;

use Debugbar;
use Illuminate\View\Factory;
use SuperV\Platform\Adapters\AdapterServiceProvider;
use SuperV\Platform\Contracts\ServiceProvider;
use SuperV\Platform\Domains\Application\Console\EnvSet;
use SuperV\Platform\Domains\Application\Console\InstallSuperV;
use SuperV\Platform\Domains\Console\ConsoleServiceProvider;
use SuperV\Platform\Domains\Console\Features\RegisterConsoleCommands;
use SuperV\Platform\Domains\Database\DatabaseServiceProvider;
use SuperV\Platform\Domains\Droplet\DropletManager;
use SuperV\Platform\Domains\Droplet\Jobs\GetPortRoutes;
use SuperV\Platform\Domains\Droplet\Model\DropletCollection;
use SuperV\Platform\Domains\Droplet\Model\DropletModel;
use SuperV\Platform\Domains\Droplet\Module\Jobs\DetectActivePort;
use SuperV\Platform\Domains\Droplet\Port\PortCollection;
use SuperV\Platform\Domains\Feature\FeatureCollection;
use SuperV\Platform\Domains\Feature\ServesFeaturesTrait;
use SuperV\Platform\Domains\Manifest\Features\ManifestDroplet;
use SuperV\Platform\Domains\Manifest\ManifestCollection;
use SuperV\Platform\Domains\UI\Form\FormServiceProvider;
use SuperV\Platform\Domains\UI\Navigation\Navigation;
use SuperV\Platform\Domains\UI\Page\PageCollection;
use SuperV\Platform\Domains\View\Twig\Bridge\TwigBridgeServiceProvider;
use SuperV\Platform\Domains\View\ViewComposer;
use SuperV\Platform\Domains\View\ViewTemplate;
use SuperV\Platform\Traits\BindsToContainer;
use SuperV\Platform\Traits\RegistersRoutes;
use Illuminate\Console\Application as Artisan;

/**
 * Class PlatformServiceProvider.
 *
 * https://www.draw.io/#G0Byi-qvl6eS2ySW45cFAtVWVZVTQ
 */
class PlatformServiceProvider extends ServiceProvider
{
    use ServesFeaturesTrait;
    use RegistersRoutes;
    use BindsToContainer;

    /** @var  Platform */
    protected $platform;

    protected $providers = [
        ConsoleServiceProvider::class,
        PlatformEventProvider::class,
        TwigBridgeServiceProvider::class,
        FormServiceProvider::class,
    ];

    protected $singletons = [
        'manifests'     => ManifestCollection::class,
        'droplets'      => DropletCollection::class,
        'features'      => FeatureCollection::class,
        'pages'         => PageCollection::class,
        'ports'         => PortCollection::class,
        'view.template' => ViewTemplate::class,
        'navigation'    => Navigation::class,

    ];

    protected $bindings = [
//      MigrationRepositoryInterface::class => DatabaseMigrationRepository::class
    ];

    protected $commands = [
        EnvSet::class,
        InstallSuperV::class,
    ];

    public function register()
    {
        $this->app->register(DatabaseServiceProvider::class);
        $this->app->register(AdapterServiceProvider::class);

        // commmands needed before the platform is installed
        Artisan::starting(function ($artisan) {
            $artisan->resolveCommands($this->commands);
        });

//        app(Bridge::class)->addExtension(app(AsseticExtension::class));

        if (! env('SUPERV_INSTALLED', false)) {
            return;
        }
        $this->setupConfig();

        $this->registerBindings($this->bindings);
        $this->registerProviders($this->providers);
        $this->registerSingletons($this->singletons);
        $this->registerPlatform();
//        $this->registerDevTools();
    }

    public function boot()
    {
        if (! env('SUPERV_INSTALLED', false)) {
            return;
        }

        /**
         * Refactor idea: instead of registering routes views etc
         * by looping all droplets, first collect the droplets
         * then perform registeration depending on port, cli
         */

        $this->setupView();
        $this->bootDroplets();
        $this->manifestPlatform();

        $routes = $this->dispatch(new GetPortRoutes($this));
        $routes = array_merge($this->routes ?? [], $routes);
        $this->disperseRoutes($routes);

        $this->registerConsoleCommands();

        $this->detectActivePort();
    }

    protected function setupView(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views/', 'superv');
        $this->loadViewsFrom(storage_path(), 'storage');

        app(Factory::class)->composer('*', ViewComposer::class);

        superv('view.template')->set('menu', superv('navigation'));
    }

    protected function bootDroplets()
    {
        app(DropletManager::class)->boot();
    }

    protected function setupConfig()
    {
        foreach (glob(__DIR__.'/../config/*') as $path) {
            $key = pathinfo($path, PATHINFO_FILENAME);
            $config = config()->get("superv.{$key}", []);

            $merged = array_replace(require $path, $config);
            config()->set('platform::'.$key, $merged);
        }
    }

    protected function registerPlatform()
    {
        $this->app->singleton('superv.platform', function () {
            $this->platform = new Platform(DropletModel::where('name', 'platform')->first());

            superv('droplets')->put('superv.platform', $this->platform);

            return $this->platform;
        });
    }

    protected function registerDevTools(): void
    {
        if ($this->app->environment() == 'local') {
//            $this->app->register(SketchpadServiceProvider::class);
        }

        $this->app->register(\Barryvdh\Debugbar\ServiceProvider::class);
        $this->registerAliases([
            'Debugbar' => \Barryvdh\Debugbar\Facade::class,
        ]);
    }

    protected function registerConsoleCommands(): void
    {
        $this->dispatch(new RegisterConsoleCommands($this->platform));
//        $this->commands($this->commands);
    }

    protected function manifestPlatform(): void
    {
        $this->dispatch(new ManifestDroplet(superv('platform')));
    }

    protected function detectActivePort(): void
    {
        $this->dispatch(new DetectActivePort());
    }

    public function getResourcePath($path = null)
    {
        return $this->platform->getResourcePath($path);
    }

    public function getPath($path = null)
    {
        return $this->platform->getPath($path);
    }
}
