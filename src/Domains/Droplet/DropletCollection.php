<?php

namespace SuperV\Platform\Domains\Droplet;

use SuperV\Platform\Support\Collection;

class DropletCollection extends Collection
{
    /**
     * @param $slug
     * @return \SuperV\Platform\Domains\Droplet\Droplet
     */
    public function withSlug($slug)
    {
        foreach ($this->items as $key => $droplet) {
            if ($slug === $key) {
                return $droplet;
            }
        }
    }

    /**
     * @param $class
     * @return \SuperV\Platform\Domains\Droplet\Droplet
     */
    public function withClass($class)
    {
        foreach ($this->items as $key => $droplet) {
            if ($class === get_class($droplet)) {
                return $droplet;
            }
        }
    }
}