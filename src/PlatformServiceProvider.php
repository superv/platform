<?php namespace SuperV\Platform;

use SuperV\Platform\Contracts\ServiceProvider;
use SuperV\Platform\Domains\Droplet\DropletManager;

class PlatformServiceProvider extends ServiceProvider
{
    protected $providers = [
        'SuperV\Platform\Adapters\AdapterServiceProvider',
        'SuperV\Nucleus\NucleusServiceProvider'
    ];
    
    protected $singletons = [
        'SuperV\Platform\Domains\Feature\FeatureCollection'      => '~',
        'SuperV\Platform\Domains\Droplet\Data\DropletCollection' => '~',
    ];
    
    protected $bindings = [];
    
    protected $commands = [
        'SuperV\Platform\Domains\Droplet\Console\DropletInstall',
        'SuperV\Platform\Domains\Droplet\Console\DropletServer',
        'SuperV\Platform\Domains\Droplet\Console\DropletDispatch',
        'SuperV\Platform\Domains\Droplet\Console\MakeDroplet',
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