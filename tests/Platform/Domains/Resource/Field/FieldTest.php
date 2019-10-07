<?php

namespace Tests\Platform\Domains\Resource\Field;

use Illuminate\Http\Request;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldTypeInterface;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Form\FormData;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class FieldTest extends ResourceTestCase
{
    function test__before_resolving_request_callback()
    {
        $requestMock = $this->makePostRequest(['phone' => '32']);

        $field = FieldFactory::createFromArray(['name' => 'phone', 'type' => 'text']);
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

        $field = FieldFactory::createFromArray(['name' => 'phone', 'type' => 'text']);
        $field->beforeResolvingEntry(
            function (FormData $data, EntryContract $entry, FieldTypeInterface $fieldType) use ($entryMock) {
                $data->set('phone', '532');
                $this->assertSame($entryMock, $entry);
                $this->assertEquals('phone', $fieldType->getName());
            });

        $formDataMock = $this->bindMock(FormData::class);
        $formDataMock->shouldReceive('set')->with('phone', '532')->once();

        $field->getFieldType()
              ->resolveDataFromEntry($formDataMock, $entryMock);
    }
}