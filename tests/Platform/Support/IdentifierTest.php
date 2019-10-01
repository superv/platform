<?php

namespace Tests\Platform\Support;

use InvalidArgumentException;
use SuperV\Platform\Support\Identifier;
use SuperV\Platform\Support\IdentifierType;
use Tests\Platform\TestCase;

class IdentifierTest extends TestCase
{
    function test__nodes()
    {
        $this->assertEquals(2, sv_identifier('ab.forms:default')->getNodeCount());
        $this->assertEquals(4, sv_identifier('ab.cd.ef.gab')->getNodeCount());
    }

    function test__validate()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Not a valid identifier string: [ab]');
        sv_identifier('ab');
    }

    function test__parent()
    {
        $this->assertNull(sv_identifier('ab.cd')->parent());

        $this->assertEquals('ab', sv_identifier('ab.cd.ef')->getNamespace());
        $this->assertTrue(sv_identifier('ab.cd.ef')->isNamespace('ab'));

        $this->assertInstanceOf(Identifier::class, sv_identifier('ab.cd.ef')->parent());

        $this->assertEquals('ab.cd', sv_identifier('ab.cd.ef')->parent());
        $this->assertSame('ab.cd', sv_identifier('ab.cd.ef')->getParent());
        $this->assertEquals('ab.cd.ef', sv_identifier('ab.cd.ef.gab')->parent());
    }

    function test__type()
    {
        $this->assertEquals('resources', sv_identifier('ab.orders')->type());
        $this->assertTrue(sv_identifier('ab.orders')->type()->isResource());

        $this->assertEquals('entries', sv_identifier('ab.orders:1')->type());
        $this->assertEquals(1, sv_identifier('ab.orders:1')->getTypeId());
        $this->assertTrue(sv_identifier('ab.orders:1')->type()->isEntry());
        $this->assertEquals('ab.orders', sv_identifier('ab.orders:1')->parent());

        $this->assertInstanceOf(IdentifierType::class, sv_identifier('ab.orders.forms')->type());

        $this->assertEquals('forms', sv_identifier('ab.orders.forms')->type());
        $this->assertTrue(sv_identifier('ab.orders.forms')->type()->isForm());

        $this->assertEquals('forms', sv_identifier('ab.orders.forms:default')->type());
        $this->assertSame('forms', sv_identifier('ab.orders.forms:default')->getType());

        $this->assertEquals('fields', sv_identifier('ab.cd.fields')->type());
        $this->assertEquals('entries', sv_identifier('ab.cd.ef.entries')->type());

        $this->assertEquals('ab.orders.title', sv_identifier('ab.orders.fields:title')->withoutType());
    }

    function test__typeId()
    {
        $this->assertEquals(null, sv_identifier('ab.orders.forms')->getTypeId());
        $this->assertEquals('default', sv_identifier('ab.orders.forms:default')->getTypeId());
        $this->assertEquals('1', sv_identifier('ab.orders.entries:1')->getTypeId());

        $this->assertEquals('default', sv_identifier('ab.orders.forms:default')->type()->id());
        $this->assertEquals(1, sv_identifier('ab.orders.entries:1')->type()->id());
    }

    function test__last_node()
    {
        $this->assertEquals('entries:1', sv_identifier('ab.orders.entries:1')->getLastNode());
    }

    function test__to_array()
    {
        $this->assertEquals(
            [
                'parent'  => 'ab.orders:1',
                'type'    => 'fields',
                'type_id' => 'title',
            ],
            sv_identifier('ab.orders:1.fields:title')->toArray()
        );
    }
}

