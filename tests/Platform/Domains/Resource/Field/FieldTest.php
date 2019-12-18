<?php

namespace Tests\Platform\Domains\Resource\Field;

use Illuminate\Http\Request;
use Mockery;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Field\Composer\FormComposer;
use SuperV\Platform\Domains\Resource\Field\Contracts\DecoratesFormComposer;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldTypeInterface;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface;
use SuperV\Platform\Domains\Resource\Form\FormData;
use Tests\Platform\Domains\Resource\Fixtures\FieldTypes\Genius\FormComposerDecorator;
use Tests\Platform\Domains\Resource\Fixtures\FieldTypes\Genius\GeniusType;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class FieldTest extends ResourceTestCase
{
    function test__resolves_default_form_composer_without_form_object()
    {
        $field = FieldFactory::createFromArray(['handle' => 'phone', 'type' => 'text']);

        $formComposerMock = $this->bindMock(FormComposer::class);
        $formComposerMock->expects('setField')->with($field);

        $formComposer = $field->getFormComposer();
        $this->assertInstanceOf(FormComposer::class, $formComposer);
    }

    function test__resolves_default_form_composer_with_form_object()
    {
        $field = FieldFactory::createFromArray(['handle' => 'phone', 'type' => 'text']);
        $form = Mockery::mock(FormInterface::class);

        $formComposerMock = $this->bindMock(FormComposer::class);
        $formComposerMock->expects('setField')->with($field)->andReturnSelf();
        $formComposerMock->expects('setForm')->with($form)->andReturnSelf();

        $this->assertInstanceOf(FormComposer::class, $field->getFormComposer($form));
    }

    function test__decorates_field_types_form_composer()
    {
        $fieldTypeMock = Mockery::mock(GeniusType::class)->makePartial();
        $this->assertInstanceOf(DecoratesFormComposer::class, $fieldTypeMock);
        $decoratorMock = Mockery::mock(FormComposerDecorator::class)->makePartial();

        $fieldTypeMock->expects('getFormComposerDecoratorClass')->andReturn($decoratorMock);

        $field = FieldFactory::createFromArray(['handle' => 'phone', 'type' => $fieldTypeMock]);

        $this->assertInstanceOf(FormComposerDecorator::class, $field->getFormComposer());
    }

    function test__before_resolving_request_callback()
    {
        $requestMock = $this->makePostRequest(['phone' => '32']);

        $field = FieldFactory::createFromArray(['handle' => 'phone', 'type' => 'text']);
        $field->beforeResolvingRequest(function ($value, Request $request) use ($requestMock) {
            $this->assertSame($requestMock, $request);

            return '05'.$value;
        });

        $formDataMock = $this->bindMock(FormData::class);
        $formDataMock->shouldReceive('set')->with('phone', '0532')->once();

        $field->getFieldType()
              ->resolveDataFromRequest($formDataMock, $requestMock);
    }

    function test__before_resolving_entry_callback()
    {
        $entryMock = $this->bindMock(EntryContract::class);

        $field = FieldFactory::createFromArray(['handle' => 'phone', 'type' => 'text']);
        $field->beforeResolvingEntry(
            function (FormData $data, EntryContract $entry, FieldTypeInterface $fieldType) use ($entryMock) {
                $data->set('phone', '532');
                $this->assertSame($entryMock, $entry);
                $this->assertEquals('phone', $fieldType->getFieldHandle());
            });

        $formDataMock = $this->bindMock(FormData::class);
        $formDataMock->shouldReceive('set')->with('phone', '532')->once();

        $field->getFieldType()
              ->resolveDataFromEntry($formDataMock, $entryMock);
    }
}