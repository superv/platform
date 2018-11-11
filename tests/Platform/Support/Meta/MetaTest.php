<?php

namespace Tests\Platform\Support\Meta;

use ArrayAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Platform\Contracts\Repository;
use SuperV\Platform\Domains\Resource\Testing\ResourceTestHelpers;
use SuperV\Platform\Support\Meta\Meta;
use Tests\Platform\TestCase;

class MetaTest extends TestCase
{
    use RefreshDatabase;
    use ResourceTestHelpers;

    /** @var array */
    protected $metadata;

    /** @var \SuperV\Platform\Support\Meta\Meta */
    protected $meta;

    protected function setUp()
    {
        parent::setUp();

        $this->metadata = [
            'type'   => 'string',
            'length' => 255,
        ];

        $this->meta = new Meta($this->metadata);
    }

    function test__construct()
    {
        $this->assertInstanceOf(ArrayAccess::class, $this->meta);
    }

    function test__all()
    {
        $this->assertEquals($this->metadata, $this->meta->all());
    }

    function test__has()
    {
        $this->assertTrue($this->meta->has('type'));
    }

    function test__get()
    {
        $this->assertEquals('string', $this->meta->get('type'));
    }

    function test__get_with_dot_notation()
    {
        $meta = new Meta(['abc' => ['def' => true]]);
        $this->assertTrue($meta->get('abc.def'));

        $meta = new Meta(['abc' => ['def' => ['ghi' => true]]]);
        $this->assertTrue($meta->get('abc.def.ghi'));

        $meta = new Meta(['abc' => ['def' => ['ghi' => ['jkl' => true]]]]);
        $this->assertTrue($meta->get('abc.def.ghi.jkl'));

        $this->assertNull($meta->get('abc.xyz'));
        $this->assertNull($meta->get('abc.def.xyz'));
    }

    function test__get_with_default()
    {
        $this->assertEquals('default', $this->meta->get('nobody', 'default'));
    }

    function test__set()
    {
        $this->meta->set('label', 'logo');
        $this->assertEquals('logo', $this->meta->get('label'));
    }

    function test__set_with_dot_notation()
    {
        $meta = new Meta();
        $meta->set('rules.nullable', true);

        $this->assertEquals(['rules' => ['nullable' => true]], $meta->all());
    }

    function test_set_converts_array_values()
    {
        $meta = new Meta([
            'config' => ['rules' => ['unique', 'required']],
        ]);

        $this->assertEquals(['rules' => ['unique', 'required']], $meta->get('config'));
        $this->assertEquals(['unique', 'required'], $meta->get('config.rules'));
        $this->assertEquals('unique', $meta->get('config.rules.0'));
    }

    function test__offset_exists()
    {
        $this->assertTrue($this->meta->offsetExists('type'));
    }

    function test__offset_get()
    {
        $this->assertEquals('string', $this->meta->offsetGet('type'));
    }

    function test__offset_set()
    {
        $this->meta->offsetSet('type', 'number');
        $this->assertEquals('number', $this->meta->offsetGet('type'));
    }

    function test__offset_unset()
    {
        $this->meta->offsetUnset('type');
        $this->assertNull($this->meta->offsetGet('type'));
    }
}

