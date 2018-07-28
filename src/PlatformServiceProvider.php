<?php

namespace SuperV\Platform;

use Platform;
use SuperV\Platform\Console\SuperVInstallCommand;
use SuperV\Platform\Domains\Auth\Contracts\User;
use SuperV\Platform\Domains\Database\Migrations\Scopes as MigrationScopes;
use SuperV\Platform\Domains\Droplet\Console\DropletInstallCommand;
use SuperV\Platform\Domains\Feature\FeatureFacade;
use SuperV\Platform\Providers\BaseServiceProvider;
use SuperV\Platform\Providers\TwigServiceProvider;

class PlatformServiceProvider extends BaseServiceProvider
{
    protected $providers = [
        'SuperV\Platform\Providers\ThemeServiceProvider',
        'SuperV\Platform\Adapters\AdapterServiceProvider',
        'SuperV\Platform\Domains\Auth\AuthServiceProvider',
        'SuperV\Platform\Domains\Asset\AssetServiceProvider',
        'SuperV\Platform\Domains\Database\Migrations\MigrationServiceProvider',
    ];

    protected $_bindings = [];

    protected $aliases = [
        'Feature' => FeatureFacade::class,
    ];

    protected $_singletons = [
        'SuperV\Platform\Domains\Auth\Contracts\Users' => 'SuperV\Platform\Domains\Auth\Users',
    ];

    protected $listeners = [
        'Illuminate\Routing\Events\RouteMatched'         => 'SuperV\Platform\Listeners\RouteMatchedListener',
        'SuperV\Platform\Domains\Port\PortDetectedEvent' => 'SuperV\Platform\Listeners\PortDetectedListener',
    ];

    protected $commands = [
        SuperVInstallCommand::class,
        DropletInstallCommand::class,
    ];

    public function register()
    {
        if ($this->app->runningInConsole()) {
            MigrationScopes::register('platform', Platform::path('database/migrations'));
        }
        $this->mergeConfigFrom(
            __DIR__.'/../config/superv.php', 'superv'
        );

        /**
         * Register User Model
         */
        $this->_bindings[User::class] = Platform::config('auth.user.model');

        if (Platform::config('twig.enabled')) {
            $this->providers[] = TwigServiceProvider::class;
        }

        $this->registerBindings($this->_bindings);
        $this->registerSingletons($this->_singletons);
        $this->registerAliases($this->aliases);
        $this->registerListeners($this->listeners);
        $this->registerCommands($this->commands);

        $this->registerListeners([
            'platform.registered' => function () {
                $this->registerProviders($this->providers);
            },
        ]);

        event('platform.registered');
    }

    public function boot()
    {
        if (config('superv.installed') !== true) {
            return;
        }

//        app(Router::class)->loadFromPath(Platform::path('routes'));

//        $router = app('router');
//        $files = glob(Platform::path('routes')."/*.php");
//        foreach ($files as $file) {
//            $routes = (array)require $file;
//            foreach ($routes as $uri => $action) {
//                if (! is_array($action)) {
//                    $action = ['uses' => $action];
//                }
//                if (str_contains($uri, '@')) {
//                    list($verb, $uri) = explode('@', $uri);
//                }
//                $router->{$verb ?? 'get'}($uri, $action);
//            }
//        }

        Platform::boot();
    }
}