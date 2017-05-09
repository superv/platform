<?php namespace SuperV\Platform\Domains\Droplet\Data;

use SuperV\Platform\Model\EloquentModel;

class DropletModel extends EloquentModel
{
    protected $table = 'platform_droplets';
    
    public function getPath()
    {
        return $this->getAttribute('path');
    }
    
    public function setPath($path)
    {
        $this->setAttribute('path', $path);
        
        return $this;
    }
    
    public function setType($type)
    {
        $this->setAttribute('type', str_replace('superv-', '', $type));
        
        return $this;
    }
    
    public function setNamespace($namespace)
    {
        $this->setAttribute('namespace', $namespace);
        
        return $this;
    }
    
    public function setName($name)
    {
        $this->setAttribute('name', $name);
        
        return $this;
    }
    
    public function setVendor($vendor)
    {
        $this->setAttribute('vendor', $vendor);
        
        return $this;
    }
}