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
        $field = new Field('display_name', 'text');
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
        $field = new Field('name', 'text');

        $field->addWatcher($entry);
        $field->setValue('Omar');
        $this->assertEquals('Omar', $entry->name);

        $field->removeWatcher($entry);
        $field->setValue('Hattab');
        $this->assertEquals('Omar', $entry->name);
    }
}

class TestEntry implements Watcher
{
    public $name;

    public function watchableUpdated($params)
    {
        if ($params instanceof FieldValue) {
            $this->{$params->fieldName()} = $params->get();
        }
    }
}