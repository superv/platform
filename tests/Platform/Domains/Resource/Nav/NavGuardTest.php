<?php

namespace Tests\Platform\Domains\Resource\Nav;

use SuperV\Platform\Domains\Resource\Nav\Nav;
use SuperV\Platform\Domains\Resource\Nav\NavGuard;
use SuperV\Platform\Domains\Resource\Nav\Section;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class NavGuardTest extends ResourceTestCase
{
    protected function setUp()
    {
        parent::setUp();

        Section::truncate();
    }

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

        $this->assertEquals([
            __section('foo', [
                __section('baz'),
                __section('bom', [
                    __section('tac'),
                    __section('tic'),
                ]),
            ]),
        ], $filtered['sections']);
    }
}

function __section($handle, array $sections = null)
{
    return array_filter([
        'title'    => ucwords(str_unslug($handle)),
        'handle'   => $handle,
        'sections' => $sections,
    ]);
}