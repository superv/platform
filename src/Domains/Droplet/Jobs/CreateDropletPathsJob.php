<?php namespace SuperV\Platform\Domains\Droplet\Jobs;



use SuperV\Platform\Contracts\Filesystem;

class CreateDropletPathsJob
{
    private $model;
    
    public function __construct($model)
    {
        $this->model = $model;
    }
    
    public function handle(Filesystem $filesystem)
    {
        $path = base_path("_/droplets/{$this->model->vendor}/{$this->model->name}-{$this->model->type}");
        
        $filesystem->makeDirectory($path, 0755, true, true);
        
        return $path;
    }
}