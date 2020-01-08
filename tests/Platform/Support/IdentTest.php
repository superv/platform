<?php

namespace Tests\Platform\Support;

use InvalidArgumentException;
use SuperV\Platform\Support\Ident;
use SuperV\Platform\Support\IdentType;
use Tests\Platform\TestCase;

/**
 * Class IdentTest
 * sv.platform.resources:
 * sv.platform.addons.fields:handle                 sv/platform/res/{res}/fld/handle/act/
 * sv.platform.forms.lists:default                  sv/platform/res/{res}/lst/default
 * sv.platform.resources.entry.events:retrieved
 *
 * @package Tests\Platform\Support
 */
class IdentTest extends TestCase
{
    function test__nodes()
    {
        $this->assertEquals(2, sv_ident('ab.forms:default')->getNodeCount());
        $this->assertEquals(4, sv_ident('ab.cd.ef.gab')->getNodeCount());

        $ident = sv_ident('sv.testing.posts.fields:title');
        $this->assertEquals('sv', $ident->vendor());
        $this->assertEquals('sv.testing', $ident->addon());
        $this->assertEquals('sv.testing.posts', $ident->resource());
    }

    function test__validate()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Ident string must contain at least 2 nodes: [ab]');
        sv_ident('ab');
    }

    function test__parent()
    {
        $this->assertNull(sv_ident('ab.cd')->parent());

        $this->assertEquals('ab', sv_ident('ab.cd.ef')->getNamespace());
        $this->assertTrue(sv_ident('ab.cd.ef')->isNamespace('ab'));

        $this->assertEquals('cd', sv_ident('ab.cd.ef')->getResource());
        $this->assertEquals('cd', sv_ident('ab.cd')->getResource());

        $this->assertInstanceOf(Ident::class, sv_ident('ab.cd.ef')->parent());

        $this->assertEquals('ab.cd', sv_ident('ab.cd.ef')->parent());
        $this->assertSame('ab.cd', sv_ident('ab.cd.ef')->getParent());
        $this->assertEquals('ab.cd.ef', sv_ident('ab.cd.ef.gab')->parent());
    }

    function __type()
    {
        $this->assertEquals('resources', sv_ident('ab.orders')->type());
        $this->assertTrue(sv_ident('ab.orders')->type()->isResource());

        $this->assertEquals('entries', sv_ident('ab.orders:1')->type());
        $this->assertEquals(1, sv_ident('ab.orders:1')->handle());
        $this->assertTrue(sv_ident('ab.orders:1')->type()->isEntry());
        $this->assertEquals('ab.orders', sv_ident('ab.orders:1')->parent());

        $this->assertInstanceOf(IdentType::class, sv_ident('ab.orders.forms')->type());

        $this->assertEquals('forms', sv_ident('ab.orders.forms')->type());
        $this->assertTrue(sv_ident('ab.orders.forms')->type()->isForm());

        $this->assertEquals('forms', sv_ident('ab.orders.forms:default')->type());
        $this->assertSame('forms', sv_ident('ab.orders.forms:default')->getType());

        $this->assertEquals('fields', sv_ident('ab.cd.fields')->type());
        $this->assertEquals('entries', sv_ident('ab.cd.ef.entries')->type());

        $this->assertEquals('ab.orders.title', sv_ident('ab.orders.fields:title')->withoutType());
    }

    function test__handle()
    {
        $this->assertEquals(null, sv_ident('ab.orders.forms')->handle());
        $this->assertEquals('default', sv_ident('ab.orders.forms:default')->handle());
        $this->assertEquals('1', sv_ident('ab.orders.entries:1')->handle());

//        $this->assertEquals('default', sv_ident('ab.orders.forms:default')->type()->handle());
//        $this->assertEquals(1, sv_ident('ab.orders.entries:1')->type()->handle());
    }

    function test__last_node()
    {
        $this->assertEquals('entries:1', sv_ident('ab.orders.entries:1')->getLastNode());
    }

    function test__to_array()
    {
        $this->assertEquals(
            [
                'parent'  => 'ab.orders:1',
                'type'    => 'fields',
                'type_id' => 'title',
            ],
            sv_ident('ab.orders:1.fields:title')->toArray()
        );
    }
}

