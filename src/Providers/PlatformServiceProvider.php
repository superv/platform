<?php namespace SuperV\Platform\Providers;

use SuperV\Platform\Contracts\ServiceProvider;
use SuperV\Platform\Domains\Droplet\DropletManager;

class PlatformServiceProvider extends ServiceProvider
{
    protected $commands = [
        'SuperV\Platform\Domains\Droplet\Console\DropletInstall',
        'SuperV\Platform\Domains\Droplet\Console\DropletServer',
        'SuperV\Platform\Domains\Droplet\Console\DropletDispatch',
    ];
    
    protected $singletons = [
        'SuperV\Platform\Domains\Feature\FeatureCollection'      => '~',
        'SuperV\Platform\Domains\Droplet\Data\DropletCollection' => '~',
    ];
    
    protected $bindings = [];
    
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
        // Register Console Commands
        $this->commands($this->commands);
        
        // Register bindings.
        foreach ($this->bindings as $abstract => $concrete) {
            $this->app->bind($abstract, $concrete);
        }
        
        // Register singletons.
        foreach ($this->singletons as $abstract => $concrete) {
            $this->app->singleton($abstract, $concrete == '~' ? $abstract : $concrete);
        }
    }
}