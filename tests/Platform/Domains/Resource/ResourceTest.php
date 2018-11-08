<?php

namespace Tests\Platform\Domains\Resource;

use Exception;
use Illuminate\Database\Eloquent\Model;
use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Resource\Field\Types\Number;
use SuperV\Platform\Domains\Resource\Field\Types\Text;
use SuperV\Platform\Domains\Resource\Field\Types\Textarea;
use SuperV\Platform\Domains\Resource\Resource;

class ResourceTest extends ResourceTestCase
{
    /** @test */
    function creates_anonymous_model_class_if_not_provided()
    {
        $resource = $this->makeResource('test_users');

        $resourceModel = $resource->newEntryInstance();

        $this->assertInstanceOf(Model::class, $resourceModel);
        $this->assertEquals('test_users', $resourceModel->getTable());
    }

    /** @test */
    function builds_resource()
    {
        $resource = $this->makeResource('test_users', ['name', 'age:integer', 'bio:text']);
        $resource->build();
        $entry = $resource->newEntryInstance()->newQuery()->create([
            'name' => 'Name',
            'age'  => 99,
            'bio'  => 'Bio',
        ]);
        $resource->loadEntry($entry->getKey());

        $this->assertEquals($entry->fresh(), $resource->getEntry());
        $this->assertTrue($resource->isBuilt());

        $this->assertEquals([
            ['class' => Text::class, 'value' => 'Name'],
            ['class' => Number::class, 'value' => 99],
            ['class' => Textarea::class, 'value' => 'Bio'],
        ], $this->getFields($resource));
    }

    /** @test */
    function should_fail_if_field_requested_before_resource_is_built()
    {
        $resource = $this->makeResource('test_users', ['name']);
        try {
            $resource->getField('name');
        } catch (Exception $e) {
            $this->addToAssertionCount(1);

            return;
        }
        $this->fail('Failed to check if resource is built');
    }

    protected function getFields(Resource $resource)
    {
        return $resource->getFields()->map(
            function (Field $field) {
                return [
                    'class' => get_class($field),
                    'value' => $field->getValue(),
                ];
            }
        )->values()->all();
    }
}

