<?php

namespace Tests\Platform\Domains\Droplet;

use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Droplets\Sample\SampleDroplet;
use SuperV\Platform\Domains\Droplet\Events\DropletInstalledEvent;
use SuperV\Platform\Domains\Droplet\Installer;
use SuperV\Platform\Domains\Droplet\Locator;
use SuperV\Platform\Exceptions\PathNotFoundException;
use Tests\Platform\ComposerLoader;
use Tests\Platform\TestCase;

class InstallerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function installs_a_droplet()
    {
        ComposerLoader::load(base_path('tests/Platform/__fixtures__/sample-droplet'));

        $installer = $this->installer();

        app('events')->listen(
            DropletInstalledEvent::class,
            function (DropletInstalledEvent $event) use ($installer) {
                if ($event->droplet !== $installer->getDroplet()) {
                    $this->fail('Failed to match droplet in dispatched event');
                }
            });

        $installer->setPath('tests/Platform/__fixtures__/sample-droplet')
                  ->setSlug('superv.droplets.sample')
                  ->install();

        $this->assertDatabaseHas('droplets', [
            'name'      => 'SampleDroplet',
            'vendor'    => 'superv',
            'slug'      => 'superv.droplets.sample',
            'type'      => 'droplet',
            'path'      => 'tests/Platform/__fixtures__/sample-droplet',
            'enabled'   => true,
            'namespace' => 'SuperV\\Droplets\\Sample',
        ]);
    }

    /** @test */
    function ensures_droplet_is_available_in_droplets_collection_right_after_it_is_installed()
    {
        ComposerLoader::load(base_path('tests/Platform/__fixtures__/sample-droplet'));

        $this->installer()->setPath('tests/Platform/__fixtures__/sample-droplet')
                  ->setSlug('superv.droplets.sample')
                  ->install();

        $droplet = superv('droplets')->get('superv.droplets.sample');
        $this->assertNotNull($droplet);
        $this->assertInstanceOf(SampleDroplet::class, $droplet);

    }

    /** @test */
    function droplet_installs_subdroplets()
    {
        ComposerLoader::load(base_path('tests/Platform/__fixtures__/superv/droplets/another'));
        ComposerLoader::load(base_path('tests/Platform/__fixtures__/superv/droplets/another/droplets/themes/another_sub-droplet'));

        $this->installer()
             ->setPath('tests/Platform/__fixtures__/superv/droplets/another')
             ->setSlug('superv.droplets.another')
             ->install();

        $this->assertDatabaseHas('droplets', [
            'name' => 'AnotherDroplet',
            'slug' => 'superv.droplets.another',
        ]);
        $this->assertDatabaseHas('droplets', [
            'name'      => 'AnotherSubDroplet',
            'slug'      => 'superv.themes.another_sub',
            'type'      => 'droplet',
            'path'      => 'tests/Platform/__fixtures__/superv/droplets/another/droplets/themes/another_sub-droplet',
            'enabled'   => true,
            'namespace' => 'SuperV\\Droplets\\AnotherSub',
        ]);
    }

    /** @test */
    function runs_droplets_migrations_when_installed()
    {
        ComposerLoader::load(base_path('tests/Platform/__fixtures__/sample-droplet'));

        $this->installer()
             ->setPath('tests/Platform/__fixtures__/sample-droplet')
             ->setSlug('superv.droplets.sample')
             ->install();

        $this->assertDatabaseHas('migrations', ['migration' => '2016_01_01_200000_droplet_foo_migration']);
        $this->assertDatabaseHas('migrations', ['migration' => '2016_01_01_200100_droplet_bar_migration']);
        $this->assertDatabaseHas('migrations', ['migration' => '2016_01_01_200200_droplet_baz_migration']);
    }

    /** @test */
    function locates_droplet_from_slug_if_path_is_not_given()
    {
        ComposerLoader::load(base_path('tests/Platform/__fixtures__/superv/droplets/another'));
        ComposerLoader::load(base_path('tests/Platform/__fixtures__/superv/droplets/another/droplets/themes/another_sub-droplet'));
        config()->set('superv.droplets.location', 'tests/Platform/__fixtures__');

        $droplet = $this->installer()
                        ->setLocator(new Locator())
                        ->setSlug('superv.droplets.another')
                        ->install()
                        ->getDroplet();

        $this->assertEquals('tests/Platform/__fixtures__/superv/droplets/another', (new Locator())->locate('superv.droplets.another'));
        $this->assertEquals('tests/Platform/__fixtures__/superv/droplets/another', $droplet->path());
    }

    /** @test */
    function verifies_droplet_path_exists()
    {
        $this->expectException(PathNotFoundException::class);

        $this->installer()
             ->setPath('path/does/not/exist')
             ->setSlug('superv.droplets.sample')
             ->install();
    }

    /** @test */
    function parses_composer_data()
    {
        $installer = $this->installer();
        $installer->setPath('tests/Platform/__fixtures__/sample-droplet');

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