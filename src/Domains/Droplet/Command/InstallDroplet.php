<?php namespace SuperV\Platform\Domains\Droplet\Command;

use SuperV\Platform\Domains\Droplet\Data\DropletModel;
use SuperV\Platform\Domains\Droplet\Data\DropletRepository;
use SuperV\Platform\Domains\Droplet\DropletLoader;
use SuperV\Platform\Domains\Droplet\DropletPaths;

class InstallDroplet
{
    private $namespace;
    
    public function __construct($namespace)
    {
        $this->namespace = $namespace;
    }
    
    public function handle(DropletPaths $paths, DropletLoader $loader, DropletRepository $droplets)
    {
        $model = new DropletModel();
        if (!$path = $paths->getPath(str_replace('.', '-', $this->namespace))) {
            throw new \InvalidArgumentException('Droplet path not found');
        }
        
        $model->setPath($path);
        if (!$loader->locate($model)) {
            throw new \InvalidArgumentException('Droplet composer file could not be located');
        }
        
        $model->enabled = true;
        $model->slug = $model->vendor . '.' . $model->type . '.' . $model->name;
        
        return $model->save();
    }
}