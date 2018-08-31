<?php

namespace SuperV\Platform\Domains\Droplet;

use SuperV\Platform\Support\Collection;

class DropletCollection extends Collection
{
    public function withSlug($slug)
    {
        foreach ($this->items as $key => $droplet) {
            if ($slug === $key) {
                return $droplet;
            }
        }
    }
}