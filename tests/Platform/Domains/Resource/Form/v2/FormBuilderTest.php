<?php

namespace Tests\Platform\Domains\Resource\Form\v2;

use SuperV\Platform\Domains\Resource\Form\FormModel;
use SuperV\Platform\Domains\Resource\Form\v2\Contracts\FormInterface;
use SuperV\Platform\Domains\Resource\Form\v2\Factory;
use SuperV\Platform\Domains\Resource\Form\v2\FormFieldCollection;
use Tests\Platform\Domains\Resource\Form\v2\Helpers\FormFieldFake;
use Tests\Platform\Domains\Resource\Form\v2\Helpers\FormTestHelpers;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class FormBuilderTest extends ResourceTestCase
{
    use FormTestHelpers;

    function test__builder_fields()
    {
        $builder = $this->makeFormBuilder($this->makeTestFields());
        $form = $builder->getForm();

        $this->assertInstanceOf(FormFieldCollection::class, $form->getFields());
        $this->assertEquals(2, $form->getFields()->count());

        $this->assertEquals('name', $form->getField('users.name')->getName());
        $this->assertEquals('email', $form->getField('users.email')->getName());
    }

    function test__resolves_field_values_from_initial_form_data()
    {
        $form = $this->makeFormBuilder($this->makeTestFields())
                     ->setFormData(['users.name' => 'SuperV User'])
                     ->getForm();

        $this->assertEquals('SuperV User', $form->getFieldValue('users.name'));
    }

    function test__build_standard_form()
    {
        $formFields = $this->bindMock(FormFieldCollection::class);

        $builder = Factory::createBuilder();
        $this->assertInstanceOf(\SuperV\Platform\Domains\Resource\Form\v2\FormBuilder::class, $builder);

        $builder->setFormIdentifier('test-form-id');
        $builder->setFormUrl('url-to-form');
        $builder->setFormData([1, 2, 3]);

        $formMock = $this->bindMock(FormInterface::class);
        $formMock->shouldReceive('setIdentifier')->with('test-form-id')->once()->andReturnSelf();
        $formMock->shouldReceive('setUrl')->with('url-to-form')->once()->andReturnSelf();
        $formMock->shouldReceive('setFields')->with($formFields)->once()->andReturnSelf();
        $formMock->shouldReceive('setData')->with([1, 2, 3])->once()->andReturnSelf();

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

    function test__removes_hidden_fields()
    {
        $builder = Factory::createBuilder();
        $fakeField = \Mockery::mock(FormFieldFake::fake())->makePartial();
        $fakeField->shouldReceive('isHidden')->andReturn(true)->once();

        $builder->addField($fakeField);
    }
}
