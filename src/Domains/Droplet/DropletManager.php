<?php

namespace SuperV\Platform\Domains\Droplet;

use SuperV\Platform\Domains\Droplet\Feature\IntegrateDroplet;
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

    public function __construct(DropletCollection $droplets)
    {
        $this->droplets = $droplets;
    }

    public function load()
    {
        $enabledDroplets = Droplet::query()->where('enabled', true)->get();

        /** @var Droplet $droplet */
        foreach ($enabledDroplets as $droplet) {
            $droplet = $droplet->newDropletInstance();
            $this->droplets->put($droplet->getSlug(), $droplet);

            /*
             * If this is a Port type droplet, set its hostname from
             * env file. We will use this to extract Port from
             * the current request hostname.
             */
            if ($droplet->isType('port')) {
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

    public function boot()
    {
        $droplets = $this->droplets->allButPorts();

        foreach ($droplets as $model) {
            $this->serve(new IntegrateDroplet($model));
        }
    }
}
