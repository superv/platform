<?php

namespace SuperV\Platform;

use Debugbar;
use Illuminate\View\Factory;
use SuperV\Platform\Adapters\AdapterServiceProvider;
use SuperV\Platform\Contracts\ServiceProvider;
use SuperV\Platform\Domains\Database\DatabaseServiceProvider;
use SuperV\Platform\Domains\Database\Migration\Console\MakeMigrationCommand;
use SuperV\Platform\Domains\Database\Migration\Console\MigrateCommand;
use SuperV\Platform\Domains\Droplet\Console\DropletInstallCommand;
use SuperV\Platform\Domains\Droplet\Console\MakeDropletCommand;
use SuperV\Platform\Domains\Droplet\DropletManager;
use SuperV\Platform\Domains\Droplet\Model\DropletCollection;
use SuperV\Platform\Domains\Droplet\Types\PortCollection;
use SuperV\Platform\Domains\Feature\FeatureCollection;
use SuperV\Platform\Domains\Manifest\ManifestCollection;
use SuperV\Platform\Domains\UI\Form\FormServiceProvider;
use SuperV\Platform\Domains\UI\Navigation\Navigation;
use SuperV\Platform\Domains\UI\Page\PageCollection;
use SuperV\Platform\Domains\View\ViewComposer;
use SuperV\Platform\Domains\View\ViewTemplate;
use SuperV\Platform\Http\Middleware\MiddlewareCollection;
use SuperV\Platform\Traits\BindsToContainer;
use SuperV\Platform\Traits\RegistersRoutes;
use TwigBridge\ServiceProvider as TwigBridgeServiceProvider;

/**
 * Class PlatformServiceProvider.
 *
 * https://www.draw.io/#G0Byi-qvl6eS2ySW45cFAtVWVZVTQ
 */
class PlatformServiceProvider extends ServiceProvider
{
    use RegistersRoutes;
    use BindsToContainer;

    protected $routes = [
        'platform/entries/{ticket}/delete' => [
            'as'   => 'superv::entries.delete',
            'uses' => 'SuperV\Platform\Http\Controllers\Entry\DeleteEntryController@index',
        ],
        'platform/entries/{ticket}/edit'   => [
            'as'   => 'superv::entries.edit',
            'uses' => 'SuperV\Platform\Http\Controllers\Entry\EditEntryController@index',
        ],
    ];

    protected $providers = [
        DatabaseServiceProvider::class,
        AdapterServiceProvider::class,
        PlatformEventProvider::class,
        TwigBridgeServiceProvider::class,
        FormServiceProvider::class,
    ];

    protected $singletons = [
        'middlewares'   => MiddlewareCollection::class,
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
        MakeMigrationCommand::class,
        MakeDropletCommand::class,
        MigrateCommand::class,
    ];

    public function register()
    {
        if (! env('SUPERV_INSTALLED', false)) {
            return;
        }

//        app(Bridge::class)->addExtension(app(AsseticExtension::class));

        // Register Console Commands
        $this->commands($this->commands);

        $this->registerBindings($this->bindings);
        $this->registerProviders($this->providers);
        $this->registerSingletons($this->singletons);
        $this->registerRoutes($this->routes);
    }

    public function boot()
    {
        Debugbar::startMeasure('platform.boot', 'Platform Boot');
        if (! env('SUPERV_INSTALLED', false)) {
            return;
        }

        $this->loadViewsFrom(__DIR__.'/../resources/views/', 'superv');

        /* @var DropletManager $manager */
        $manager = $this->app->make('SuperV\Platform\Domains\Droplet\DropletManager');

        $manager->boot();

        app(Factory::class)->composer('*', ViewComposer::class);

        superv('view.template')->set('menu', superv('navigation'));

        app('events')->dispatch('superv::app.loaded');

        Debugbar::stopMeasure('platform.boot');
    }
}
