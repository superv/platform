<?php

namespace Tests\Platform\Domains\Resource;

use Platform;
use SuperV\Platform\Domains\Port\Port;
use SuperV\Platform\Domains\Resource\Nav;
use SuperV\Platform\Domains\Resource\Nav\NavModel;
use SuperV\Platform\Domains\Resource\ResourceModel;

class NavigationTest extends ResourceTestCase
{
    /** @test */
    function builds_navigation()
    {
        $this->makeResource('no_nav_resource_a');
        $this->makeResource('no_nav_resource_b');
        $this->makeResource('no_nav_resource_c');

        $this->makeResource('t_users', [], ['nav' => 'acp.settings.auth', 'label' => 'System Users']);
        $navEntry = NavModel::query()->latest()->first();
        $this->assertArrayContains([
            'nav'         => 'acp',
            'section'     => 'settings',
            'subsection'  => 'auth',
            'slug'        => 'system_users',
            'title'       => 'System Users',
            'resource_id' => ResourceModel::withSlug('t_users')->getId(),
        ], $navEntry->toArray());
    }

    /** @test */
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