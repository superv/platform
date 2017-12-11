<?php

namespace SuperV\Platform\Domains\Droplet\Model;

use SuperV\Platform\Domains\Droplet\Droplet;
use SuperV\Platform\Platform;
use SuperV\Platform\Support\Collection;

class DropletCollection extends Collection
{
    public function ports()
    {
        $items = [];
        /** @var Droplet $droplet */
        foreach ($this->items as $droplet) {
            if ($droplet->isType('port')) {
                $items[] = $droplet;
            }
        }

        return new self($items);
    }

    public function bySlug($slug)
    {
        foreach ($this->items as $droplet) {
            /** @var Droplet $droplet */
            if ($droplet->getSlug() == $slug) {
                return $droplet;
            }
        }
    }

    public function allButPorts()
    {
        $items = [];
        /** @var Droplet $droplet */
        foreach ($this->items as $droplet) {
            if (!$droplet->isType('port') && !$droplet instanceof Platform) {
                $items[] = $droplet;
            }
        }

        return new self($items);
    }
}
