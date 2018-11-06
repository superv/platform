<?php

namespace SuperV\Platform\Domains\Addon;

use SuperV\Platform\Support\Collection;

class AddonCollection extends Collection
{
    /**
     * @param $slug
     * @return \SuperV\Platform\Domains\Addon\Addon
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
     * @return \SuperV\Platform\Domains\Addon\Addon
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