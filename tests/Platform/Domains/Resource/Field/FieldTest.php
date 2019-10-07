<?php

namespace Tests\Platform\Domains\Resource\Field;

use Illuminate\Http\Request;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Form\FormData;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class FieldTest extends ResourceTestCase
{
    function test__when_resolving_request()
    {
        $field = FieldFactory::createFromArray(['name' => 'phone', 'type' => 'text']);
        $field->whenResolvingRequest(function ($value, Request $request) {
            return '05'.$value;
        });

        $formDataMock = $this->bindMock(FormData::class);
        $formDataMock->shouldReceive('set')->with('phone', '0532')->once();

        $field->getFieldType()
              ->resolveDataFromRequest($formDataMock, $this->makePostRequest(['phone' => '32']));
    }
}