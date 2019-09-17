<?php

namespace Tests\Platform\Domains\addon;

use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use SuperV\Addons\Sample\SampleAddon;
use SuperV\Platform\Domains\Addon\AddonModel;
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
                if ($event->addon !== sv_addons('sample')) {
                    $this->fail('Failed to match addon in dispatched event');
                }
            });

        $installer->setPath('tests/Platform/__fixtures__/sample-addon')
                  ->install();

        $addon = AddonModel::query()->where('identifier', 'sample')->first();
        $this->assertNotNull($addon);

        $this->assertDatabaseHas('sv_addons', [
            'name'          => 'sample',
            'vendor'        => 'superv',
            'identifier'    => 'sample',
            'type'          => 'addon',
            'path'          => 'tests/Platform/__fixtures__/sample-addon',
            'enabled'       => true,
            'psr_namespace' => 'SuperV\\Addons\\Sample',
        ]);
    }

    function __install_with_custom_identifier()
    {
        $this->setUpAddonInstaller('sample')->install('sv.my_addon');

        $this->assertNotNull($entry = AddonModel::byIdentifier('sv.my_addon'));
        $this->assertEquals('sv.my_addon', $entry->getIdentifier());
        $this->assertEquals('sv', $entry->getVendor());
        $this->assertEquals('my_addon', $entry->getVendor());
    }

    function test__seeds_addon()
    {
        $this->setUpAndSeedAddon();

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

        $this->installer()
             ->setPath('tests/Platform/__fixtures__/sample-addon')
             ->install();

        $addon = sv_addons('sample');
        $this->assertNotNull($addon);
        $this->assertInstanceOf(SampleAddon::class, $addon);
    }

    function __addon_installs_subaddons()
    {
        ComposerLoader::load(base_path('tests/Platform/__fixtures__/superv/addons/another'));
        ComposerLoader::load(base_path('tests/Platform/__fixtures__/superv/addons/another/addons/themes/another_sub-addon'));

        $this->installer()
             ->setPath('tests/Platform/__fixtures__/superv/addons/another')
             ->setVendor('superv')
             ->setName('another')
             ->install();

        $this->assertDatabaseHas('sv_addons', [
            'name'       => 'another',
            'identifier' => 'superv.addons.another',
        ]);
        $this->assertDatabaseHas('sv_addons', [
            'name'          => 'another_sub',
            'identifier'    => 'superv.addons.another_sub',
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
             ->install();

        $this->assertDatabaseHas('migrations', ['migration' => '2016_01_01_200000_addon_foo_migration']);
        $this->assertDatabaseHas('migrations', ['migration' => '2016_01_01_200100_addon_bar_migration']);
        $this->assertDatabaseHas('migrations', ['migration' => '2016_01_01_200200_addon_baz_migration']);
    }

    function __locates_addon_from_slug_if_path_is_not_given()
    {
        ComposerLoader::load(base_path('tests/Platform/__fixtures__/superv/addons/another'));
        ComposerLoader::load(base_path('tests/Platform/__fixtures__/superv/addons/another/addons/themes/another_sub-addon'));
        config()->set('superv.addons.location', 'tests/Platform/__fixtures__');

        $addon = $this->installer()
                      ->setLocator(new Locator())
                      ->setName('superv.another')
                      ->setAddonType('addon')
                      ->install()
                      ->getAddon();

        $this->assertEquals('tests/Platform/__fixtures__/superv/addons/another', (new Locator())->locate('superv.another', $addon->getType()));
        $this->assertEquals('tests/Platform/__fixtures__/superv/addons/another', $addon->path());
    }

    function test__verifies_addon_path_exists()
    {
        $this->expectException(PathNotFoundException::class);

        $this->installer()
             ->setPath('path/does/not/exist')
             ->install();
    }

    /**
     * @return \SuperV\Platform\Domains\Addon\Installer
     */
    protected function installer()
    {
        return Installer::resolve();
    }
}
