<?php namespace SuperV\Platform\Domains\Droplet\Model;

use SuperV\Platform\Domains\Model\EloquentModel;

class DropletModel extends EloquentModel
{
    protected $table = 'platform_droplets';
    
    public function path($path = null)
    {
        if ($path) {
            $this->setAttribute('path', $path);
            
            return $this;
        }
        
        return $this->getAttribute('path');
    }    
    
    public function namespace($namespace = null)
    {
        if ($namespace) {
            $this->setAttribute('namespace', $namespace);
            
            return $this;
        }
        
        return $this->getAttribute('namespace');
    }
    
    public function name()
    {
        return $this->name;
    }
    
    public function type()
    {
        return $this->type;
    }
    
    public function vendor()
    {
        return $this->vendor;
    }
}