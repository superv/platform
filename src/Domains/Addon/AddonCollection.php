<?php

namespace SuperV\Platform\Domains\Addon;

use Illuminate\Support\Collection;

class AddonCollection extends Collection
{
    /**
     * @param $slug
     * @return \SuperV\Platform\Domains\Addon\Addon
     */
    public function withSlug($slug)
    {
        foreach ($this->items as $key => $addon) {
            if ($slug === $key) {
                return $addon;
            }
        }
    }

    /**
     * @param $class
     * @return \SuperV\Platform\Domains\Addon\Addon
     */
    public function withClass($class)
    {
        foreach ($this->items as $key => $addon) {
            if ($class === get_class($addon)) {
                return $addon;
            }
        }
    }
}