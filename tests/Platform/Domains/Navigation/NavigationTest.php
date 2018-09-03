<?php

namespace Tests\Platform\Domains\Navigation;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Auth\User;
use SuperV\Platform\Domains\Authorization\Haydar;
use SuperV\Platform\Domains\Authorization\HaydarBouncer;
use SuperV\Platform\Domains\Droplet\Installer;
use SuperV\Platform\Domains\Navigation\Collector;
use SuperV\Platform\Domains\Navigation\HasSection;
use SuperV\Platform\Domains\Navigation\Navigation;
use SuperV\Platform\Domains\Navigation\Section;
use Tests\Platform\ComposerLoader;
use Tests\Platform\TestCase;

class NavigationTest extends TestCase
{
    use RefreshDatabase;

//    protected $installs = ['superv.modules.nucleo'];

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

        $nav = app(Navigation::class)->slug('acp')->get();
        $this->assertNotNull($nav);

        $this->assertArraySubset([
            'slug'     => 'acp',
            'sections' => [
                [
                    'title' => 'Dashboard',
                    'slug'  => 'dashboard',
                    'icon'  => 'tachometer',
                    'url'   => 'platform/dashboard',
                ],
                [
                    'title'    => 'Platform',
                    'slug'     => 'platform',
                    'icon'     => 'cog',
                    'url'      => 'platform',
                    'sections' => [
                        [
                            'title'    => 'User Management',
                            'slug'     => 'user_management',
                            'sections' => [
                                [
                                    'title' => 'Users',
                                    'slug'  => 'users',
                                    'url'   => 'platform/users',
                                ],
                                [
                                    'title' => 'Roles',
                                    'slug'  => 'roles',
                                    'url'   => 'platform/roles',
                                ],
                            ],
                        ],
                        [
                            'title'    => 'Settings',
                            'icon'     => 'cog',
                            'slug'     => 'settings',
                            'sections' => [
                                [
                                    'title' => 'Localization',
                                    'slug'  => 'localization',
                                    'url'   => 'platform/localization',
                                ],
                                [
                                    'title' => 'Droplets',
                                    'slug'  => 'droplets',
                                    'url'   => 'platform/droplets',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ], $nav);
    }

    /** @test */
    function section_parses_url_from_resource()
    {
        $fake = new class implements HasSection
        {
            public static function getSection(): ?array
            {
                return [
                    'parent'   => 'acp',
                    'resolver' => function () {
                        return
                            Section::make('resource-section')->parent('acp');
                    },
                ];
            }
        };

        $this->app->bind(Collector::class, FakeCollector::class);
        FakeCollector::$sections = function () use ($fake) {
            return [
                $fake::getSection()['resolver'](),
            ];
        };

        $nav = app(Navigation::class)->slug('acp')->get();

        $this->assertEquals(1, count($nav['sections']));
        $fakeNav = $fake::getSection()['resolver']()->build();
        $this->assertArraySubset($fakeNav, $nav['sections'][0]);
    }

    /** @test */
    function filter_by_authorization()
    {
        $this->app->bind(Haydar::class, HaydarBouncer::class);
        $bouncer = app(\Silber\Bouncer\Bouncer::class);
        $bouncer->tables([
            'permissions'    => 'bouncer_permissions',
            'assigned_roles' => 'bouncer_assigned_roles',
            'roles'          => 'bouncer_roles',
            'abilities'      => 'bouncer_abilities',
        ]);
        $bouncer->allow('root')->everything();
        $bouncer->allow('admin')->everything();
        $bouncer->forbid('admin')->to('manage.platform');
        $bouncer->allow('operations')->to('view.operations');
        $bouncer->allow('user')->to('view.dashboard');

        $this->app->bind(Collector::class, FakeCollector::class);
        FakeCollector::$sections = function () {
            return [
                Section::make('manage_platform')->ability('manage.platform'),
                Section::make('manage_users')->ability('manage.users'),
                Section::make('operations')->ability('view.operations'),
                Section::make('dashboard')->ability('view.dashboard'),
            ];
        };

        $this->be($user = factory(User::class)->create());
        $bouncer->assign('user')->to($user);
        $this->assertArraySubset([
            [
                'title' => 'Dashboard',
                'slug'  => 'dashboard',
            ]], $this->getSectionsForAbility());

        $this->be($admin = factory(User::class)->create());
        $bouncer->assign('admin')->to($admin);
        $this->assertArraySubset([
            ['title' => 'Manage Users', 'slug' => 'manage_users'],
            ['title' => 'Operations', 'slug' => 'operations'],
            ['title' => 'Dashboard', 'slug' => 'dashboard'],
        ], $this->getSectionsForAbility());

        $this->be($operations = factory(User::class)->create());
        $bouncer->assign('operations')->to($operations);
        $bouncer->assign('user')->to($operations);
        $this->assertArraySubset([
            ['title' => 'Operations', 'slug' => 'operations'],
            ['title' => 'Dashboard', 'slug' => 'dashboard'],
        ], $this->getSectionsForAbility());

        $this->be($root = factory(User::class)->create());
        $bouncer->assign('root')->to($root);
        $this->assertArraySubset([
            ['title' => 'Manage Platform', 'slug' => 'manage_platform'],
            ['title' => 'Manage Users', 'slug' => 'manage_users'],
            ['title' => 'Operations', 'slug' => 'operations'],
            ['title' => 'Dashboard', 'slug' => 'dashboard'],
        ], $this->getSectionsForAbility());
    }

    protected function getSectionsForAbility()
    {
        $nav = app(Navigation::class)->slug('acp')->get();

        return $nav['sections'];
    }
}

class FakeCollector implements Collector
{
    public static $sections;

    public function sections()
    {
        return (static::$sections)();
    }

    public function collect(string $slug): Collection
    {
        return (new Collection())
            ->put('sv.droplet.slug',
                collect($this->sections()));
    }
}