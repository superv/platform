<?php

namespace Tests\Platform\Domains\Resource\Field;

use SuperV\Platform\Domains\Resource\Field\Composer;
use SuperV\Platform\Domains\Resource\Field\Contracts\ComposerInterface;
use SuperV\Platform\Domains\Resource\Field\Contracts\FakerInterface;
use SuperV\Platform\Domains\Resource\Field\Contracts\MutatorInterface;
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

//    function test__resolves_default_mutator()
//    {
//        $field = $this->makeField('foo', DummyType::class);
//        $mutator = $field->getFieldType()->resolveMutator();
//        $this->assertInstanceOf(MutatorInterface::class, $mutator);
//    }
//
//    function test__resolves_custom_mutator()
//    {
//        $field = $this->makeField('foo', GeniusType::class);
//        $mutator = $field->getFieldType()->resolveMutator();
//        $this->assertInstanceOf(MutatorInterface::class, $mutator);
//        $this->assertInstanceOf(GeniusMutator::class, $mutator);
//    }

    function test__resolves_default_composer()
    {
        $field = $this->makeField('foo', DummyType::class);
        $composer = $field->getFieldType()->resolveComposer();
        $this->assertInstanceOf(ComposerInterface::class, $composer);
        $this->assertInstanceOf(Composer::class, $composer);
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