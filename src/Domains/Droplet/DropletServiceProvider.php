<?php namespace SuperV\Platform\Domains\Droplet;

use Illuminate\Foundation\Application;
use SuperV\Platform\Domains\Droplet\Jobs\PackDropletRoutesJob;
use SuperV\Platform\Domains\Feature\JobDispatcherTrait;

class DropletServiceProvider
{
    use JobDispatcherTrait;

    /**
     * @var Application
     */
    protected $app;

    /**
     * @var Droplet
     */
    protected $droplet;

    protected $listeners = [];

    protected $routes = [];

    protected $aliases = [];

    protected $bindings = [];

    protected $singletons = [];

    protected $features = [];

    protected $composers = [];

    protected $commands = [];

    public function __construct(Application $app, Droplet $droplet)
    {
        $this->app = $app;
        $this->droplet = $droplet;
    }

    public function getCommands()
    {
        return $this->commands;
    }

    /**
     * @return mixed
     */
    public function getListeners()
    {
        return $this->listeners;
    }

    public function getRoutes()
    {
        $routes = $this->run(new PackDropletRoutesJob($this));

        return array_merge($this->routes, $routes);
    }

    /**
     * @return mixed
     */
    public function getAliases()
    {
        return $this->aliases;
    }

    /**
     * @return mixed
     */
    public function getSingletons()
    {
        return $this->singletons;
    }

    /**
     * @return mixed
     */
    public function getBindings()
    {
        return $this->bindings;
    }

    /**
     * @return array
     */
    public function getFeatures()
    {
        return $this->features;
    }

    /**
     * @return Droplet
     */
    public function getDroplet()
    {
        return $this->droplet;
    }

    public function getPath($path = null)
    {
        return $this->droplet->getPath($path);
    }

    public function getNamespace()
    {
        return $this->getDroplet()->getNamespace();
    }
}