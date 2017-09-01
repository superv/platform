<?php

namespace SuperV\Platform\Domains\Droplet\Model;

use SuperV\Platform\Domains\Droplet\Droplet;
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
        $droplets = $this->query->where('enabled', true)->orderBy('type', 'DESC')->get();

        //$droplets->map(function(DropletModel $model) {
        //    return app($model->droplet(), ['model' => $model]);
        //});
        return $droplets;
    }
}
