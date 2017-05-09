<?php namespace SuperV\Platform\Domains\Droplet;

class DropletPaths
{
    protected $base = '_/droplets';
    
    public function getPath($namespace)
    {
        foreach ($this->all() as $path) {
            $path = "{$path}/{$namespace}";
            if (is_dir(base_path($path)) ) {
                return $path;
            }
        }
    }
    
    public function all()
    {
        return [$this->base()];
    }
    
    public function base()
    {
        return $this->base;
        //return $this->vendorAddons(glob("{$path}/*", GLOB_ONLYDIR));
    }
    
    protected function vendorAddons($directories)
    {
        $paths = [];
        
        foreach ($directories as $vendor) {
            foreach (glob("{$vendor}/*", GLOB_ONLYDIR) as $addon) {
                $paths[] = $addon;
            }
        }
        
        return $paths;
    }
}