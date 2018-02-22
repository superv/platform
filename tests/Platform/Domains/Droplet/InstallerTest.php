<?php

namespace Tests\Platform\Domains\Droplet;

use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Platform\Domains\Droplet\Installer;
use SuperV\Platform\Exceptions\PathNotFoundException;
use Tests\ComposerLoader;
use Tests\Platform\BaseTestCase;

class InstallerTest extends BaseTestCase
{
    use RefreshDatabase;

    /** @test */
    function installs_a_droplet()
    {
        ComposerLoader::load(base_path('tests/Platform/__fixtures__/sample-droplet'));
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
    function runs_droplets_migrations_when_installed()
    {
        ComposerLoader::load(base_path('tests/Platform/__fixtures__/sample-droplet'));

        $this->installer()
             ->path('tests/Platform/__fixtures__/sample-droplet')
             ->slug('droplets.sample')
             ->install();

        $this->assertDatabaseHas('migrations', ['migration' => '2016_01_01_200000_droplet_foo_migration']);
        $this->assertDatabaseHas('migrations', ['migration' => '2016_01_01_200100_droplet_bar_migration']);
        $this->assertDatabaseHas('migrations', ['migration' => '2016_01_01_200200_droplet_baz_migration']);
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
     * @return \SuperV\Platform\Domains\Droplet\Installer
     */
    protected function installer()
    {
        return app(Installer::class);
    }
}