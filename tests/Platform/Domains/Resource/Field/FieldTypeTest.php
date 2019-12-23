<?php

namespace Tests\Platform\Domains\Resource\Field;

use SuperV\Platform\Domains\Resource\Field\Contracts\ComposerInterface;
use SuperV\Platform\Domains\Resource\Field\Contracts\FakerInterface;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldValueInterface;
use SuperV\Platform\Domains\Resource\Field\FieldComposer;
use Tests\Platform\Domains\Resource\Fixtures\FieldTypes\Dummy\DummyType;
use Tests\Platform\Domains\Resource\Fixtures\FieldTypes\Genius\Composer as GeniusComposer;
use Tests\Platform\Domains\Resource\Fixtures\FieldTypes\Genius\GeniusType;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class FieldTypeTest extends ResourceTestCase
{
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

//    function test__get_value()
//    {
//        $options = ['foo', 'bar'];
//        $optionsJson = json_encode($options);
//
//        $entryMock = $this->makeMock(EntryContract::class);
//        $entryMock->expects('getAttribute')->twice()->with('options')->andReturn($optionsJson);
//
//        // without mutator
//        $value = $this->makeField('options', DummyType::class)
//                      ->getFieldType()
//                      ->getValue($entryMock);
//        $this->assertEquals($optionsJson, $value);
//
//        // with mutator
//        $value = $this->makeField('options', GeniusType::class)
//                      ->getFieldType()
//                      ->getValue($entryMock);
//
//        $this->assertEquals($options, $value);
//    }
//
//    function test__set_value()
//    {
//        $options = ['foo', 'bar'];
//        $optionsJson = json_encode($options);
//
//        $dataMapMock = $this->bindMock(DataMapInterface::class);
//        $dataMapMock->expects('set')->with('options', $optionsJson);
//        // without mutator
//        $value = $this->makeField('options', DummyType::class)
//                      ->getFieldType()
//                      ->setValue($dataMapMock, $optionsJson);
//        $this->assertEquals($optionsJson, $value);
//
//        // with mutator
//        $dataMapMock->expects('set')->with('options', $optionsJson);
//        $value = $this->makeField('options', GeniusType::class)
//                      ->getFieldType()
//                      ->setValue($dataMapMock, $options);
//        $this->assertEquals($optionsJson, $value);
//    }
//
//    function test__resolves_default_mutator()
//    {
//        $mutator = $this->makeField('options', DummyType::class)
//                        ->getFieldType()->resolveMutator();
//        $this->assertNull($mutator);
//
//        $mutator = $this->makeField('options', GeniusType::class)
//                        ->getFieldType()->resolveMutator();
//        $this->assertInstanceOf(GeniusMutator::class, $mutator);
//        $this->assertInstanceOf(FieldMutatorInterface::class, $mutator);
//    }

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
        $fieldType = GeniusType::resolve();
        $faker = $fieldType->resolveFaker();
        $this->assertInstanceOf(FakerInterface::class, $faker);
    }
}