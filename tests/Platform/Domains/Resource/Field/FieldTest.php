<?php

namespace Tests\Platform\Domains\Resource\Field;

use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldValueInterface;
use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Resource\Form\FormData;
use Tests\Platform\Domains\Resource\Fixtures\FieldTypes\Dummy\DummyType;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class FieldTest extends ResourceTestCase
{
    function test__resolves_default_field_value_object()
    {
        $fieldTypeMock = $this->makeMock(DummyType::resolve())->makePartial();
        $fieldTypeMock->expects('resolveFieldValue')->andReturnNull();

        $containerMock = $this->bindMock(Container::class);
        $field = (new Field($containerMock))->setFieldType($fieldTypeMock);

        $fieldValueMock = $this->makeMock(FieldValueInterface::class);
        $fieldValueMock->expects('setField')->with($field)->andReturnSelf();
        $containerMock->expects('make')->with(FieldValueInterface::class)->andReturn($fieldValueMock);

        $value = $field->getValue();
        $this->assertInstanceOf(FieldValueInterface::class, $value);
        $this->assertSame($fieldValueMock, $value);
    }

    function test__resolves_custom_field_value_object()
    {
        $fieldValueMock = $this->makeMock(FieldValueInterface::class);

        $fieldTypeMock = $this->makeMock(DummyType::resolve())->makePartial();
        $fieldTypeMock->expects('resolveFieldValue')->andReturn($fieldValueMock);

        $containerMock = $this->bindMock(Container::class);
        $field = (new Field($containerMock))->setFieldType($fieldTypeMock);

        $containerMock->shouldNotReceive('make');

        $value = $field->getValue();
        $this->assertSame($fieldValueMock, $value);
    }

    function test__before_resolving_request_callback()
    {
        $requestMock = $this->makePostRequest(['phone' => '32']);

        $field = $this->makeField('phone');
        $field->beforeResolvingRequest(function ($value, Request $request) use ($requestMock) {
            $this->assertSame($requestMock, $request);

            return '05'.$value;
        });

        $formDataMock = $this->bindMock(FormData::class);
        $formDataMock->expects('set')->with('phone', '0532');

        $field->getValue()->setRequest($requestMock)->resolve()->mapTo($formDataMock);

//        $field->getFieldType()
//              ->resolveDataFromRequest($formDataMock, $requestMock);
    }

    function test__before_resolving_entry_callback()
    {
        $entryMock = $this->makeMock(EntryContract::class);

        $field = $this->makeField('phone');
//        $field->beforeResolvingEntry(
//            function (FormData $data, EntryContract $entry, FieldTypeInterface $fieldType) use ($entryMock) {
//                $data->set('phone', '532');
//                $this->assertSame($entryMock, $entry);
//                $this->assertEquals('phone', $fieldType->getFieldHandle());
//            });

        $field->beforeResolvingEntry(
            function (FieldValueInterface $fieldValue, EntryContract $entry) use ($entryMock) {
                $fieldValue->set('532');
                $this->assertSame($entryMock, $entry);
            });

        $formDataMock = $this->bindMock(FormData::class);
        $formDataMock->expects('set')->with('phone', '0532');

        $field->getValue()->setEntry($entryMock)->resolve()->mapTo($formDataMock);
//
//        $field->getFieldType()
//              ->resolveDataFromEntry($formDataMock, $entryMock);
    }
}