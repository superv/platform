<?php

namespace Tests\Platform\Domains\Resource\Field;

use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Resource\Field\FieldValue;
use SuperV\Platform\Domains\Resource\Field\Watcher;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class FieldTest extends ResourceTestCase
{
    /** @test */
    function instantiates()
    {
        $field = Field::make(['name' => 'display_name', 'type' => 'text']);
        $this->assertNotNull($field->uuid());
        $this->assertInstanceOf(FieldValue::class, $field->value());

        $this->assertEquals([
            'type'  => 'text',
            'uuid'  => $field->uuid(),
            'name'  => 'display_name',
            'label' => 'Display Name',
        ], $field->compose());
    }

    /** @test */
    function notify_watchers()
    {
        $entry = new TestEntry();
        $field = Field::make(['name' => 'name', 'type' => 'text']);

        $field->setWatcher($entry);
        $field->setValue('Omar');
        $this->assertEquals('Omar', $entry->name);

        $field->removeWatcher();
        $field->setValue('Hattab');
        $this->assertEquals('Omar', $entry->name);
    }
}

class TestEntry implements Watcher
{
    public $name;

    public function setAttribute($key, $value)
    {
        $this->{$key} = $value;
    }

    public function getAttribute($key)
    {
    }

    public function save()
    {
    }
}