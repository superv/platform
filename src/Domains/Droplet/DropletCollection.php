<?php

namespace SuperV\Platform\Domains\Droplet;

use SuperV\Platform\Support\Collection;

class DropletCollection extends Collection
{
    public function ports()
    {
        $items = [];
        /** @var Droplet $droplet */
        foreach ($this->items as $droplet) {
            $type = $droplet->getType();
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

    public function byName($name)
    {
        foreach ($this->items as $droplet) {
            /** @var Droplet $droplet */
            if ($droplet->getName() == $name) {
                return $droplet;
            }
        }
    }

    public function allButPorts()
    {
        $items = [];
        /** @var Droplet $droplet */
        foreach ($this->items as $droplet) {
            if (!$droplet->isType('port')) {
                $items[] = $droplet;
            }
        }

        return new self($items);
    }
}
