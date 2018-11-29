<?php

namespace Tests\Platform\Domains\Resource;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Schema\Schema;
use SuperV\Platform\Domains\Resource\Nav\Nav;
use SuperV\Platform\Domains\Resource\Nav\Section;
use SuperV\Platform\Domains\Resource\ResourceBlueprint;

class NavigationTest extends ResourceTestCase
{
    protected function setUp()
    {
        parent::setUp();

        Section::truncate();
    }

    /** @test */
    function build_section()
    {
        $nav = Nav::create('acp');
        $navEntry = $nav->entry()->fresh();

        $this->assertInstanceOf(Section::class, $navEntry);
        $this->assertEquals('Acp', $navEntry->getTitle());
        $this->assertEquals('acp', $navEntry->getHandle());
        $this->assertNull($navEntry->getParent());

        $marketing = $nav->addSection('Marketing')->fresh();
        $this->assertEquals($navEntry->id, $marketing->getParent()->id);
        $this->assertEquals('Marketing', $marketing->getTitle());
        $this->assertEquals('marketing', $marketing->getHandle());

        $marketingCrm = $marketing->addChild('Crm');
        $this->assertEquals($marketing->id, $marketingCrm->getParent()->id);
        $this->assertEquals('Crm', $marketingCrm->getTitle());

        $marketingPromotions = $marketing->addChild('Promotions');
        $this->assertEquals($marketing->id, $marketingPromotions->getParent()->id);
        $this->assertEquals('Promotions', $marketingPromotions->getTitle());

        $marketingPromotionsCodes = $marketingPromotions->addChild('Codes');
        $this->assertEquals($marketingPromotions->id, $marketingPromotionsCodes->getParent()->id);

        $marketing->getChildren()->assertEquals([$marketingCrm, $marketingPromotions]);
    }

    /** @test */
    function easy_create()
    {
        $nav = Nav::create('Acp');
        // level 1
        $nav->add('marketing');
        $marketing = $nav->getChild('marketing');
        $this->assertEquals('Marketing', $marketing->getTitle());
        $this->assertEquals('marketing', $marketing->getHandle());

        // level 2
        $settings = $nav->add('settings.auth');
        $this->assertEquals('Settings', $settings->getTitle());
        $this->assertEquals('settings', $settings->getHandle());
        $this->assertEquals($nav->entry()->id, $settings->getParent()->id);
        $this->assertEquals(1, $settings->children()->count());

        $auth = $settings->getChild('auth');
        $this->assertEquals('Auth', $auth->getTitle());
        $this->assertEquals('auth', $auth->getHandle());
        $this->assertEquals(0, $auth->children()->count());

        // level 3
        $settingsAgain = $nav->add('settings.config.mail_templates');
        $this->assertEquals($settings->id, $settingsAgain->id);
        $this->assertEquals($nav->entry()->id, $settings->getParent()->id);
        $this->assertEquals(2, $settings->children()->count());

        $config = $settings->getChild('config');
        $this->assertEquals('Config', $config->getTitle());
        $this->assertEquals('config', $config->getHandle());
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
    function composes_nav()
    {
        $nav = Nav::create('acp');
        $nav->add('foo.bar');
        $nav->add('foo.baz');
        $nav->add('foo.baz.bom');
        $nav->add('bar.baz');
        $nav->add('bar.foo');

        $this->assertEquals([
            'title'    => 'Acp',
            'handle'   => 'acp',
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
                    'handle'   => 'bar',
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

    /** @test */
    function get_from_full_handle()
    {
        Nav::create('acp.settings.auth');

        $auth = Section::get('acp.settings.auth');
        $this->assertEquals('auth', $auth->getHandle());

        Nav::create('a.b.c.d.e.f');

        $e = Section::get('a.b.c.d.e');
        $this->assertEquals('E', $e->getTitle());
        $this->assertEquals(1, $e->children()->count());
    }

    /** @test */
    function creates_from_resource_blueprint()
    {
        Schema::create('t_users', function (Blueprint $table, ResourceBlueprint $resource) {
            $table->increments('id');
            $resource->nav([
                'parent' => 'acp.settings.auth',
                'title'  => 'System Users',
                'handle' => 'users',
                'icon'   => 'user',
            ]);
        });

        $this->assertArrayContains([
            'title'  => 'System Users',
            'handle' => 'users',
            'icon'   => 'user',
            'url'    => 'sv/res/t_users',
        ], Section::get('acp.settings.auth.users')->compose());

        Schema::create('t_templates', function (Blueprint $table, ResourceBlueprint $resource) {
            $table->increments('id');
            $resource->label('Templates'); // modifies section handle and title
            $resource->nav('acp.settings');
        });

        $this->assertEquals([
            'title'  => 'Templates',
            'handle' => 'templates',
            'url'    => 'sv/res/t_templates',
        ], Section::get('acp.settings.templates')->fresh()->compose());
    }
}