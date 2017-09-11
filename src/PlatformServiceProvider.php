<?php

namespace SuperV\Platform;

use davestewart\sketchpad\SketchpadServiceProvider;
use Debugbar;
use Illuminate\View\Factory;
use SuperV\Platform\Adapters\AdapterServiceProvider;
use SuperV\Platform\Contracts\ServiceProvider;
use SuperV\Platform\Domains\Application\Console\EnvSet;
use SuperV\Platform\Domains\Application\Console\InstallSuperV;
use SuperV\Platform\Domains\Config\Jobs\AddConfigNamespace;
use SuperV\Platform\Domains\Database\DatabaseServiceProvider;
use SuperV\Platform\Domains\Database\Migration\Console\MakeMigrationCommand;
use SuperV\Platform\Domains\Database\Migration\Console\MigrateCommand;
use SuperV\Platform\Domains\Droplet\Console\DropletInstallCommand;
use SuperV\Platform\Domains\Droplet\Console\DropletSeedCommand;
use SuperV\Platform\Domains\Droplet\Console\MakeDropletCommand;
use SuperV\Platform\Domains\Droplet\DropletManager;
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

    protected $routes = [
        'platform/entries/{ticket}/delete' => [
            'as'   => 'superv::entries.delete',
            'uses' => 'SuperV\Platform\Http\Controllers\Entry\DeleteEntryController@index',
            'port' => 'acp',
        ],
        'platform/entries/{ticket}/edit'   => [
            'as'   => 'superv::entries.edit',
            'uses' => 'SuperV\Platform\Http\Controllers\Entry\EditEntryController@index',
            'port' => 'acp',
        ],
    ];

    protected $providers = [
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

    protected $bindings = [];

    protected $commands = [
        DropletInstallCommand::class,
        DropletSeedCommand::class,
        MakeMigrationCommand::class,
        MakeDropletCommand::class,
        MigrateCommand::class,
        EnvSet::class,
        InstallSuperV::class,
    ];

    public function register()
    {
        $this->app->register(DatabaseServiceProvider::class);
        $this->app->register(AdapterServiceProvider::class);

        $this->registerConsoleCommands();

//        app(Bridge::class)->addExtension(app(AsseticExtension::class));

        if (! env('SUPERV_INSTALLED', false)) {
            return;
        }

        $this->registerBindings($this->bindings);
        $this->registerProviders($this->providers);
        $this->registerSingletons($this->singletons);
        $this->registerPlatform();
        $this->registerDevTools();
    }

    public function boot()
    {
        if (! env('SUPERV_INSTALLED', false)) {
            return;
        }
        Debugbar::startMeasure('platform.boot', 'Platform Boot');

        /**
         * Refactor idea: instead of registering routes views etc
         * by looping all droplets, first collect the droplets
         * then perform registeration depending on port, cli
         */

        $this->setupView();
        $this->setupConfig();
        $this->bootDroplets();
        $this->manifestPlatform();
        $this->disperseRoutes($this->routes);
        $this->detectActivePort();

        Debugbar::stopMeasure('platform.boot');
    }

    protected function setupView(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views/', 'superv');
        $this->loadViewsFrom(storage_path(), 'storage');

        app(Factory::class)->composer('*', ViewComposer::class);

        superv('view.template')->set('menu', superv('navigation'));
    }

    protected function bootDroplets(): void
    {
        app(DropletManager::class)->boot();
    }

    protected function setupConfig(): void
    {
        $this->dispatch(new AddConfigNamespace('superv', superv('platform')->getResourcePath('config')));
    }

    protected function registerPlatform(): void
    {
        $this->app->singleton('superv.platform', function () {
            $platform = new Platform(DropletModel::where('name', 'platform')->first());

            superv('droplets')->put('superv.platform', $platform);

            return $platform;
        });
    }

    protected function registerDevTools(): void
    {
        if ($this->app->environment() == 'local') {
            $this->app->register(SketchpadServiceProvider::class);
        }

        $this->app->register(\Barryvdh\Debugbar\ServiceProvider::class);
        $this->registerAliases([
            'Debugbar' => \Barryvdh\Debugbar\Facade::class,
        ]);
    }

    protected function registerConsoleCommands(): void
    {
        $this->commands($this->commands);
    }

    protected function manifestPlatform(): void
    {
        $this->dispatch(new ManifestDroplet(superv('platform')));
    }

    protected function detectActivePort(): void
    {
        $this->dispatch(new DetectActivePort());
    }
}
