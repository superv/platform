<?php

namespace Tests\Platform\Domains\Resource\Form;

use SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldTypeInterface as FieldType;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface as Form;
use SuperV\Platform\Domains\Resource\Form\FormFactory;
use SuperV\Platform\Domains\Resource\Form\FormField;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class FormFieldsTest extends ResourceTestCase
{
    protected $appliedCallbacks = [];

    function test__receives_saving_event_before_form_is_saved()
    {
        $form = FormFactory::builder()->setIdentifier('the-form')->getForm();

        $fieldTypeMock = $this->bindMock(FieldType::class);
        $fieldTypeMock->shouldReceive('saving')->with($form)->once();

        $fieldMock = $this->bindPartialMock(
            FieldInterface::class,
            FormField::make(['type' => 'text', 'name' => 'phone'])
        );
        $form->fields()->addField($fieldMock);

        $fieldMock->shouldReceive('getFieldType')->andReturn($fieldTypeMock)->once();
        $fieldMock->shouldReceive('getCallback')
                  ->with('before_saving')
                  ->andReturn(function (Form $form, FieldType $fieldType) {
                      $this->appliedCallbacks['before_saving'] = compact('form', 'fieldType');
                  })
                  ->once();

        $form->resolve()->fireEvent('saving');

        $this->assertArrayHasKey('before_saving', $this->appliedCallbacks);
        $this->assertSame($form, $this->appliedCallbacks['before_saving']['form']);
        $this->assertSame($fieldTypeMock, $this->appliedCallbacks['before_saving']['fieldType']);
    }
}