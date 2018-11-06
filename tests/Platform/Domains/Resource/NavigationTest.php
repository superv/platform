<?php

namespace Tests\Platform\Domains\Resource;

use Platform;
use SuperV\Platform\Domains\Port\Port;
use SuperV\Platform\Domains\Resource\Nav\Nav;
use SuperV\Platform\Domains\Resource\Nav\Section;
use SuperV\Platform\Domains\Resource\ResourceModel;

class NavigationTest extends ResourceTestCase
{
    /** @test */
    function build_section()
    {
        $nav = Nav::create('acp');
        $navEntry = $nav->entry()->fresh();

        $this->assertInstanceOf(Section::class, $navEntry);
        $this->assertEquals('Acp', $navEntry->title);
        $this->assertEquals('acp', $navEntry->handle);
        $this->assertNull($navEntry->parent);

        $marketing = $nav->addSection('Marketing')->fresh();
        $this->assertEquals($navEntry->id, $marketing->parent->id);
        $this->assertEquals('Marketing', $marketing->title);
        $this->assertEquals('marketing', $marketing->handle);

        $marketingCrm = $marketing->addChild('Crm');
        $this->assertEquals($marketing->id, $marketingCrm->parent->id);
        $this->assertEquals('Crm', $marketingCrm->title);

        $marketingPromotions = $marketing->addChild('Promotions');
        $this->assertEquals($marketing->id, $marketingPromotions->parent->id);
        $this->assertEquals('Promotions', $marketingPromotions->title);

        $marketingPromotionsCodes = $marketingPromotions->addChild('Codes');
        $this->assertEquals($marketingPromotions->id, $marketingPromotionsCodes->parent->id);

        $marketing->getChildren()->assertEquals([$marketingCrm, $marketingPromotions]);
    }

    /** @test */
    function easy_create()
    {
        $nav = Nav::create('Acp');
        // level 1
        $nav->add('marketing');
        $marketing = $nav->getChild('marketing');
        $this->assertEquals('Marketing', $marketing->title);
        $this->assertEquals('marketing', $marketing->handle);

        // level 2
        $settings = $nav->add('settings.auth');
        $this->assertEquals('Settings', $settings->title);
        $this->assertEquals('settings', $settings->handle);
        $this->assertEquals($nav->entry()->id, $settings->parent->id);
        $this->assertEquals(1, $settings->children()->count());

        $auth = $settings->getChild('auth');
        $this->assertEquals('Auth', $auth->title);
        $this->assertEquals('auth', $auth->handle);
        $this->assertEquals(0, $auth->children()->count());

        // level 3
        $settingsAgain = $nav->add('settings.config.mail_templates');
        $this->assertEquals($settings->id, $settingsAgain->id);
        $this->assertEquals($nav->entry()->id, $settings->parent->id);
        $this->assertEquals(2, $settings->children()->count());

        $config = $settings->getChild('config');
        $this->assertEquals('Config', $config->title);
        $this->assertEquals('config', $config->handle);
        $this->assertEquals(1, $config->children()->count());
    }

    /** @test */
    function deep_level()
    {
        $nav = Nav::create('sv');

        $a = $nav->add('a.a.a.a.a');
        $a->add('b.a.a.a');
        $a->add('c.a.a.a');
        $a->add('d.a.a.a');

        $this->assertEquals(18, Section::count());
    }

    /** @test */
    function this_is_soo_easy()
    {
        Nav::create('a.b.c.d.e.f');
        $this->assertEquals(6, Section::count());

        Nav::create('a.a.a');
        $this->assertEquals(8, Section::count());

        Nav::create('a.a.b');
        $this->assertEquals(9, Section::count());
    }

    /** @test */
    function get_from_namespace()
    {
        Nav::create('a.b.c.d.e.f');

        $e = Nav::get('a.b.c.d.e');
        $this->assertEquals('E', $e->title);
        $this->assertEquals(1, $e->children()->count());
    }

    /** @test */
    function composes_nav()
    {
        $nav = Nav::create('acp');
        $nav->add('foo.bar');
        $nav->add('foo.baz');
        $nav->add('foo.baz.bom');
        $nav->add('bar.baz');
        $nav->add('bar.foo');

        $this->assertEquals([
            'title'      => 'Acp',
            'handle'      => 'acp',
            'sections' => [
                [
                    'title'    => 'Foo',
                    'handle'   => 'foo',
                    'sections' => [
                        [
                            'title'  => 'Bar',
                            'handle' => 'bar',
                        ],
                        [
                            'title'    => 'Baz',
                            'handle'   => 'baz',
                            'sections' => [
                                [
                                    'title'  => 'Bom',
                                    'handle' => 'bom',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'title'    => 'Bar',
                    'handle'    => 'bar',
                    'sections' => [
                        [
                            'title'  => 'Baz',
                            'handle' => 'baz',
                        ],
                        [
                            'title'  => 'Foo',
                            'handle' => 'foo',
                        ],
                    ],
                ],
            ],
        ], Nav::get('acp')->compose());
    }

    function builds_navigation()
    {
        $this->makeResource('no_nav_resource_a');
        $this->makeResource('no_nav_resource_b');
        $this->makeResource('no_nav_resource_c');

        $this->makeResource('t_users', [], ['nav' => 'acp.settings.auth', 'label' => 'System Users']);
        $navEntry = Section::query()->latest()->first();
        $this->assertArrayContains([
            'nav'         => 'acp',
            'section'     => 'settings',
            'subsection'  => 'auth',
            'slug'        => 'system_users',
            'title'       => 'System Users',
            'resource_id' => ResourceModel::withSlug('t_users')->getId(),
        ], $navEntry->toArray());
    }

    function build_sections()
    {
        $this->makeResource('t_users', [], ['nav' => 'acp.settings.auth']);
        $this->makeResource('t_roles', [], ['nav' => 'acp.settings.auth']);
        $this->makeResource('t_actions', [], ['nav' => 'acp.settings.auth']);

        $this->makeResource('t_templates', [], ['nav' => 'acp.settings.config']);
        $this->makeResource('t_mails', [], ['nav' => 'acp.settings.config']);

        $this->makeResource('t_clients', [], ['nav' => 'acp.marketing.crm']);
        $this->makeResource('t_features', [], ['nav' => 'acp.marketing.memberships']);
        $this->makeResource('t_memtypes', [], ['nav' => 'acp.marketing.memberships']);
        $this->makeResource('t_subs', [], ['nav' => 'acp.marketing.memberships']);

        $nav = Nav::make('acp');
        $nav->build();

        $this->assertEquals(2, $nav->sections()->count());

        $settings = $nav->section('settings');
        $this->assertInstanceOf(Section::class, $settings);
        $this->assertEquals('Settings', $settings->title());
        $this->assertEquals('settings', $settings->slug());
        $this->assertEquals(10, $settings->ranking());
        $this->assertNull($settings->slug());

        $this->assertEquals(2, $settings->count());
        $this->assertEquals(3, $settings->get('auth')->count());
        $this->assertEquals(2, $settings->get('config')->count());

        $marketing = $nav->section('marketing');
        $this->assertEquals(2, $marketing->count());
        $this->assertEquals(1, $marketing->get('crm')->count());
        $this->assertEquals(3, $marketing->get('memberships')->count());

        $composed = $nav->compose();

        $this->newUser();

        Platform::setPort(new Port(['slug' => 'acp', 'navigation_slug' => 'acp']));
        $this->withoutExceptionHandling();

        $response = $this->getJson('data/nav', $this->getHeaderWithAccessToken());

        $this->assertEquals($composed, $response->decodeResponseJson('data.nav'));
    }
}