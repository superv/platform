<?php

namespace SuperV\Platform\Domains\Droplet;

use SuperV\Platform\Domains\Droplet\Feature\IntegrateDroplet;
use SuperV\Platform\Domains\Droplet\Feature\LoadDroplet;
use SuperV\Platform\Domains\Droplet\Model\Droplets;
use SuperV\Platform\Domains\Feature\ServesFeaturesTrait;
use SuperV\Platform\Support\Collection;

class DropletManager
{
    use ServesFeaturesTrait;

    /**
     * @var DropletPaths
     */
    private $paths;

    /**
     * @var Droplets
     */
    private $droplets;

    public function __construct(DropletPaths $paths, Droplets $droplets)
    {
        $this->paths = $paths;
        $this->droplets = $droplets;
    }

    public function boot()
    {
        /** @var Collection $enabled */
        $enabled = $this->droplets->enabled();

        foreach ($enabled->get() as $model) {
            $this->serve(new LoadDroplet(base_path($model->path())));
        }

        /** @var \SuperV\Platform\Domains\Droplet\Model\DropletModel $model */
        foreach ($enabled->where('type', 'port')->get() as $model) {

            $this->bootDroplet($model);
        }

        foreach ($this->droplets->enabled()->where('type', '!=', 'port')->get() as $model) {

            $this->bootDroplet($model);
        }
    }

    private function bootDroplet($model)
    {
//        $this->serve(new LoadDroplet(base_path($model->path())));

        $this->serve(new IntegrateDroplet($model));
    }
}
