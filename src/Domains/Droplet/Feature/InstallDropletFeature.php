<?php namespace SuperV\Platform\Domains\Droplet\Feature;

use SuperV\Platform\Domains\Composer\Jobs\GetBaseNamespaceJob;
use SuperV\Platform\Domains\Composer\Jobs\GetComposerArrayJob;
use SuperV\Platform\Domains\Droplet\Jobs\LocateDropletJob;
use SuperV\Platform\Domains\Droplet\Jobs\MakeDropletModelJob;
use SuperV\Platform\Domains\Feature\Feature;
use SuperV\Platform\Domains\Droplet\DropletLoader;
use SuperV\Platform\Domains\Droplet\DropletPaths;

class InstallDropletFeature extends Feature
{
    private $slug;
    
    public function __construct($slug)
    {
        $this->slug = $slug;
    }
    
    public function handle(DropletPaths $paths, DropletLoader $loader)
    {
        $model = $this->run(new MakeDropletModelJob($this->slug));
        
        $this->run(new LocateDropletJob($model));
        
        $composer = $this->run(new GetComposerArrayJob(base_path($model->getPath())));
        $namespace = $this->run(new GetBaseNamespaceJob($composer));
        
        $model->setNamespace($namespace);
        
        $model->enabled = true;
        $model->slug = $model->vendor . '.' . $model->type . '.' . $model->name;
        
        return $model->save();
    }
}