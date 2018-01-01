<?php

namespace SuperV\Platform\Domains\Droplet;

use SuperV\Platform\Contracts\Container;
use SuperV\Platform\Domains\Droplet\Droplet;
use SuperV\Platform\Domains\Droplet\Droplets;
use SuperV\Platform\Traits\EnforcableTrait;

class DropletFactory
{
    use EnforcableTrait;

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
     * @param Droplet $model
     *
     * @return Droplet
     */
    public function create(Droplet $model)
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
        if (! $droplet = $this->droplets->withSlug($slug)) {
            throw new \Exception("Droplet model with slug {$slug} not found");
        }

        return $droplet;
    }
}