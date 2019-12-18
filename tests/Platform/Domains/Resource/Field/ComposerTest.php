<?php

namespace Tests\Platform\Domains\Resource\Field;

use SuperV\Platform\Domains\Resource\Database\Entry\ResourceEntry;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Support\Composer\Payload;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class ComposerTest extends ResourceTestCase
{
    function test__composer_to_table_data()
    {
        $entryMock = $this->makeMock(ResourceEntry::class);
        $entryMock->expects('getAttribute')->with('foo')->andReturn('foo-value');

        $field = $this->makeField('foo', 'text', ['classes' => 'foo-classes']);
        $payload = $field->getComposer()->toTable($entryMock);

        $this->assertInstanceOf(Payload::class, $payload);
    }

    function test__composer_to_view()
    {
        $entryMock = $this->makeMock(ResourceEntry::class);
        $entryMock->expects('getAttribute')->with('foo')->andReturn('foo-value');

        $field = $this->makeField('foo', 'text', ['classes' => 'foo-classes']);
        $payload = $field->getComposer()->toView($entryMock);

        $this->assertInstanceOf(Payload::class, $payload);
        $this->assertEquals('foo-value', $payload->get('value'));
        $this->assertEquals('foo-classes', $payload->get('classes'));
        $this->assertTrue($payload->get('presenting'));
    }

    function test__compose_to_form()
    {
        $field = $this->makeField('foo', 'text');
        $payload = $field->getComposer()->toForm();

        $this->assertInstanceOf(Payload::class, $payload);
        $this->assertEquals('__placeholder', $payload->get('placeholder'));
        $this->assertEquals('__hint', $payload->get('hint'));
        $this->assertEquals('__meta', $payload->get('meta'));
        $this->assertEquals('__presenting', $payload->get('presenting'));
    }

    function test__compose()
    {
        $payload = $this->makeField('foo', 'text')->getComposer()->compose();

        $this->assertInstanceOf(Payload::class, $payload);
        $this->assertEquals('sv.foo', $payload->get('identifier'));
        $this->assertEquals('foo', $payload->get('handle'));
        $this->assertEquals('text', $payload->get('type'));
        $this->assertEquals('sv_text_field', $payload->get('component'));
        $this->assertEquals('Foo', $payload->get('label'));
    }

    protected function makeField(string $handle = 'foo', $type = 'text', array $config = []): FieldInterface
    {
        return FieldFactory::createFromArray([
            'identifier'  => 'sv.foo',
            'handle'      => 'foo',
            'type'        => 'text',
            'component'   => 'sv_text_field',
            'placeholder' => '__placeholder',
            'config'      => array_merge([
                'meta'       => '__meta',
                'hint'       => '__hint',
                'presenting' => '__presenting',
            ], $config),
        ]);
    }
}