<?php

namespace SuperV\Platform\Domains\Droplet\Agent;

use SuperV\Modules\Supreme\Domains\Drop\Model\DropModel;
use SuperV\Modules\Supreme\Domains\Server\Jobs\RunServerScriptJob;
use SuperV\Modules\Supreme\Domains\Server\Model\ServerModel;
use SuperV\Modules\Supreme\Domains\Server\Server;
use SuperV\Modules\Supreme\Domains\Service\Model\ServiceModel;
use SuperV\Platform\Domains\Feature\Feature;
use SuperV\Platform\Domains\Task\Job;

class AgentFeature extends Feature
{
    /** @var  ServerModel */
    protected $server;

    /** @var  DropModel */
    protected $drop;

    protected $jobs = [];

    /**
     * @var array
     */
    private $params;

    public function __construct(array $params = null)
    {
        $this->params = $params;

        if ($dropId = $this->param('drop_id')) {
            $this->drop = superv('drops')->find($dropId);
            $serverModel = $this->drop->getServer();
        } elseif ($serverId = $this->param('server_id')) {
            $serverModel = superv('servers')->find($serverId);
        } else {
            throw new \InvalidArgumentException('Can not find server in feature params');
        }

        $this->server = new Server($serverModel);
    }

    /**
     * @param      $title
     *
     * @param null $script
     *
     * @return RunServerScriptJob
     */
    public function job($title, $script = null)
    {
        $job = (new RunServerScriptJob($this->server))->setTitle($title);

        if ($script) {
            $job->script($script);
        }

        array_push($this->jobs, $job);

        return $job;
    }

    /**
     * @return array
     */
    public function getJobs(): array
    {
        return $this->jobs;
    }


    public function param($name, $default = null)
    {
        return array_get($this->params, $name, $default);
    }

    public function __get($name)
    {
        return array_get($this->params, $name);
    }
}
