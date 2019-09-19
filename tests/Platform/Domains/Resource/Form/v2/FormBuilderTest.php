<?php

namespace Tests\Platform\Domains\Resource\Form\v2;

use SuperV\Platform\Domains\Resource\Form\v2\Contracts\FormBuilderInterface;
use SuperV\Platform\Domains\Resource\Form\v2\Contracts\FormInterface;
use SuperV\Platform\Domains\Resource\Form\v2\FormFactory;
use SuperV\Platform\Domains\Resource\Form\v2\FormFieldCollection;
use Tests\Platform\Domains\Resource\Form\v2\Helpers\FormFake;
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
        $this->assertEquals('name', $form->getField('sv.users.fields:name')->getName());
        $this->assertEquals('email', $form->getField('sv.users.fields:email')->getName());

        $builder = $this->makeFormBuilder();
        $builder->addFields($this->makeTestFields());
        $this->assertEquals(2, $builder->getForm()->getFields()->count());
    }

    function test__resolves_field_values_from_initial_form_data()
    {
        $form = $this->makeFormBuilder($this->makeTestFields())
                     ->setFormData(['fields' => ['sv.users.fields:name' => 'SuperV User']])
                     ->getForm();

        $this->assertEquals('SuperV User', $form->getFieldValue('sv.users.fields:name'));
    }

    function test__build_standard_form()
    {
        $formFields = $this->bindMock(FormFieldCollection::class);
        $formData = ['fields' => [1, 2, 3]];
        $formFields->shouldReceive('fill')->with([1, 2, 3])->once();

        $builder = FormFactory::createBuilder();
        $this->assertInstanceOf(FormBuilderInterface::class, $builder);

        $builder->setFormIdentifier('test-form-id');
        $builder->setFormUrl('url-to-form');
        $builder->setFormData($formData);

        $formMock = $this->bindMock(FormInterface::class);
        $formMock->shouldReceive('setIdentifier')->with('test-form-id')->once()->andReturnSelf();
        $formMock->shouldReceive('setUrl')->with('url-to-form')->once()->andReturnSelf();
        $formMock->shouldReceive('setFields')->with($formFields)->once()->andReturnSelf();
        $formMock->shouldReceive('setData')->with($formData)->once()->andReturnSelf();

        $builder->build();
    }

    function test__build_from_form_entry()
    {
        $formEntry = FormFake::fake()
                             ->setFakeFields(['sv.users.fields:title', 'sv.users.fields:location'])
                             ->createFormEntry();

        $builder = FormFactory::createBuilder($formEntry);

        $form = $builder->getForm();

        $this->assertEquals($formEntry->getIdentifier(), $form->getIdentifier());
        $this->assertEquals(sv_route('sv::forms.show', ['identifier' => $form->getIdentifier()]), $form->getUrl());

        $this->assertTrue($formEntry->getFormFields()->count() > 0);
        $this->assertEquals($formEntry->getFormFields()->count(), $form->getFields()->count());

        $field = $form->getFields()->first();
        $this->assertEquals('title', $field->getName());
    }

    function test__removes_hidden_fields()
    {
        $builder = FormFactory::createBuilder();
        $fakeField = \Mockery::mock(FormFieldFake::fake())->makePartial();
        $fakeField->shouldReceive('isHidden')->andReturn(true)->once();

        $builder->addField($fakeField);
    }
}
