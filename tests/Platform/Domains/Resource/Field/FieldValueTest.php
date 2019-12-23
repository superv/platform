<?php

namespace Tests\Platform\Domains\Resource\Field;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Contracts\DataMapInterface;
use SuperV\Platform\Domains\Resource\Field\FieldValue;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class FieldValueTest extends ResourceTestCase
{
    function test__resolve_from_entry()
    {
        $entryMock = $this->makeMock(EntryContract::class);
        $entryMock->expects('getAttribute')->with('name')->andReturn('Adam');

        $fieldValue = FieldValue::of($this->makeField('name'));
        $this->assertEquals('Adam', $fieldValue->setEntry($entryMock)->resolve()->get());
    }

    function test__resolve_from_request()
    {
        $requestMock = $this->makePostRequest(['name' => 'Adam']);

        $fieldValue = FieldValue::of($this->makeField('name'));
        $this->assertEquals('Adam', $fieldValue->setRequest($requestMock)->resolve()->get());
    }

    function test__map_request_to_data_map()
    {
        $dataMap = $this->makeMock(DataMapInterface::class);
        $dataMap->expects('set')->with('name', 'Adam');

        $requestMock = $this->makePostRequest(['name' => 'Adam']);
        $fieldValue = FieldValue::of($this->makeField('name'));
        $fieldValue->setRequest($requestMock)->resolve();
        $fieldValue->mapTo($dataMap);
    }
}