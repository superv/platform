<?php

namespace Tests\Platform\Domains\Navigation;

use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Platform\Domains\Droplet\Installer;
use SuperV\Platform\Domains\Navigation\Collector;
use SuperV\Platform\Domains\Navigation\DropletNavigationCollector;
use SuperV\Platform\Domains\Navigation\Navigation;
use Tests\Platform\ComposerLoader;
use Tests\Platform\TestCase;

class NavigationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function builds_navigation_from_droplet_config_folder()
    {
        ComposerLoader::load(base_path('tests/Platform/__fixtures__/sample-droplet'));

        $installer = app(Installer::class);
        $installer->setPath('tests/Platform/__fixtures__/sample-droplet')
                  ->setSlug('superv.droplets.sample')
                  ->install();

        $droplet = $installer->getDroplet();
        $this->app->register($droplet->providerClass());

        \Platform::boot();

        $nav = app(Navigation::class)->slug('acp_main')->get();
        $this->assertNotNull($nav);

        $this->assertEquals([
            'slug'     => 'acp_main',
            'sections' => [
                [
                    'title' => 'Dashboard',
                    'icon'  => 'tachometer',
                    'url'   => 'platform/dashboard',
                ],
                [
                    'title'    => 'Platform',
                    'icon'     => 'cog',
                    'url'      => 'platform',
                    'sections' => [
                        [
                            'title'    => 'User Management',
                            'sections' => [
                                ['title' => 'Users', 'url' => 'platform/users'],
                                ['title' => 'Roles', 'url' => 'platform/roles'],
                            ],
                        ],
                        [
                            'title'    => 'Settings',
                            'icon'     => 'cog',
                            'sections' => [
                                ['title' => 'Localization', 'url' => 'platform/localization'],
                                ['title' => 'Droplets', 'url' => 'platform/droplets'],
                            ],
                        ],
                    ],
                ],
            ],
        ], $nav);
    }
}