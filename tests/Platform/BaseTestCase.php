<?php

namespace Tests\SuperV\Platform;

use Mockery as m;
use Platform;
use SuperV\Platform\Domains\Droplet\DropletModel;
use Tests\SuperV\TestCase;

class BaseTestCase extends TestCase
{
    protected function make($abstract, $params = [])
    {
        return $this->app->make($abstract, $params);
    }

    /**
     * @return \SuperV\Platform\Domains\Droplet\Droplet
     */
    protected function setUpDroplet()
    {
        Platform::install('superv.droplets.sample', 'tests/Platform/__fixtures__/sample-droplet');
        $entry = DropletModel::bySlug('superv.droplets.sample');

        return $entry->resolveDroplet();
    }

    protected function setUpMock($class)
    {
        $mock = m::mock($class);
        $this->app->singleton($class, function () use ($mock) { return $mock; });

        return $mock;
    }

    protected function setUpPorts()
    {
        return config([
            'superv.ports' => [
                'web' => [
                    'hostname' => 'superv.io',
                ],
                'acp' => [
                    'hostname' => 'superv.io',
                    'prefix'   => 'acp',
                ],
                'api' => [
                    'hostname' => 'api.superv.io',
                ],
            ],
        ]);
    }
}