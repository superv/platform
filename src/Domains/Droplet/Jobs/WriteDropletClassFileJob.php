<?php namespace SuperV\Platform\Domains\Droplet\Jobs;

class WriteDropletClassFileJob
{
    private $model;
    
    public function __construct($model)
    {
        $this->model = $model;
    }
    
    public function handle()
    {
    
    }
}