<?php

namespace SuperV\Platform\Domains\Droplet;

use SuperV\Platform\Contracts\Container;
use SuperV\Platform\Domains\Droplet\Model\DropletModel;
use SuperV\Platform\Domains\Droplet\Model\Droplets;

class DropletFactory
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var Droplets
     */
    private $droplets;

    /**
     * @param Container $container
     * @param Droplets  $droplets
     */
    public function __construct(Container $container, Droplets $droplets)
    {
        $this->container = $container;
        $this->droplets = $droplets;
    }

    /**
     * @param DropletModel $model
     *
     * @return Droplet
     */
    public function create(DropletModel $model)
    {
        return $this->container->makeWith($model->droplet(), ['model' => $model]);
    }

    /**
     * @param $slug
     *
     * @return Droplet
     * @throws \Exception
     */
    public function fromSlug($slug)
    {
        if (! $model = $this->droplets->withSlug($slug)) {
            throw new \Exception("Droplet model with slug {$slug} not found");
        }

        return $this->create($model);
    }
}