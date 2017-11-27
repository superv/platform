<?php

namespace SuperV\Platform\Domains\Droplet\Model;

use SuperV\Platform\Domains\Model\EloquentRepository;

class Droplets extends EloquentRepository
{
    public function withSlug($slug)
    {
        /**
         * Try to find by name where not ambiguous.
         */
        $droplets = $this->query->where('name', $slug)->get();
        if ($droplets->count() == 1) {
            return $droplets->first();
        }

        return parent::withSlug($slug);
    }

    public function enabled()
    {
        return $this->query->where('enabled', true);
    }

    public function ports()
    {
        return $this->query->where('type', 'port');
    }
    public function allButPorts()
    {
        return $this->query->where('type', '!=', 'port');
    }
}
