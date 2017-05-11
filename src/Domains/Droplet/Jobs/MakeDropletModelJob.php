<?php namespace SuperV\Platform\Domains\Droplet\Jobs;

use SuperV\Platform\Domains\Droplet\Model\DropletModel;

class MakeDropletModelJob
{
    private $slug;
    
    public function __construct($slug)
    {
        $this->slug = $slug;
    }
    
    public function handle()
    {
        if (!str_is('*.*.*', $this->slug)) {
            throw new \Exception("Slug should be snake case and formatted like: {vendor}.{type}.{name}");
        }
        
        list($vendor, $type, $name) = array_map(
            function ($value) {
                return str_slug(strtolower($value), '_');
            },
            explode('.', $this->slug)
        );
        
        return new DropletModel([
            'vendor' => $vendor,
            'slug'   => $this->slug,
            'type'   => str_singular($type),
            'name'   => $name,
            'namespace' => ucfirst(camel_case(($vendor == 'superv' ? 'super_v' : $vendor))). "\\" . ucfirst(camel_case(str_plural($type)))."\\" . ucfirst(camel_case($name))
        ]);
    }
}