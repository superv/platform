<?php

namespace SuperV\Platform\Packs\Droplet;

use SuperV\Platform\Packs\Routing\Router;
use SuperV\Platform\Providers\BaseServiceProvider;

class DropletServiceProvider extends BaseServiceProvider
{
    /**
     * @var \SuperV\Platform\Packs\Droplet\Droplet
     */
    protected $droplet;

    public function setDroplet(Droplet $droplet)
    {
        $this->droplet = $droplet;

        return $this;
    }

    public function droplet()
    {
        return $this->droplet;
    }

    public function register()
    {
        parent::register();

        $this->addViewNamespaces([
            $this->droplet->entry()->slug => base_path($this->droplet->entry()->path.'/resources/views'),
        ]);
    }

    public function boot()
    {
        app(Router::class)->loadFromPath($this->droplet->entry()->path. '/routes');
    }
}