<?php

namespace Tests\Platform\Domains\Resource\Field;

use SuperV\Platform\Domains\Resource\Field\Contracts\ComposerInterface;
use SuperV\Platform\Domains\Resource\Field\Contracts\FakerInterface;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldValueInterface;
use SuperV\Platform\Domains\Resource\Field\FieldComposer;
use SuperV\Platform\Domains\Resource\Field\FieldController;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Field\FieldRepository;
use SuperV\Platform\Domains\Resource\Field\FieldType;
use Tests\Platform\Domains\Resource\Fixtures\FieldTypes\Dummy\DummyType;
use Tests\Platform\Domains\Resource\Fixtures\FieldTypes\Genius\Composer as GeniusComposer;
use Tests\Platform\Domains\Resource\Fixtures\FieldTypes\Genius\Controller;
use Tests\Platform\Domains\Resource\Fixtures\FieldTypes\Genius\GeniusType;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class FieldTypeTest extends ResourceTestCase
{
    function test__field_type_registery()
    {
        $this->assertEquals(GeniusType::class, FieldType::resolveTypeClass('genius'));
    }

    function test__resolves_from_container()
    {
        $fieldType = GeniusType::resolve();
        $this->assertInstanceOf(GeniusType::class, $fieldType);
    }

    function test__resolves_custom_field_value_object()
    {
        $fieldValue = $this->makeField('options', DummyType::class)
                           ->getFieldType()->resolveFieldValue();
        $this->assertNull($fieldValue);

        $fieldValue = $this->makeField('options', GeniusType::class)
                           ->getFieldType()->resolveFieldValue();
        $this->assertInstanceOf(FieldValueInterface::class, $fieldValue);
    }

    function test__resolves_default_composer()
    {
        $field = $this->makeField('foo', DummyType::class);
        $composer = $field->getFieldType()->resolveComposer();
        $this->assertInstanceOf(ComposerInterface::class, $composer);
        $this->assertInstanceOf(FieldComposer::class, $composer);
    }

    function test__resolves_custom_composer()
    {
        $field = $this->makeField('foo', GeniusType::class);
        $composer = $field->getFieldType()->resolveComposer();
        $this->assertInstanceOf(ComposerInterface::class, $composer);
        $this->assertInstanceOf(GeniusComposer::class, $composer);
    }

    function test__resolves_faker()
    {
        $faker = GeniusType::resolve()->resolveFaker();
        $this->assertInstanceOf(FakerInterface::class, $faker);
    }

    function test__resolves_controller()
    {
        $controller = GeniusType::resolve()->resolveController();
        $this->assertInstanceOf(Controller::class, $controller);
        $this->assertInstanceOf(FieldController::class, $controller);
    }

    function test__controller_response()
    {
        $user = $this->newUser(['allow' => 'tst.abc.fields:foo']);
        $field = FieldFactory::withIdentifier('tst.abc.fields:foo');

        $response = $this->getJsonUser($field->router()->route('lookup'), $user);
        $response->assertOk();

        $this->assertEquals('the-lookup-response', $response->getContent());
    }

    function test__controller_auth()
    {
        $user = $this->newUser(['allow' => null]);
        $field = FieldFactory::withIdentifier('tst.abc.fields:foo');

        $response = $this->getJsonUser($field->router()->route('lookup'), $user);
        $response->assertStatus(403);
    }

    protected function setUp(): void
    {
        parent::setUp();

        FieldType::register(GeniusType::class);

        FieldRepository::resolve()->create([
            'identifier' => 'tst.abc.fields:foo',
            'type'       => GeniusType::class,
        ]);
    }
}