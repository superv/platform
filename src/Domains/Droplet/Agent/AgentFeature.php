<?php

namespace SuperV\Platform\Domains\Droplet\Agent;

use SuperV\Platform\Domains\Task\Job;
use SuperV\Platform\Domains\Feature\Feature;
use SuperV\Modules\Supreme\Domains\Service\Model\ServiceModel;

class AgentFeature extends Feature
{
    protected $server;

    protected $service;

    protected $jobs = [];

    public function addJob(Job $job)
    {
        array_push($this->jobs, $job);

        return $this;
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
