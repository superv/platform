<?php namespace SuperV\Platform\Domains\Droplet\Agent;

use SuperV\Modules\Supreme\Domains\Script\Command\ParseFile;
use SuperV\Modules\Supreme\Domains\Server\Server;
use SuperV\Platform\Domains\Droplet\Jobs\LocateResourceJob;
use SuperV\Platform\Domains\Feature\Feature;

class AgentFeature extends Feature
{
    /**
     * @var array
     */
    protected $params;

    /**
     * @var \SuperV\Modules\Supreme\Domains\Server\Server
     */
    protected $server;

    public function __construct(Server $server, array $params = [])
    {
        $this->params = $params;
        $this->server = $server;
    }

    public function setListener($listener)
    {
        $this->server->setListener([$listener, 'listen']);

        return $this;
    }

    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        return array_get($this->params, $name);
    }

    protected function stub($stub, $tokens)
    {
        // TODO.ali: get agent slug from class name
        $location = $this->dispatch(new LocateResourceJob("superv.agents.power_dns::{$stub}", 'stub'));

        return $this->dispatch(new ParseFile($location, $tokens));
    }
}