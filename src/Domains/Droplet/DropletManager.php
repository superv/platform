<?php namespace SuperV\Platform\Domains\Droplet;

use SuperV\Platform\Domains\Droplet\Model\DropletRepository;
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
        DropletRepository $droplets
    ) {
        $this->paths = $paths;
        $this->integrator = $integrator;
        $this->droplets = $droplets;
        $this->loader = $loader;
    }
    
    public function register()
    {
        foreach ($this->droplets->enabled() as $model) {
            
            $this->loader->load(base_path($model->getPath()));
            $this->integrator->register($model);
        }
    }

}