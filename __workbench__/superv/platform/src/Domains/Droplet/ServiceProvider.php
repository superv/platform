<?php

namespace SuperV\Platform\Domains\Droplet;

use SuperV\Platform\PlatformServiceProvider;

class ServiceProvider extends PlatformServiceProvider
{
    /**
     * @var \SuperV\Platform\Domains\Droplet\Droplet
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

    public function setBindings($bindings)
    {
        $this->bindings = $bindings;
    }

    public function setAliases($aliases)
    {
        $this->aliases = $aliases;
    }

    public function setSingletons($singletons)
    {
        $this->singletons = $singletons;
    }

    public function register()
    {
        parent::register();

        $this->addViewNamespaces([
            $this->droplet->entry()->name => $this->droplet->entry()->path.'/resources/views',
            $this->droplet->entry()->slug =>  $this->droplet->entry()->path.'/resources/views'
        ]);

    }
}