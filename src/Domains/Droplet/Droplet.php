<?php namespace SuperV\Platform\Domains\Droplet;

use SuperV\Platform\Domains\Droplet\Model\DropletModel;

class Droplet
{
    /** @var  DropletModel */
    protected $model;
    
    public function newServiceProvider()
    {
        return app()->make($this->getServiceProvider(), [app(), $this]);
    }
    
    public function getServiceProvider()
    {
        return get_class($this) . 'ServiceProvider';
    }
    
    public function getSlug()
    {
        return $this->model->slug;
    }
    
    public function setModel(DropletModel $model)
    {
        $this->model = $model;
        
        return $this;
    }
}