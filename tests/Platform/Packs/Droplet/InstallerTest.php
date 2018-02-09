<?php

namespace Tests\SuperV\Platform\Packs\Droplet;

use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Platform\Exceptions\PathNotFoundException;
use SuperV\Platform\Packs\Droplet\Installer;
use Tests\SuperV\Platform\BaseTestCase;

class InstallerTest extends BaseTestCase
{
    use RefreshDatabase;

    /** @test */
    function installs_a_droplet()
    {
        $this->installer()
             ->path('tests/Platform/__fixtures__/sample-droplet')
             ->slug('droplets.sample')
             ->install();

        $this->assertDatabaseHas('droplets', [
            'name'      => 'SampleDroplet',
            'slug'      => 'droplets.sample',
            'type'      => 'droplet',
            'path'      => 'tests/Platform/__fixtures__/sample-droplet',
            'enabled'   => true,
            'namespace' => 'SuperV\\Droplets\\Sample',
        ]);
    }

    /** @test */
    function verifies_droplet_path_exists()
    {
        $this->expectException(PathNotFoundException::class);
        $this->installer()
             ->path('path/does/not/exist')
             ->slug('droplets.sample')
             ->install();
    }

    /** @test */
    function path_cannot_be_null()
    {
        $this->expectException(PathNotFoundException::class);
        $this->installer()
             ->path(null)
             ->slug('droplets.sample')
             ->install();
    }

    /** @test */
    function parses_composer_data()
    {
        $installer = $this->installer();
        $installer->path('tests/Platform/__fixtures__/sample-droplet');

        $this->assertEquals("SuperV\\Droplets\\Sample", $installer->namespace());
        $this->assertEquals('droplet', $installer->type());
        $this->assertEquals('SampleDroplet', $installer->name());
    }

    /**
     * @return \SuperV\Platform\Packs\Droplet\Installer
     */
    protected function installer()
    {
        return app(Installer::class);
    }
}