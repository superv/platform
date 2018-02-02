<?php

namespace Tests\SuperV\Platform\Packs\Droplet\Console;

use SuperV\Platform\Packs\Droplet\Installer;
use Tests\SuperV\Platform\BaseTestCase;

class DropletInstallCommandTest extends BaseTestCase
{
    /**
     * @test
     */
    function invokes_droplet_installer_with_correct_parameters()
    {
        $installer = $this->setUpMock(Installer::class);
        $installer->shouldReceive('path')->with('path/to/droplet')->once()->andReturnSelf();
        $installer->shouldReceive('slug')->with('droplet.slug')->once()->andReturnSelf();
        $installer->shouldReceive('install')->once();

        $this->artisan('droplet:install', [
            '--path' => 'path/to/droplet',
            'slug'   => 'droplet.slug',
        ]);
    }
}