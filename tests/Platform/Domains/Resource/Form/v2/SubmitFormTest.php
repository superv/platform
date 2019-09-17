<?php

namespace Tests\Platform\Domains\Resource\Form\v2;

use SuperV\Platform\Domains\Resource\Form\Contracts\FormField as FormFieldContract;
use SuperV\Platform\Domains\Resource\Form\v2\Contracts\Form;
use SuperV\Platform\Domains\Resource\Form\v2\Jobs\SubmitForm;
use Tests\Platform\Domains\Resource\Form\v2\Helpers\FormTestHelpers;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class SubmitFormTest extends ResourceTestCase
{
    use FormTestHelpers;

    function test__submit_data_array()
    {
        $form = $this->bindMock(Form::class);
        $form->shouldReceive('setFieldValue')->with('field-1', 'value-1')->once()->andReturnSelf();
        $form->shouldReceive('setFieldValue')->with('field-2', 'value-2')->once()->andReturnSelf();

        SubmitForm::resolve()->handle($form, ['field-1' => 'value-1', 'field-2' => 'value-2']);
    }

    function __submit_data_array()
    {
        $fieldA = $this->bindMock(FormFieldContract::class);
        $fieldA->shouldReceive('setValue')->with('value-1')->once();

        $fieldB = $this->bindMock(FormFieldContract::class);
        $fieldB->shouldReceive('setValue')->with('value-2')->once();

        $form = $this->bindMock(Form::class);
        $form->shouldReceive('getField')->with('field-1')->once()->andReturn($fieldA);
        $form->shouldReceive('getField')->with('field-2')->once()->andReturn($fieldB);

        SubmitForm::resolve()->handle($form, ['field-1' => 'value-1', 'field-2' => 'value-2']);
    }
}



