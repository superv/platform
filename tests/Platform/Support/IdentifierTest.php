<?php

namespace Tests\Platform\Support;

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
    function test__parent()
    {
        $this->assertNull(sv_identifier('ab')->parent());
        $this->assertInstanceOf(Identifier::class, sv_identifier('ab.cd')->parent());

        $this->assertEquals('ab', sv_identifier('ab.cd')->parent());
        $this->assertSame('ab', sv_identifier('ab.cd')->getParent());

        $this->assertEquals('ab.cd', sv_identifier('ab.cd.ef')->parent());
        $this->assertEquals('ab.cd.ef', sv_identifier('ab.cd.ef.gab')->parent());
    }

    function test__type()
    {
        $this->assertInstanceOf(IdentifierType::class, sv_identifier('ab.forms')->type());
        $this->assertEquals('forms', sv_identifier('ab.forms')->type());
        $this->assertTrue(sv_identifier('ab.forms')->type()->isForm());

        $this->assertEquals('forms', sv_identifier('ab.forms:default')->type());
        $this->assertSame('forms', sv_identifier('ab.forms:default')->getType());

        $this->assertEquals('fields', sv_identifier('ab.cd.fields')->type());
        $this->assertEquals('entries', sv_identifier('ab.cd.ef.entries')->type());
    }

    function test__typeId()
    {
        $this->assertEquals(null, sv_identifier('ab.forms')->getTypeId());
        $this->assertEquals('default', sv_identifier('ab.forms:default')->getTypeId());
        $this->assertEquals('1', sv_identifier('ab.entries:1')->getTypeId());

        $this->assertEquals('default', sv_identifier('ab.forms:default')->type()->id());
        $this->assertEquals(1, sv_identifier('ab.entries:1')->type()->id());
    }
}

