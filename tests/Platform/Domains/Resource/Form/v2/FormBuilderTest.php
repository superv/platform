<?php

namespace Tests\Platform\Domains\Resource\Form\v2;

use SuperV\Platform\Domains\Resource\Form\FormModel;
use SuperV\Platform\Domains\Resource\Form\v2\Contracts\Form;
use SuperV\Platform\Domains\Resource\Form\v2\Factory;
use SuperV\Platform\Domains\Resource\Form\v2\FormFieldCollection;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class FormBuilderTest extends ResourceTestCase
{
    function test__removes_hidden_fields()
    {
        $builder = Factory::createBuilder();
        $fakeField = \Mockery::mock(FormFieldFake::fake())->makePartial();
        $fakeField->shouldReceive('isHidden')->andReturn(true)->once();

        $builder->addField($fakeField);
    }
    function test__build_standard_form()
    {
        $formFields = $this->bindMock(FormFieldCollection::class);

        $builder = Factory::createBuilder();
        $this->assertInstanceOf(\SuperV\Platform\Domains\Resource\Form\v2\FormBuilder::class, $builder);

        $builder->setFormIdentifier('test-form-id');
        $builder->setFormUrl('url-to-form');

        $formMock = $this->bindMock(Form::class);
        $formMock->shouldReceive('setIdentifier')->with('test-form-id')->once()->andReturnSelf();
        $formMock->shouldReceive('setUrl')->with('url-to-form')->once()->andReturnSelf();
        $formMock->shouldReceive('setFields')->with($formFields)->once()->andReturnSelf();

        $builder->build();
    }

    function test__build_from_form_entry()
    {
        $identifier = 'platform::sv_addons::forms.default';
        $formEntry = FormModel::withIdentifier($identifier);

        $builder = Factory::createBuilder();
        $builder->setFormEntry($formEntry);

        $form = $builder->getForm();

        $this->assertEquals($identifier, $form->getIdentifier());
        $this->assertEquals(sv_route('sv::forms.show', ['identifier' => $identifier]), $form->getUrl());
        $this->assertEquals($formEntry->getFormFields()->count(), $form->getFields()->count());
    }
}
