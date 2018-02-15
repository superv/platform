<?php

namespace SuperV\Platform\Domains\Droplet;

use SuperV\Platform\Domains\Routing\Router;
use SuperV\Platform\Providers\BaseServiceProvider;

class DropletServiceProvider extends BaseServiceProvider
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

    public function register()
    {
        parent::register();

        $this->addViewNamespaces([
            $this->droplet->entry()->slug => base_path($this->droplet->entry()->path.'/resources/views'),
        ]);

        $scopes = config('superv.migrations.scopes');
        $scopes[$this->droplet->entry()->slug] = $this->droplet->entry()->path.'/database/migrations';
        config()->set('superv.migrations.scopes', $scopes);
    }

    public function boot()
    {
        app(Router::class)->loadFromPath($this->droplet->entry()->path.'/routes');
    }
}