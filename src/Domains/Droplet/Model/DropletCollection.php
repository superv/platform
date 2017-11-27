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
