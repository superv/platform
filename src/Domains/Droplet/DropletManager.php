<?php

namespace SuperV\Platform\Domains\Droplet;

use SuperV\Platform\Domains\Droplet\Feature\IntegrateDroplet;
use SuperV\Platform\Domains\Droplet\Feature\LoadDroplet;
use SuperV\Platform\Domains\Droplet\Model\DropletCollection;
use SuperV\Platform\Domains\Droplet\Model\DropletModel;
use SuperV\Platform\Domains\Droplet\Model\Droplets;
use SuperV\Platform\Domains\Droplet\Port\Port;
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

    public function __construct(DropletPaths $paths, DropletCollection $droplets)
    {
        $this->paths = $paths;
        $this->droplets = $droplets;
    }

    public function load()
    {
        /** @var DropletModel $model  */
        foreach(app(Droplets::class)->enabled()->get() as $model) {
            $this->serve(new LoadDroplet(base_path($model->getPath())));

            /** @var Droplet $droplet */
            $droplet = app($model->droplet())->setModel($model);

            $this->droplets->put($droplet->getSlug(), $droplet);

            /*
             * If this is a Port type droplet, set its hostname from
             * env file. We will use this to extract Port from current
             * request hostname.
             */
            if ($droplet instanceof Port) {
                $portName = strtoupper($droplet->getName());
                $droplet->setHostname(env("SUPERV_PORTS_{$portName}_HOSTNAME"));
                superv('ports')->push($droplet);

            }

        }
    }

    public function bootPorts()
    {
        $ports = $this->droplets->ports();

//        /** @var Port $port */
//        foreach($ports as $port) {
//            $this->serve(new LoadDroplet(base_path($port->getPath())));
//        }
        foreach($ports as $model) {
           $this->bootDroplet($model);
        }
    }

    public function bootAllButPorts()
    {
        $droplets = $this->droplets->allButPorts();

//        /** @var Droplet $droplet */
//        foreach ($droplets as $droplet) {
//            $this->serve(new LoadDroplet(base_path($droplet->getPath())));
//        }

        foreach ($droplets as $model) {

            $this->bootDroplet($model);
        }
    }

    private function bootDroplet($model)
    {
        $this->serve(new IntegrateDroplet($model));
    }
}
