<?php

namespace Tests\Platform\Domains\Resource\Field;

use Event;
use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Field\FieldModel;
use Tests\Platform\Domains\Resource\ResourceTestCase;

/**
 * Class FieldFactoryTest
 *
 * @package Tests\Platform\Domains\Resource\Field
 * @group   resource
 */
class FieldFactoryTest extends ResourceTestCase
{
    function test__make_field_from_field_entry()
    {
        $fieldEntry = new FieldModel(['identifier' => uuid(), 'name' => 'title', 'type' => 'text']);

        $field = FieldFactory::createFromEntry($fieldEntry);
        $this->assertInstanceOf(Field::class, $field);
        $this->assertEquals('title', $field->getName());
        $this->assertEquals('text', $field->getFieldType());
    }

    function test__dispatches_event_when_field_is_resolved()
    {
        $fieldEntry = new FieldModel($this->makeFieldAttributes());

        $eventName = $fieldEntry->getIdentifier().'.events:resolved';

        Event::fake($eventName);

        $field = FieldFactory::createFromEntry($fieldEntry);

        Event::assertDispatched($eventName, function ($eventName, Field $payload) use ($field) {
            return $field->getIdentifier() === $payload->getIdentifier();
        });
    }
}
