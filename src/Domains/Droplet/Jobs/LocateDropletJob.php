<?php namespace SuperV\Platform\Domains\Droplet\Jobs;

class LocateDropletJob
{
    protected $model;
    
    protected $base = '_/droplets';
    
    public function __construct($model)
    {
        $this->model = $model;
    }
    
    public function handle()
    {
        $paths = [$this->base];
        $clues = [$this->model->vendor . "/" . $this->model->name . "-" . $this->model->type];
        
        foreach ($paths as $path) {
            foreach ($clues as $clue) {
                $path = "{$path}/{$clue}";
                if (is_dir(base_path($path))) {
                    $this->model->setPath($path);
                    
                    return $path;
                }
            }
        }
        
        throw new \Exception('Droplet could not be located');
    }
}