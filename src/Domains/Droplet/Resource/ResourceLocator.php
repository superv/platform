<?php namespace SuperV\Platform\Domains\Droplet\Resource;

use SuperV\Platform\Domains\Droplet\Model\DropletCollection;

class ResourceLocator
{
    /**
     * @var DropletCollection
     */
    private $droplets;

    public function __construct(DropletCollection $droplets)
    {
        $this->droplets = $droplets;
    }

    public function locate($namespace, $type = null)
    {
        if (!str_is('*::*', $namespace)) {
            return null;
        }
        list($droplet, $resource) = explode('::', $namespace);
        $droplet = $this->droplets->get($droplet);

        $location = base_path($droplet->getPath() . '/resources/' . str_plural($type) . '/' . $resource . '.' . $type);

        return $location;
    }
}