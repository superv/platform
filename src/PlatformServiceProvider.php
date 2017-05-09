<?php namespace SuperV\Platform;

use Illuminate\Support\ServiceProvider;
use SuperV\Platform\Domains\Droplet\DropletLoader;
use SuperV\Platform\Domains\Droplet\DropletManager;
use SuperV\Ports\Api\ApiPortServiceProvider;

class PlatformServiceProvider extends ServiceProvider
{
    protected $commands = [
        'SuperV\Platform\Domains\Droplet\Console\DropletInstall',
        'SuperV\Platform\Domains\Droplet\Console\DropletServer',
        'SuperV\Platform\Domains\Droplet\Console\DropletDispatch',
    ];
    protected $singletons = [
        'SuperV\Platform\Feature\FeatureCollection'              => '~',
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
        
        // Register Ports
//        $loader = $this->app->make(DropletLoader::class);
//        $loader->load(base_path('_/superv-ports/api'));
//        $loader->register();
//
//        $this->app->register(ApiDropletServiceProvider::class);
    }
}