<?php

namespace Tests\Platform\Domains\Resource\Nav;

use SuperV\Platform\Domains\Resource\Nav\Nav;
use SuperV\Platform\Domains\Resource\Nav\NavGuard;
use SuperV\Platform\Domains\Resource\Nav\Section;
use Tests\Platform\Domains\Resource\ResourceTestCase;

/**
 * Class NavGuardTest
 *
 * @package Tests\Platform\Domains\Resource\Nav
 * @group   resource
 */
class NavGuardTest extends ResourceTestCase
{
    function test__filters_nav()
    {
        $nav = Nav::create('acp');
        $nav->add('foo.bar');
        $nav->add('foo.baz.bom');
        $nav->add('foo.bom.tac');
        $nav->add('foo.bom.tic');
        $nav->add('foo.bom.toe');

        $user = $this->newUser();
        $user->allow('foo');
        $user->allow('foo.baz');
        $user->allow('foo.bom.*');
        $user->forbid('foo.bom.toe');

        $filtered = (new NavGuard($user, Nav::get('acp')))->compose();

        $baz = __section('baz');
        $bom = __section('bom', [
           'tac' => __section('tac'),
           'tic' => __section('tic'),
        ]);
        $expected = ['foo' => __section('foo', [
            'baz' => $baz,
            'bom' => $bom,
        ])];
        $this->assertEquals($expected, $filtered['sections']);
    }

    protected function setUp()
    {
        parent::setUp();

        Section::truncate();
    }
}

function __section($handle, array $sections = null)
{
    return array_filter([
        'title'    => ucwords(str_unslug($handle)),
        'handle'   => $handle,
        'sections' => $sections,
    ]);
    return [$handle => array_filter([
        'title'    => ucwords(str_unslug($handle)),
        'handle'   => $handle,
        'sections' => $sections,
    ])];
}