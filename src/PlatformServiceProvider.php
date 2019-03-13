<?php

namespace SuperV\Platform;

use Current;
use Hub;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Collection;
use Platform;
use SuperV\Platform\Console\SuperVInstallCommand;
use SuperV\Platform\Console\SuperVUninstallCommand;
use SuperV\Platform\Domains\Addon\AddonCollection;
use SuperV\Platform\Domains\Addon\Console\AddonInstallCommand;
use SuperV\Platform\Domains\Addon\Console\AddonMakeMigrationCommand;
use SuperV\Platform\Domains\Addon\Console\AddonReinstallCommand;
use SuperV\Platform\Domains\Addon\Console\AddonRunMigrationCommand;
use SuperV\Platform\Domains\Addon\Console\AddonUninstallCommand;
use SuperV\Platform\Domains\Addon\Console\MakeAddonCommand;
use SuperV\Platform\Domains\Addon\Events\AddonBootedEvent;
use SuperV\Platform\Domains\Addon\Events\AddonInstalledEvent;
use SuperV\Platform\Domains\Addon\Listeners\AddonBootedListener;
use SuperV\Platform\Domains\Addon\Listeners\AddonInstalledListener;
use SuperV\Platform\Domains\Auth\Contracts\User;
use SuperV\Platform\Domains\Database\Migrations\MigrationServiceProvider;
use SuperV\Platform\Domains\Database\Migrations\Scopes as MigrationScopes;
use SuperV\Platform\Domains\Resource\Extension\RegisterExtensionsInPath;
use SuperV\Platform\Domains\Routing\Router;
use SuperV\Platform\Exceptions\PlatformExceptionHandler;
use SuperV\Platform\Providers\BaseServiceProvider;
use SuperV\Platform\Providers\TwigServiceProvider;

class PlatformServiceProvider extends BaseServiceProvider
{
    protected $providers = [
        Providers\ThemeServiceProvider::class,
        Adapters\AdapterServiceProvider::class,
        Domains\Auth\AuthServiceProvider::class,
        Domains\Resource\ResourceServiceProvider::class,
    ];

    protected $aliases = [
        'Platform' => 'SuperV\Platform\Facades\PlatformFacade',
        'Feature'  => 'SuperV\Platform\Domains\Feature\FeatureFacade',
        'Current'  => 'SuperV\Platform\Facades\CurrentFacade',
        'Hub'      => 'SuperV\Platform\Facades\HubFacade',
    ];

    protected $_singletons = [
        'SuperV\Platform\Domains\Auth\Contracts\Users' => 'SuperV\Platform\Domains\Auth\Users',
        'addons'                                       => AddonCollection::class,
        ExceptionHandler::class                        => PlatformExceptionHandler::class,
    ];

    protected $listeners = [
        'Illuminate\Routing\Events\RouteMatched'         => 'SuperV\Platform\Listeners\RouteMatchedListener',
        'SuperV\Platform\Domains\Port\PortDetectedEvent' => 'SuperV\Platform\Listeners\PortDetectedListener',
        AddonInstalledEvent::class                       => AddonInstalledListener::class,
        AddonBootedEvent::class                          => AddonBootedListener::class,
    ];

    protected $commands = [
        SuperVInstallCommand::class,
        SuperVUninstallCommand::class,
        AddonInstallCommand::class,
        AddonUninstallCommand::class,
        AddonReinstallCommand::class,
        MakeAddonCommand::class,
        AddonMakeMigrationCommand::class,
        AddonRunMigrationCommand::class,
    ];

    /** @var \SuperV\Platform\Platform */
    protected $platform;

    public function __construct($app)
    {
        parent::__construct($app);
        $this->platform = $app->make(\SuperV\Platform\Platform::class);
    }

    public function register()
    {
        $this->registerAliases($this->aliases);
        $this->registerCommands($this->commands);
        $this->registerSingletons($this->_singletons);

        app()->register(MigrationServiceProvider::class);

        if (! $this->platform->isInstalled()) {
            return;
        }

        $this->mergeConfigFrom(__DIR__.'/../config/superv.php', 'superv');

        $this->bindUserModel();

        $this->enableTwig();

        $this->registerListeners($this->listeners);

        $this->registerListeners([
            'platform.registered' => function () {
                $this->registerProviders($this->providers);
            },
        ]);

        $this->registerCollectionMacros();

        $this->addViewNamespaces([
            'superv' => __DIR__.'/../resources/views',
        ]);

        $this->registerDefaultPort();

        event('platform.registered');
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->registerMigrationScope();
            $this->publishConfig();
            $this->publishViews();
            $this->publishAssets();
            $this->publishSpa();
        }

        if (! $this->platform->isInstalled()) {
            return;
        }

        superv('addons')->put('superv.platform', Platform::instance());

        // Register platform resources before boot
        // so that addons can override them
        //
        RegisterExtensionsInPath::dispatch(realpath(__DIR__.'/Extensions'), 'SuperV\Platform\Extensions');

        // Boot all addons
        //
        Platform::boot();

        // Register routes needed by platform
        //
        $this->registerPlatformRoutes();

//        \Route::pattern('id', '[0-9]+');

        // Experimental query listening
        //
//        Listener::listen();
    }

    protected function bindUserModel(): void
    {
        $this->app->bind(User::class, sv_config('auth.user.model'));
    }

    protected function enableTwig(): void
    {
        if (sv_config('twig.enabled')) {
            $this->providers[] = TwigServiceProvider::class;
        }
    }

    protected function registerCollectionMacros()
    {
        Collection::macro('toAssoc', function () {
            return $this->reduce(function ($assoc, $keyValuePair) {
                list($key, $value) = $keyValuePair;
                $assoc[$key] = $value;

                return $assoc;
            }, new static);
        });

        Collection::macro('compose', function () {
            return $this->map(function ($item) {
                return sv_compose($item);
            });
        });
    }

    protected function registerMigrationScope(): void
    {
        MigrationScopes::register('platform', realpath(__DIR__.'/../database/migrations'));
    }

    protected function publishConfig(): void
    {
        $stub = __DIR__.'/../config/superv.php';

        $target = $this->app->basePath().'/config/superv.php';

        $this->publishes([$stub => $target], 'superv.config');
    }

    protected function publishViews()
    {
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/superv'),
        ], 'superv.views');
    }

    protected function publishAssets()
    {
        $this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/superv'),
        ], 'superv.assets');
    }

    protected function publishSpa()
    {
        $this->publishes([
            __DIR__.'/../resources/spa' => resource_path('superv/spa'),
        ], 'superv.spa');
    }

    protected function registerPlatformRoutes(): void
    {
        app(Router::class)->loadFromPath(Platform::path('routes'));
    }

    protected function registerDefaultPort(): void
    {
        if (Current::envIsTesting()) {
            return;
        }

        Hub::registerDefaultPort();
    }
}