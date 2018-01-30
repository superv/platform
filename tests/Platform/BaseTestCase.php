<?php

namespace Tests\SuperV\Platform;

use Platform;
use Tests\SuperV\TestCase;

class BaseTestCase extends TestCase
{
    protected function setUpDroplet()
    {
        Platform::install('superv.droplets.sample', 'tests/Platform/__fixtures__/sample-droplet');
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