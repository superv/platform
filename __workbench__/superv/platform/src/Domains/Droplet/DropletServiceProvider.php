<?php

namespace SuperV\Platform\Domains\Droplet;

use SuperV\Platform\Domains\Database\Migrations\Scopes as MigrationScopes;
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
            $this->droplet->slug() => base_path($this->droplet->resourcePath('views')),
        ]);

        if ($this->app->runningInConsole()) {
            MigrationScopes::register($this->droplet->slug(), $this->droplet->path('database/migrations'));
        }
    }

    public function boot()
    {
        app(Router::class)->loadFromPath($this->droplet->path('routes'));
    }
}