<?php

namespace Tests\Platform\Domains\Navigation;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Silber\Bouncer\BouncerServiceProvider;
use SuperV\Platform\Domains\Auth\Account;
use SuperV\Platform\Domains\Auth\User;
use SuperV\Platform\Domains\Auth\Users;
use SuperV\Platform\Domains\Authorization\Haydar;
use SuperV\Platform\Domains\Authorization\HaydarBouncer;
use SuperV\Platform\Domains\Droplet\Installer;
use SuperV\Platform\Domains\Navigation\Collector;
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

    /** @test */
    function filter_by_authorization()
    {
        $this->app->bind(Collector::class, FakeCollector::class);
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

        FakeCollector::$sections = function () {
            return [
                Section::make('Manage.Platform')->ability('manage.platform'),
                Section::make('Manage.Users')->ability('manage.users'),
                Section::make('Operations')->ability('view.operations'),
                Section::make('Dashboard')->ability('view.dashboard'),
            ];
        };

        $this->be($user = factory(User::class)->create());
        $bouncer->assign('user')->to($user);
        $this->assertEquals([['title' => 'Dashboard']], $this->getSectionsForAbility());

        $this->be($admin = factory(User::class)->create());
        $bouncer->assign('admin')->to($admin);
        $this->assertEquals([
            ['title' => 'Manage.Users'],
            ['title' => 'Operations'],
            ['title' => 'Dashboard'],
        ], $this->getSectionsForAbility());

        $this->be($operations = factory(User::class)->create());
        $bouncer->assign('operations')->to($operations);
        $bouncer->assign('user')->to($operations);
        $this->assertEquals([
            ['title' => 'Operations'],
            ['title' => 'Dashboard'],
        ], $this->getSectionsForAbility());

        $this->be($root = factory(User::class)->create());
        $bouncer->assign('root')->to($root);
        $this->assertEquals([
            ['title' => 'Manage.Platform'],
            ['title' => 'Manage.Users'],
            ['title' => 'Operations'],
            ['title' => 'Dashboard'],
        ], $this->getSectionsForAbility());
    }

    protected function getSectionsForAbility()
    {
        $nav = app(Navigation::class)->slug('acp_main')->get();

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