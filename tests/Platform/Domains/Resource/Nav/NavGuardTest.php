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

        $nav->add('app.zac');
        $nav->add('app.top');

        $nav->add('core.foo');

        $user = $this->newUser(['allow' => false]);
        $user->allow('foo');
        $user->allow('foo.baz');
        $user->allow('foo.bom.*');
        $user->forbid('foo.bom.toe');

        $user->allow('app.*');
        $user->forbid('app.zac.*');

        $user->forbid('core.*');

        $filtered = (new NavGuard($user, Nav::get('acp')))->compose();

//        dd($filtered['sections']);

        $baz = __section('baz');
        $bom = __section('bom', [
            'tac' => __section('tac'),
            'tic' => __section('tic'),
        ]);
        $this->assertEquals(__section('foo', [
            'baz' => $baz,
            'bom' => $bom,
        ]), $filtered['sections']['foo']);

        $this->assertTrue(collect($filtered['sections']['app']['sections'])->keys()->diff(['top'])->isEmpty());

        $this->assertNull($filtered['sections']['core'] ?? null);
    }

    protected function setUp(): void
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
}
