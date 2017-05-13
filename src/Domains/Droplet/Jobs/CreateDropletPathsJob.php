<?php namespace SuperV\Platform\Domains\Droplet\Jobs;



use SuperV\Platform\Contracts\Filesystem;
use SuperV\Platform\Domains\Droplet\Model\DropletModel;

class CreateDropletPathsJob
{
    /** @var \SuperV\Platform\Domains\Droplet\Model\DropletModel  */
    private $model;
    
    public function __construct(DropletModel $model)
    {
        $this->model = $model;
    }
    
    public function handle(Filesystem $filesystem)
    {
        $type = str_plural($this->model->type);
        
        $this->model->path("_/droplets/{$this->model->vendor}/{$type}/{$this->model->name}");
        
        $path = base_path($this->model->path());
        $filesystem->makeDirectory($path, 0755, true, true);
        $filesystem->makeDirectory("{$path}/src", 0755, true, true);
        $filesystem->makeDirectory("{$path}/resources", 0755, true, true);
        $filesystem->makeDirectory("{$path}/database/migrations", 0755, true, true);
    }
}