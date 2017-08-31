<?php

namespace SuperV\Platform\Domains\Droplet\Feature;

use Illuminate\View\Factory;
use SuperV\Platform\Domains\Droplet\Droplet;
use SuperV\Platform\Domains\Feature\Feature;
use SuperV\Platform\Domains\Droplet\Types\Port;
use SuperV\Platform\Domains\Droplet\DropletProvider;
use SuperV\Platform\Domains\Droplet\Model\DropletModel;
use SuperV\Platform\Domains\Droplet\Model\DropletCollection;

/**
 * Class IntegrateDroplet.
 */
class IntegrateDroplet extends Feature
{
    /**
     * @var DropletModel
     */
    private $model;

    public function __construct(DropletModel $model)
    {
        $this->model = $model;
    }

    public function handle(DropletProvider $provider, DropletCollection $droplets, Factory $views)
    {
        $model = $this->model;

        $class = $model->droplet();

        /** @var Droplet $droplet */
        $droplet = app($class)->setModel($model);

        $droplets->put($droplet->getSlug(), $droplet);

        /*
         * If this is a Port type droplet, set its hostname from
         * env file. We will use this to extract Port from current
         * request hostname.
         */
        if ($droplet instanceof Port) {
            $portName = strtoupper($model->getName());
            $droplet->setHostname(env("SUPERV_PORTS_{$portName}_HOSTNAME"));
            app('ports')->push($droplet);
        }

        $provider->register($droplet);

        // add view namespaces
        $views->addNamespace(
            $model->slug(),
            [
                base_path($droplet->getPath('resources/views')),
            ]
        );
    }
}
