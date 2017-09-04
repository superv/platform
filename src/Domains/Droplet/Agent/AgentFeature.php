<?php

namespace SuperV\Platform\Domains\Droplet\Agent;

use SuperV\Modules\Supreme\Domains\Server\Jobs\RunServerScriptJob;
use SuperV\Modules\Supreme\Domains\Service\Model\ServiceModel;
use SuperV\Platform\Domains\Feature\Feature;
use SuperV\Platform\Domains\Task\Job;

class AgentFeature extends Feature
{
    protected $server;

    protected $service;

    protected $jobs = [];

    /**
     * @var array
     */
    private $params;

    public function __construct(array $params = null)
    {
        $this->params = $params;
    }

    public function param($name, $default = null)
    {
        return array_get($this->params, $name, $default);
    }

    public function __get($name)
    {
        return array_get($this->params, $name);
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
        $job = (new RunServerScriptJob($this->server()))->setTitle($title);

        if ($script) {
            $job->script($script);
        }

        array_push($this->jobs, $job);

        return $job;
    }

    public function server()
    {
        if (! $this->server) {
            if (! $serverId = $this->param('server_id')) {
                /** @var ServiceModel $service */
                if ($service = $this->service()) {
                    $this->server = $service->getServer();
                }
            } else {
                $this->server = superv('servers')->find($serverId);
            }
        }

        return $this->server;
    }

    public function service()
    {
        if (! $this->service) {
            if ($serviceId = $this->param('service_id')) {
                $this->service = superv('services')->find($serviceId);
            }
        }

        return $this->service;
    }

    /**
     * @return array
     */
    public function getJobs(): array
    {
        return $this->jobs;
    }
}
