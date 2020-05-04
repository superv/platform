<?php

namespace SuperV\Platform;

use Current;
use Event;
use Hub;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Collection;
use Platform;
use SuperV\Platform\Console\InstallSuperVCommand;
use SuperV\Platform\Console\SuperVUninstallCommand;
use SuperV\Platform\Domains\Addon\AddonCollection;
use SuperV\Platform\Domains\Addon\Console\AddonInstallCommand;
use SuperV\Platform\Domains\Addon\Console\AddonMakeMigrationCommand;
use SuperV\Platform\Domains\Addon\Console\AddonReinstallCommand;
use SuperV\Platform\Domains\Addon\Console\AddonRunMigrationCommand;
use SuperV\Platform\Domains\Addon\Console\AddonUninstallCommand;
use SuperV\Platform\Domains\Addon\Console\MakeAddonCommand;
use SuperV\Platform\Domains\Addon\Console\MakeModuleCommand;
use SuperV\Platform\Domains\Addon\Console\MakePanelCommand;
use SuperV\Platform\Domains\Addon\Events\AddonBootedEvent;
use SuperV\Platform\Domains\Addon\Events\AddonInstalledEvent;
use SuperV\Platform\Domains\Addon\Listeners\AddonBootedListener;
use SuperV\Platform\Domains\Addon\Listeners\AddonInstalledListener;
use SuperV\Platform\Domains\Auth\Contracts\User;
use SuperV\Platform\Domains\Database\Migrations\MigrationServiceProvider;
use SuperV\Platform\Domains\Database\Migrations\Scopes as MigrationScopes;
use SuperV\Platform\Domains\Port\PortDetectedEvent;
use SuperV\Platform\Domains\Resource\Hook\HookManager;
use SuperV\Platform\Domains\Routing\Router;
use SuperV\Platform\Exceptions\PlatformExceptionHandler;
use SuperV\Platform\Listeners\PortDetectedListener;
use SuperV\Platform\Listeners\RouteMatchedListener;
use SuperV\Platform\Providers\BaseServiceProvider;

class PlatformServiceProvider extends BaseServiceProvider
{
    protected $providers = [
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
        'platform'                                     => \SuperV\Platform\Platform::class,
        ExceptionHandler::class                        => PlatformExceptionHandler::class,
    ];

    protected $listeners = [
        \Illuminate\Routing\Events\RouteMatched::class => RouteMatchedListener::class,
        PortDetectedEvent::class                       => PortDetectedListener::class,
        AddonInstalledEvent::class                     => AddonInstalledListener::class,
        AddonBootedEvent::class                        => AddonBootedListener::class,
    ];

    protected $commands = [
        InstallSuperVCommand::class,
        SuperVUninstallCommand::class,
        AddonInstallCommand::class,
        AddonUninstallCommand::class,
        AddonReinstallCommand::class,
        MakeAddonCommand::class,
        MakeModuleCommand::class,
        MakePanelCommand::class,
        AddonMakeMigrationCommand::class,
        AddonRunMigrationCommand::class,
    ];

    public function registerBase(): void
    {
        $this->registerAliases($this->aliases);
        $this->registerCommands($this->commands);
        $this->registerSingletons($this->_singletons);
        app()->register(MigrationServiceProvider::class);

        $this->registerCollectionMacros();
    }

    public function register()
    {
        $this->registerBase();

        if (! $this->platform->isInstalled()) {
            return;
        }

        $this->mergeConfigFrom(__DIR__.'/../config/superv.php', 'superv');

        $this->bindUserModel();

        $this->registerPlatformListeners();

        $this->addViewNamespaces([
            'superv' => realpath(__DIR__.'/../resources/views'),
        ]);

        $this->registerDefaultPort();

        $this->registerPlatformProviders();

        Event::dispatch('platform.registered');
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->registerMigrationScope();
            $this->publishConfig();
        }

        if (! $this->platform->isInstalled()) {
            return;
        }

        superv('addons')->put('superv.platform', $this->platform);

        // Boot all addons
        //
        $this->platform->boot();

        HookManager::resolve()->scan($this->platform->realPath('src/Resources'));

        // Register routes needed by platform
        //
        $this->registerPlatformRoutes();

        $this->setupTranslations();
    }

    public function registerPlatformProviders()
    {
        $this->registerProviders($this->providers);
    }

    public function registerPlatformListeners()
    {
        $this->registerListeners($this->listeners);
    }

    public function bindUserModel(): void
    {
        $this->app->bind(User::class, sv_config('auth.user.model'));
    }

    protected function setupTranslations()
    {
        $this->loadTranslationsFrom($this->platform->realPath('resources/lang'), 'platform');

        $this->loadJsonTranslationsFrom($this->platform->realPath('resources/lang'));

        $this->publishes([
            $this->platform->realPath('resources/lang') => resource_path('lang/vendor/superv'),
        ]);
    }

    protected function registerCollectionMacros()
    {
        Collection::macro('toAssoc', function () {
            return $this->reduce(function ($assoc, $keyValuePair) {
                [$key, $value] = $keyValuePair;
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
        MigrationScopes::register('sv.platform', realpath(__DIR__.'/../database/migrations'));
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
