<?php

namespace SuperV\Platform\Domains\Droplet;

use SuperV\Platform\Domains\Droplet\Feature\IntegrateDroplet;
use SuperV\Platform\Domains\Droplet\Model\DropletCollection;
use SuperV\Platform\Domains\Droplet\Model\DropletModel;
use SuperV\Platform\Domains\Droplet\Model\Droplets;
use SuperV\Platform\Domains\Droplet\Port\Port;
use SuperV\Platform\Domains\Feature\ServesFeaturesTrait;
use SuperV\Platform\Support\Inflator;

class DropletManager
{
    use ServesFeaturesTrait;

    /**
     * @var Droplets
     */
    private $droplets;

    public function __construct( DropletCollection $droplets)
    {
        $this->droplets = $droplets;
    }

    public function load()
    {
        /** @var DropletModel $model */
        foreach (app(Droplets::class)->enabled()->get() as $model) {
            /** @var Droplet $droplet */
            $droplet = app($model->droplet())->setModel($model);

            $this->droplets->put($droplet->getSlug(), $droplet);

            /*
             * If this is a Port type droplet, set its hostname from
             * env file. We will use this to extract Port from current
             * request hostname.
             */
            if ($droplet instanceof Port) {
                $portName = strtolower($droplet->getName());
                if ($config = config("superv.ports.{$portName}")) {
                    app(Inflator::class)->inflate($droplet, $config);
                }
                superv('ports')->push($droplet);
            }
        }
    }

    public function bootPorts()
    {
        $ports = $this->droplets->ports();

        foreach ($ports as $model) {
            $this->serve(new IntegrateDroplet($model));
        }
    }

    public function bootAllButPorts()
    {
        $droplets = $this->droplets->allButPorts();

        foreach ($droplets as $model) {
            $this->serve(new IntegrateDroplet($model));
        }
    }
}
