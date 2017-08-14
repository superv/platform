<?php namespace SuperV\Platform\Domains\Droplet\Model;


class DropletModel extends DropletEntryModel
{
    public function path($path = null)
    {
        if ($path) {
            $this->setAttribute('path', $path);
            
            return $this;
        }
        
        return $this->getAttribute('path');
    }

    public function getPath($path = null)
    {
        return $this->path . ($path ? '/' . $path : '');
    }
    
    public function namespace($namespace = null)
    {
        if ($namespace) {
            $this->setAttribute('namespace', $namespace);
            
            return $this;
        }
        
        return $this->getAttribute('namespace');
    }

    public function droplet()
    {
        return $this->namespace() . "\\" . studly_case("{$this->name}_{$this->type}");
    }
    
    public function getName()
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

    public function slug()
    {
        return $this->slug;
    }
}