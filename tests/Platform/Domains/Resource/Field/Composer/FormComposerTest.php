<?php

namespace Tests\Platform\Domains\Resource\Field\Composer;

use SuperV\Platform\Domains\Resource\Field\Composer\FormComposer;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface;
use SuperV\Platform\Support\Composer\Payload;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class FormComposerTest extends ResourceTestCase
{
    function test__payload_without_form_object()
    {
        $fieldMock = $this->bindMock(FieldInterface::class);
        $fieldMock->expects('getIdentifier')->andReturns('__identifier');
        $fieldMock->expects('getHandle')->andReturns('__handle');
        $fieldMock->expects('getType')->andReturns('__type');
        $fieldMock->expects('getComponent')->andReturns('__component');
        $fieldMock->expects('getLabel')->andReturns('__label');
        $fieldMock->expects('getValue')->andReturns('__value');
        $fieldMock->expects('getPlaceholder')->andReturns('__placeholder');
        $fieldMock->expects('getConfigValue')->with('hint')->andReturns('__hint');
        $fieldMock->expects('getConfigValue')->with('meta')->andReturns('__meta');
        $fieldMock->expects('getConfigValue')->with('presenting')->andReturns('__presenting');

        $formComposer = FormComposer::resolve();
        $formComposer->setField($fieldMock);

        $payload = $formComposer->compose();
        $this->assertInstanceOf(Payload::class, $payload);
        $this->assertEquals('__identifier', $payload->get('identifier'));
        $this->assertEquals('__handle', $payload->get('handle'));
        $this->assertEquals('__type', $payload->get('type'));
        $this->assertEquals('__component', $payload->get('component'));
        $this->assertEquals('__label', $payload->get('label'));
        $this->assertEquals('__value', $payload->get('value'));
        $this->assertEquals('__placeholder', $payload->get('placeholder'));
        $this->assertEquals('__hint', $payload->get('hint'));
        $this->assertEquals('__meta', $payload->get('meta'));
        $this->assertEquals('__presenting', $payload->get('presenting'));
    }
}