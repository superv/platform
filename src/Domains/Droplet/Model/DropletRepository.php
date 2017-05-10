<?php namespace SuperV\Platform\Domains\Droplet\Model;

class DropletRepository
{
    /** @var DropletModel */
    protected $model;
    
    public function __construct(DropletModel $model)
    {
        $this->model = $model;
    }
    
    public function all()
    {
        return $this->model->all();
    }
    
    public function enabled()
    {
        return $this->model->where('enabled', true)->get();
    }
}