<?php namespace SuperV\Platform\Domains\Droplet;

use SuperV\Platform\Domains\Droplet\Model\DropletCollection;
use SuperV\Platform\Domains\Droplet\Model\DropletRepository;
use SuperV\Platform\Domains\Droplet\Model\Droplets;
use SuperV\Platform\Domains\Feature\ServesFeaturesTrait;

class DropletManager
{
    use ServesFeaturesTrait;
    
    /**
     * @var DropletPaths
     */
    private $paths;
    
    /**
     * @var DropletIntegrator
     */
    private $integrator;
    
    /**
     * @var DropletRepository
     */
    private $droplets;
    
    /**
     * @var DropletLoader
     */
    private $loader;
    
    public function __construct(
        DropletPaths $paths,
        DropletIntegrator $integrator,
        DropletLoader $loader,
        Droplets $droplets
    ) {
        $this->paths = $paths;
        $this->integrator = $integrator;
        $this->droplets = $droplets;
        $this->loader = $loader;
    }
    
    public function register()
    {
        /** @var \SuperV\Platform\Domains\Droplet\Model\DropletModel $model */
        foreach ($this->droplets->enabled() as $model) {
            
            $this->loader->load(base_path($model->path()));
            $this->integrator->register($model);
        }

    }

}