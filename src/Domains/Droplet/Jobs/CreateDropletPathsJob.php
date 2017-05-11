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
        $this->model->path("_/droplets/{$this->model->vendor}/{$this->model->name}-{$this->model->type}");
        
        $path = base_path($this->model->path());
        $filesystem->makeDirectory($path, 0755, true, true);
        $filesystem->makeDirectory("{$path}/src", 0755, true, true);
        $filesystem->makeDirectory("{$path}/resources", 0755, true, true);
        $filesystem->makeDirectory("{$path}/database/migrations", 0755, true, true);
    }
}