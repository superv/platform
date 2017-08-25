<?php namespace SuperV\Platform;

use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use Illuminate\View\Factory;
use Laravel\Tinker\TinkerServiceProvider;
use Spatie\Tail\TailServiceProvider;
use SuperV\Platform\Adapters\AdapterServiceProvider;
use SuperV\Platform\Contracts\ServiceProvider;
use SuperV\Platform\Domains\Database\DatabaseServiceProvider;
use SuperV\Platform\Domains\Database\Migration\Console\MakeMigrationCommand;
use SuperV\Platform\Domains\Database\Migration\Console\MigrateCommand;
use SuperV\Platform\Domains\Droplet\Console\DropletDispatch;
use SuperV\Platform\Domains\Droplet\Console\DropletInstallCommand;
use SuperV\Platform\Domains\Droplet\Console\DropletServer;
use SuperV\Platform\Domains\Droplet\Console\MakeDropletCommand;
use SuperV\Platform\Domains\Droplet\DropletManager;
use SuperV\Platform\Domains\Droplet\Model\DropletCollection;
use SuperV\Platform\Domains\Droplet\Types\PortCollection;
use SuperV\Platform\Domains\Feature\FeatureCollection;
use SuperV\Platform\Domains\Manifest\ManifestCollection;
use SuperV\Platform\Domains\UI\Form\FormServiceProvider;
use SuperV\Platform\Domains\UI\Menu\Menu;
use SuperV\Platform\Domains\UI\Page\PageCollection;
use SuperV\Platform\Domains\View\ViewComposer;
use SuperV\Platform\Domains\View\ViewTemplate;
use SuperV\Platform\Http\Middleware\MiddlewareCollection;
use TwigBridge\ServiceProvider as TwigBridgeServiceProvider;

/**
 * Class PlatformServiceProvider
 *
 * https://www.draw.io/#G0Byi-qvl6eS2ySW45cFAtVWVZVTQ
 *
 * @package SuperV\Platform
 */
class PlatformServiceProvider extends ServiceProvider
{
    protected $providers = [
        DatabaseServiceProvider::class,
        AdapterServiceProvider::class,
        PlatformEventProvider::class,
        TwigBridgeServiceProvider::class,
        FormServiceProvider::class,
    ];

    protected $singletons = [
        MiddlewareCollection::class,
        ManifestCollection::class,
        DropletCollection::class,
        FeatureCollection::class,
        PageCollection::class,
        PortCollection::class,
        ViewTemplate::class,
        Menu::class,
    ];

    protected $bindings = [
        'manifests'     => ManifestCollection::class,
        'droplets'      => DropletCollection::class,
        'ports'         => PortCollection::class,
        'view.template' => ViewTemplate::class,
    ];

    protected $commands = [
        DropletInstallCommand::class,
        MakeMigrationCommand::class,
        MakeDropletCommand::class,
        MigrateCommand::class,
    ];

    public function register()
    {
        if (!env('SUPERV_INSTALLED', false)) {
            return;
        }

//        $this->app->register(TwigBridgeServiceProvider::class);
//        $this->app->register(HtmlServiceProvider::class);

//        app(Bridge::class)->addExtension(app(AsseticExtension::class));

        if ($this->app->environment() !== 'production') {
            $this->app->register(IdeHelperServiceProvider::class);
            $this->app->register(TinkerServiceProvider::class);
            $this->app->register(TailServiceProvider::class);
        }

        // Register Console Commands
        $this->commands($this->commands);

        // Register bindings.
        foreach ($this->bindings as $abstract => $concrete) {
            $this->app->bind($abstract, $concrete);
        }

        // Register providers.
        array_map(function ($provider) {
            $this->app->register($provider);
        }, $this->providers);

        // Register singletons.
        foreach ($this->singletons as $abstract => $concrete) {
            if (is_numeric($abstract) && is_string($concrete)) {
                $abstract = $concrete;
            }
            $this->app->singleton($abstract, $concrete);
//            $this->app->singleton($abstract, $concrete == '~' ? $abstract : $concrete);
        }
    }

    public function boot()
    {
        if (!env('SUPERV_INSTALLED', false)) {
            return;
        }

        $this->loadViewsFrom(__DIR__ . '/../resources/views/', 'superv');

        /* @var DropletManager $manager */
        $manager = $this->app->make('SuperV\Platform\Domains\Droplet\DropletManager');

        $manager->boot();

        superv(Factory::class)->composer('*', ViewComposer::class);

        superv('view.template')->set('menu', superv(Menu::class));

        superv('events')->dispatch('superv::app.loaded');
    }
}