<?php

namespace SuperV\Platform\Domains\Droplet\Feature;

use Illuminate\View\Factory;
use SuperV\Platform\Domains\Config\Jobs\EnableConfigFiles;
use SuperV\Platform\Domains\Droplet\Droplet;
use SuperV\Platform\Domains\Droplet\DropletFactory;
use SuperV\Platform\Domains\Droplet\DropletProvider;
use SuperV\Platform\Domains\Droplet\Model\DropletCollection;
use SuperV\Platform\Domains\Droplet\Model\DropletModel;
use SuperV\Platform\Domains\Droplet\Port\Port;
use SuperV\Platform\Domains\Feature\Feature;

/**
 * Class IntegrateDroplet.
 */
class IntegrateDroplet extends Feature
{
    /**
     * @var Droplet
     */
    private $droplet;

    public function __construct(Droplet $droplet)
    {
        $this->droplet = $droplet;
    }

    public function handle(DropletProvider $provider, DropletCollection $droplets, Factory $views)
    {
        $droplet = $this->droplet;
//        $model = $this->model;
//
//        $class = $model->droplet();
//
//        /** @var Droplet $droplet */
//        $droplet = app($class)->setModel($model);
        $this->dispatch(new EnableConfigFiles($droplet));

        $droplets->put($droplet->getSlug(), $droplet);


        $provider->register($droplet);

        /**
         *  Add namespaces for view and config,
         *  Both for "name::" and "superv.type.name::"
         */
        $viewsPath = [base_path($droplet->getPath('resources/views'))];
        $views->addNamespace($droplet->getSlug(), $viewsPath);
        $views->addNamespace($droplet->getName(), $viewsPath);

//        $this->dispatch(new EnableConfigFiles($droplet));
    }
}
