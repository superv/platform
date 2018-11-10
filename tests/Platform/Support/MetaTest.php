<?php

namespace Tests\Platform\Support;

use ArrayAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Platform\Contracts\Repository;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use SuperV\Platform\Domains\Resource\Testing\ResourceTestHelpers;
use SuperV\Platform\Support\Meta\Meta;
use SuperV\Platform\Support\Meta\MetaValue;
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
        $this->assertInstanceOf(Repository::class, $this->meta);
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
        $meta = new Meta(['level_1' => ['level_2' => true]]);
        $this->assertTrue($meta->get('level_1.level_2'));

        $meta = new Meta(['level_1' => ['level_2' => ['level_3' => true]]]);
        $this->assertTrue($meta->get('level_1.level_2.level_3'));

        $meta = new Meta(['level_1' => ['level_2' => ['level_3' => ['level_4' => true]]]]);
        $this->assertTrue($meta->get('level_1.level_2.level_3.level_4'));
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

    function test_set_converts_array_values()
    {
        $meta = new Meta([
            'config' => [
                'rules' => [
                    'unique' => true,
                ]],
        ]);

        $this->assertInstanceOf(Meta::class, $meta->get('config'));
    }

    function test_set_array()
    {
        $this->meta->set([
            'min'  => 10,
            'max'  => 99,
            'type' => 'text',
        ]);

        $this->assertEquals('10', $this->meta->get('min'));
        $this->assertEquals('99', $this->meta->get('max'));
        $this->assertEquals('text', $this->meta->get('type'));
    }

    function test__push_to_existing_key()
    {
        $this->meta->push('rules', 'required');

        $this->assertEquals('required', $this->meta->get('rules.0'));
    }

    function test__push_to_non_existing_key()
    {
        $this->meta->push('config', 'required');

        $this->assertEquals('required', $this->meta->get('config.0'));
    }

    function test__create()
    {
        $metaKeys = ResourceFactory::make('sv_meta_keys');
        $meta = Meta::create(['type' => 'string', 'length' => 255]);

        $this->assertNotNull($meta->uuid());
        $this->assertEquals(2, $metaKeys->count());
    }

    function test__update()
    {
        $metaKeys = ResourceFactory::make('sv_meta_keys');
        $meta = Meta::create(['type' => 'string', 'length' => 255]);
        $meta->set('length', 128);
        $meta->save();

        $this->assertEquals(2, $metaKeys->count());
        $lengthValue = $metaKeys->newQuery()
                                ->where('uuid', $meta->uuid())
                                ->where('key', 'length')
                                ->value('value');
        $this->assertEquals(128, $lengthValue);
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

