<?php namespace SuperV\Platform;

use SuperV\Platform\Contracts\ServiceProvider;
use SuperV\Platform\Domains\Droplet\DropletManager;

class PlatformServiceProvider extends ServiceProvider
{
    protected $providers = [
        'SuperV\Nucleus\NucleusServiceProvider',
        'SuperV\Platform\Adapters\AdapterServiceProvider',
        'SuperV\Platform\Domains\Database\DatabaseServiceProvider',
        'SuperV\Modules\Console\ConsoleModuleServiceProvider',
    ];

    protected $singletons = [
        'SuperV\Platform\Domains\Feature\FeatureCollection'       => '~',
        'SuperV\Platform\Domains\Droplet\Model\DropletCollection' => '~',
        'SuperV\Platform\Domains\Droplet\Types\PortCollection'    => '~',
    ];

    protected $bindings = [
        'ports' => 'SuperV\Platform\Domains\Droplet\Types\PortCollection',
    ];

    protected $commands = [
        'SuperV\Platform\Domains\Database\Migration\Console\MigrateCommand',
        'SuperV\Platform\Domains\Database\Migration\Console\MakeMigrationCommand',
        'SuperV\Platform\Domains\Droplet\Console\MakeDropletCommand',
        'SuperV\Platform\Domains\Droplet\Console\DropletServer',
        'SuperV\Platform\Domains\Droplet\Console\DropletInstallCommand',
        'SuperV\Platform\Domains\Droplet\Console\DropletDispatch',
    ];

    public function boot()
    {
        if (!env('SUPERV_INSTALLED', false)) {
            return;
        }
        $this->app->booted(
            function () {
                /* @var DropletManager $manager */
                $manager = $this->app->make('SuperV\Platform\Domains\Droplet\DropletManager');

                $manager->register();
            }
        );
    }

    public function register()
    {
        if (!env('SUPERV_INSTALLED', false)) {
            return;
        }

        if ($this->app->environment() !== 'production') {
            $this->app->register('Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider');
            $this->app->register('Laravel\Tinker\TinkerServiceProvider');
            $this->app->register('Spatie\Tail\TailServiceProvider');
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
            $this->app->singleton($abstract, $concrete == '~' ? $abstract : $concrete);
        }
    }
}