<?php

namespace Tests\Platform\Domains\Resource\Field;

use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Field\FieldValue;
use SuperV\Platform\Domains\Resource\Field\Types\Text;
use SuperV\Platform\Domains\Resource\Field\Watcher;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class FieldTest extends ResourceTestCase
{
    function test__factory()
    {
        $field = FieldFactory::createFromArray(['name' => 'display_name', 'type' => 'text']);
        $this->assertNotNull($field->uuid());
        $this->assertInstanceOf(FieldValue::class, $field->value());

        $this->assertEquals([
            'type'  => 'text',
            'uuid'  => $field->uuid(),
            'name'  => 'display_name',
            'label' => 'Display Name',
        ], $field->compose());
    }

    function test__notify_watchers()
    {
        $entry = new TestEntry();
        $field = FieldFactory::createFromArray(['name' => 'name', 'type' => 'text']);

        $field->setWatcher($entry);
        $field->setValue('Omar');
        $this->assertEquals('Omar', $entry->name);

        $field->removeWatcher();
        $field->setValue('Hattab');
        $this->assertEquals('Omar', $entry->name);
    }

    function test__field_type()
    {
        $field = FieldFactory::createFromArray(['name' => 'name', 'type' => 'text']);

        $this->assertInstanceOf(Text::class, $field->resolveType());

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