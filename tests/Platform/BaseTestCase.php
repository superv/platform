<?php

namespace Tests\SuperV\Platform;

use Platform;
use SuperV\Platform\Domains\Droplet\DropletModel;
use Tests\SuperV\TestCase;

class BaseTestCase extends TestCase
{
    /**
     * @return \SuperV\Platform\Domains\Droplet\Droplet
     */
    protected function setUpDroplet()
    {
        Platform::install('superv.droplets.sample', 'tests/Platform/__fixtures__/sample-droplet');
        $entry = DropletModel::bySlug('superv.droplets.sample');

        return $entry->resolveDroplet();
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