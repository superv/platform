<?php

namespace Tests\Platform\Domains\Resource\Nav;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Schema\Schema;
use SuperV\Platform\Domains\Resource\Nav\Nav;
use SuperV\Platform\Domains\Resource\Nav\Section;
use SuperV\Platform\Domains\Resource\ResourceConfig;
use SuperV\Platform\Support\Composer\Payload;
use Tests\Platform\Domains\Resource\ResourceTestCase;

/**
 * Class NavTest
 *
 * @package Tests\Platform\Domains\Resource\Nav
 * @group   resource
 */
class NavTest extends ResourceTestCase
{
    function test__build_section()
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

    function test__easy_create()
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

    function test__deep_level()
    {
        $nav = Nav::create('sv');

        $a = $nav->add('a.a.a.a.a');
        $a->add('b.a.a.a');
        $a->add('c.a.a.a');
        $a->add('d.a.a.a');

        $this->assertEquals(18, Section::count());
    }

    function test__this_is_soo_easy()
    {
        Nav::create('a.b.c.d.e.f');
        $this->assertEquals(6, Section::count());

        Nav::create('a.a.a');
        $this->assertEquals(8, Section::count());

        Nav::create('a.a.b');
        $this->assertEquals(9, Section::count());
    }

    function test__composes_nav()
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
                'foo' =>
                    [
                        'title'    => 'Foo',
                        'handle'   => 'foo',
                        'sections' => [
                            'bar' =>
                                [
                                    'title'  => 'Bar',
                                    'handle' => 'bar',
                                ],
                            'baz' =>
                                [
                                    'title'    => 'Baz',
                                    'handle'   => 'baz',
                                    'sections' => [
                                        'bom' =>
                                            [
                                                'title'  => 'Bom',
                                                'handle' => 'bom',
                                            ],
                                    ],
                                ],
                        ],
                    ],
                'bar' =>
                [
                    'title'    => 'Bar',
                    'handle'   => 'bar',
                    'sections' => [
                        'baz' =>
                            [
                                'title'  => 'Baz',
                                'handle' => 'baz',
                            ],
                        'foo' =>
                            [
                                'title'  => 'Foo',
                                'handle' => 'foo',
                            ],
                    ],
                ],
            ],
        ], Nav::get('acp')->compose());
    }

    function test__get_from_full_handle()
    {
        Nav::create('acp.settings.auth');

        $auth = Section::get('acp.settings.auth');
        $this->assertEquals('auth', $auth->getHandle());

        Nav::create('a.b.c.d.e.f');

        $e = Section::get('a.b.c.d.e');
        $this->assertEquals('E', $e->getTitle());
        $this->assertEquals(1, $e->children()->count());
    }

    function test__creates_from_resource_blueprint()
    {
        Schema::create('tbl_users', function (Blueprint $table, ResourceConfig $resource) {
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
            'url'    => 'sv/res/testing.users',
        ], Section::get('acp.settings.auth.users')->compose());

        Schema::create('t_templates', function (Blueprint $table, ResourceConfig $resource) {
            $table->increments('id');
            $resource->label('Templates'); // modifies section handle and title
            $resource->nav('acp.settings');
        });

        $this->assertEquals([
            'title'  => 'Templates',
            'handle' => 'templates',
            'url'    => 'sv/res/platform.t_templates',
        ], Section::get('acp.settings.templates')->fresh()->compose());
    }

    function test__inject_while_building()
    {
        Nav::create('acp.settings.auth');
        Nav::building('acp.settings', function (Payload $payload) {
            $payload->push('sections', ['title'  => 'Foo',
                                        'handle' => 'foo']);
        });

        Nav::building('acp.settings', 'Bar', 'bar');

        $composed = Nav::get('acp')->compose();
        $this->assertEquals(3, count(array_get($composed, 'sections.settings.sections')));
    }

    function test__adds_full_colophon_for_authorization_check()
    {
        $nav = Nav::create('acp');
        $nav->add('foo.baz.bom');

        $this->assertEquals([
            'title'    => 'Acp',
            'handle'   => 'acp',
            'colophon' => 'acp',
            'sections' => [
                'foo' => [
                    'title'    => 'Foo',
                    'handle'   => 'foo',
                    'colophon' => 'acp.foo',
                    'sections' => [
                        'baz' =>
                            [
                                'title'    => 'Baz',
                                'handle'   => 'baz',
                                'colophon' => 'acp.foo.baz',
                                'sections' => [
                                    'bom' =>
                                        [
                                            'title'    => 'Bom',
                                            'handle'   => 'bom',
                                            'colophon' => 'acp.foo.baz.bom',
                                        ],
                                ],
                            ],
                    ],
                ],
            ],
        ], Nav::get('acp')->compose($withColophon = true));
    }

    protected function setUp()
    {
        parent::setUp();

        Section::truncate();
    }
}
