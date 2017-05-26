<?php namespace SuperV\Platform\Domains\Droplet;

use Illuminate\View\Factory;
use SuperV\Platform\Domains\Droplet\Model\DropletCollection;
use SuperV\Platform\Domains\Droplet\Model\DropletModel;
use SuperV\Platform\Domains\Droplet\Types\Port;

class DropletIntegrator
{
    /**
     * @var DropletProvider
     */
    private $provider;

    /**
     * @var DropletCollection
     */
    private $droplets;

    /**
     * @var \Illuminate\View\Factory
     */
    private $views;

    public function __construct(DropletProvider $provider, DropletCollection $droplets, Factory $views)
    {
        $this->provider = $provider;
        $this->droplets = $droplets;
        $this->views = $views;
    }

    public function register(DropletModel $model)
    {
        $class = $model->droplet();

        /** @var Droplet $droplet */
        $droplet = app($class)->setModel($model);

        $this->provider->register($droplet);
        $this->droplets->put($droplet->getSlug(), $droplet);

        if ($droplet instanceof Port) {
            $portName = strtoupper($model->name());
            $droplet->setHostname(env("PORTS_{$portName}_HOSTNAME"));
            superv('ports')->push($droplet);
        }

        // add view namespaces
        $this->views->addNamespace(
            $model->slug(),
            [
                base_path($droplet->getPath('resources/views')),
            ]
        );
    }
}