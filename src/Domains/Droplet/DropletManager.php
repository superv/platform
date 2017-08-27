<?php namespace SuperV\Platform\Domains\Droplet;

use SuperV\Platform\Domains\Droplet\Feature\IntegrateDroplet;
use SuperV\Platform\Domains\Droplet\Feature\LoadDroplet;
use SuperV\Platform\Domains\Droplet\Model\DropletRepository;
use SuperV\Platform\Domains\Droplet\Model\Droplets;
use SuperV\Platform\Domains\Feature\ServesFeaturesTrait;

class DropletManager
{
    use ServesFeaturesTrait;

    /**
     * @var DropletPaths
     */
    private $paths;

    /**
     * @var DropletRepository
     */
    private $droplets;

    public function __construct(
        DropletPaths $paths,
        Droplets $droplets
    ) {
        $this->paths = $paths;
        $this->droplets = $droplets;
    }

    public function boot()
    {
        /** @var \SuperV\Platform\Domains\Droplet\Model\DropletModel $model */
        foreach ($this->droplets->enabled() as $model) {
            \Debugbar::startMeasure("droplet.{$model->slug()}", $model->getName());

            \Debugbar::startMeasure('load', 'Load');
            $this->serve(new LoadDroplet(base_path($model->path())));
            \Debugbar::stopMeasure('load', 'Load');

            $this->serve(new IntegrateDroplet($model));
            \Debugbar::stopMeasure("droplet.{$model->slug()}");
        }
    }
}