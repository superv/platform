<?php

namespace Tests\Platform\Domains\addon;

use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use SuperV\Addons\Sample\SampleAddon;
use SuperV\Platform\Domains\Addon\Events\AddonInstalledEvent;
use SuperV\Platform\Domains\Addon\Installer;
use SuperV\Platform\Domains\Addon\Locator;
use SuperV\Platform\Exceptions\PathNotFoundException;
use Tests\Platform\ComposerLoader;
use Tests\Platform\TestCase;

class InstallerTest extends TestCase
{
    use RefreshDatabase;

    function test__installs_a_addon()
    {
        ComposerLoader::load(base_path('tests/Platform/__fixtures__/sample-addon'));

        $installer = $this->installer();

        app('events')->listen(
            AddonInstalledEvent::class,
            function (AddonInstalledEvent $event) use ($installer) {
                if ($event->addon !== $installer->getAddon()) {
                    $this->fail('Failed to match addon in dispatched event');
                }
            });

        $installer->setPath('tests/Platform/__fixtures__/sample-addon')
                  ->setNamespace('superv.addons.sample')
                  ->install();

        $this->assertDatabaseHas('sv_addons', [
            'name'          => 'SampleAddon',
            'vendor'        => 'superv',
            'namespace'     => 'superv.addons.sample',
            'type'          => 'addon',
            'path'          => 'tests/Platform/__fixtures__/sample-addon',
            'enabled'       => true,
            'psr_namespace' => 'SuperV\\Addons\\Sample',
        ]);
    }

    function test__seeds_addon()
    {
        $this->setUpAddon(null, null, $seed = true);
        $this->assertTrue($_SERVER['sample.seeder']);
    }

    function test__does_not_install_an_already_installed_addon()
    {
        $this->setUpAddon();

        $this->expectException(RuntimeException::class);

        $this->setUpAddon();
    }

    function test__ensures_addon_is_available_in_addons_collection_right_after_it_is_installed()
    {
        ComposerLoader::load(base_path('tests/Platform/__fixtures__/sample-addon'));

        $this->installer()->setPath('tests/Platform/__fixtures__/sample-addon')
             ->setNamespace('superv.addons.sample')
             ->install();

        $addon = superv('addons')->get('superv.addons.sample');
        $this->assertNotNull($addon);
        $this->assertInstanceOf(SampleAddon::class, $addon);
    }

    function test__addon_installs_subaddons()
    {
        ComposerLoader::load(base_path('tests/Platform/__fixtures__/superv/addons/another'));
        ComposerLoader::load(base_path('tests/Platform/__fixtures__/superv/addons/another/addons/themes/another_sub-addon'));

        $this->installer()
             ->setPath('tests/Platform/__fixtures__/superv/addons/another')
             ->setNamespace('superv.addons.another')
             ->install();

        $this->assertDatabaseHas('sv_addons', [
            'name'      => 'AnotherAddon',
            'namespace' => 'superv.addons.another',
        ]);
        $this->assertDatabaseHas('sv_addons', [
            'name'          => 'AnotherSubAddon',
            'namespace'     => 'superv.themes.another_sub',
            'type'          => 'addon',
            'path'          => 'tests/Platform/__fixtures__/superv/addons/another/addons/themes/another_sub-addon',
            'enabled'       => true,
            'psr_namespace' => 'SuperV\\Addons\\AnotherSub',
        ]);
    }

    function test__runs_addons_migrations_when_installed()
    {
        ComposerLoader::load(base_path('tests/Platform/__fixtures__/sample-addon'));

        $this->installer()
             ->setPath('tests/Platform/__fixtures__/sample-addon')
             ->setNamespace('superv.addons.sample')
             ->install();

        $this->assertDatabaseHas('migrations', ['migration' => '2016_01_01_200000_addon_foo_migration']);
        $this->assertDatabaseHas('migrations', ['migration' => '2016_01_01_200100_addon_bar_migration']);
        $this->assertDatabaseHas('migrations', ['migration' => '2016_01_01_200200_addon_baz_migration']);
    }

    function test__locates_addon_from_slug_if_path_is_not_given()
    {
        ComposerLoader::load(base_path('tests/Platform/__fixtures__/superv/addons/another'));
        ComposerLoader::load(base_path('tests/Platform/__fixtures__/superv/addons/another/addons/themes/another_sub-addon'));
        config()->set('superv.addons.location', 'tests/Platform/__fixtures__');

        $addon = $this->installer()
                      ->setLocator(new Locator())
                      ->setNamespace('superv.addons.another')
                      ->install()
                      ->getAddon();

        $this->assertEquals('tests/Platform/__fixtures__/superv/addons/another', (new Locator())->locate('superv.addons.another'));
        $this->assertEquals('tests/Platform/__fixtures__/superv/addons/another', $addon->path());
    }

    function test__verifies_addon_path_exists()
    {
        $this->expectException(PathNotFoundException::class);

        $this->installer()
             ->setPath('path/does/not/exist')
             ->setNamespace('superv.addons.sample')
             ->install();
    }

    function test__parses_composer_data()
    {
        $installer = $this->installer();
        $installer->setPath('tests/Platform/__fixtures__/sample-addon');

        $this->assertEquals("SuperV\\Addons\\Sample", $installer->getPsrNamespace());
        $this->assertEquals('addon', $installer->type());
        $this->assertEquals('SampleAddon', $installer->name());
    }

    /**
     * @return \SuperV\Platform\Domains\Addon\Installer
     */
    protected function installer()
    {
        return app(Installer::class);
    }
}
