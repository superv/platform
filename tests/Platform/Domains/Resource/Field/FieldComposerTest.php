<?php

namespace Tests\Platform\Domains\Resource\Field;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldValueInterface;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface;
use SuperV\Platform\Support\Composer\Payload;
use Tests\Platform\Domains\Resource\Fixtures\FieldTypes\Genius\GeniusType;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class FieldComposerTest extends ResourceTestCase
{
    function test__composer_to_table_data()
    {
        $this->makeFieldValue(
            $field = $this->makeField('foo'),
            $entryMock = $this->makeMock(EntryContract::class)
        );

        $payload = $field->getComposer()->toTable($entryMock);

        $this->assertInstanceOf(Payload::class, $payload);
    }

    function test__composer_to_view()
    {
        $this->makeFieldValue(
            $field = $this->makeField('foo', 'text', ['classes' => 'foo-classes']),
            $entryMock = $this->makeMock(EntryContract::class)
        );

        $payload = $field->getComposer()->toView($entryMock);

        $this->assertInstanceOf(Payload::class, $payload);
        $this->assertEquals('foo-value', $payload->get('value'));
        $this->assertEquals('foo-classes', $payload->get('classes'));
        $this->assertTrue($payload->get('presenting'));
    }

    function test__compose_to_create_form()
    {
        $payload = $this->makeField('foo')->getComposer()->toForm();

        $this->assertNull($payload->get('value'));
        $this->assertEquals('__placeholder', $payload->get('placeholder'));
        $this->assertEquals('__hint', $payload->get('hint'));
        $this->assertEquals('__meta', $payload->get('meta'));
        $this->assertEquals('__presenting', $payload->get('presenting'));
    }

    function test__compose_to_update_form()
    {
        $this->makeFieldValue(
            $field = $this->makeField('foo'),
            $entryMock = $this->makeMock(EntryContract::class)
        );

        $formMock = $this->makeMock(FormInterface::class);
        $formMock->expects('getEntry')->andReturn($entryMock);

        $payload = $field->getComposer()->toForm($formMock);
        $this->assertEquals('foo-value', $payload->get('value'));
    }

    function test__compose()
    {
        $payload = $this->makeField('foo', GeniusType::class)->getComposer()->compose();

        $this->assertInstanceOf(Payload::class, $payload);
        $this->assertNull($payload->get('value'));
        $this->assertEquals('sv.foo', $payload->get('identifier'));
        $this->assertEquals('foo', $payload->get('handle'));
        $this->assertEquals('genius', $payload->get('type'));
        $this->assertEquals('sv_genius_field', $payload->get('component'));
        $this->assertEquals('Foo', $payload->get('label'));
    }

    protected function makeField(string $handle = 'foo', $type = 'text', array $config = []): FieldInterface
    {
        return FieldFactory::createFromArray([
            'identifier'  => 'sv.foo',
            'handle'      => 'foo',
            'type'        => $type,
            'placeholder' => '__placeholder',
            'config'      => array_merge([
                'meta'       => '__meta',
                'hint'       => '__hint',
                'presenting' => '__presenting',
            ], $config),
        ]);
    }

    /**
     * @param \SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface $field
     * @param                                                                  $entryMock
     */
    protected function makeFieldValue(FieldInterface $field, $entryMock): void
    {
        $fieldValueMock = $this->bindMock(FieldValueInterface::class);
        $fieldValueMock->expects('setField')->with($field)->andReturnSelf();
        $fieldValueMock->expects('setEntry')->with($entryMock)->andReturnSelf();
        $fieldValueMock->expects('resolve')->andReturnSelf();
        $fieldValueMock->expects('get')->andReturn('foo-value');
    }
}

