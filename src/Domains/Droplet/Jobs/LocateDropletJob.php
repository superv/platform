<?php namespace SuperV\Platform\Domains\Droplet\Jobs;

use SuperV\Platform\Domains\Droplet\Model\DropletModel;

class LocateDropletJob
{
    /** @var \SuperV\Platform\Domains\Droplet\Model\DropletModel */
    protected $model;
    
    protected $paths = [
        '_superv/droplets',
        'droplets',
    ];
    
    public function __construct(DropletModel $model)
    {
        $this->model = $model;
    }
    
    public function handle()
    {
        $clues = [$this->model->path];
        
        foreach ($this->paths as $path) {
            foreach ($clues as $clue) {
                $path = "{$path}/{$clue}";
                if (is_dir(base_path($path))) {
                    $this->model->path($path);
                    
                    return $path;
                }
            }
        }
        
        throw new \Exception('Droplet could not be located');
    }
}