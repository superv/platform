<?php

namespace Tests\Platform\Domains\Resource\Form\v2;

use Event;
use SuperV\Platform\Domains\Resource\Form\v2\Contracts\Form;
use SuperV\Platform\Domains\Resource\Form\v2\Factory;
use SuperV\Platform\Domains\Resource\Form\v2\FormFieldCollection;
use SuperV\Platform\Domains\Resource\Form\v2\Jobs\ComposeForm;
use SuperV\Platform\Domains\Resource\Form\v2\Jobs\SubmitForm;
use SuperV\Platform\Support\Composer\Payload;
use Tests\Platform\Domains\Resource\Form\v2\Helpers\FormTestHelpers;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class FormTest extends ResourceTestCase
{
    use FormTestHelpers;

    function test__initial_state()
    {
        $form = $this->makeFormBuilder()->getForm();
        $this->assertInstanceOf(Form::class, $form);

        $this->assertFalse($form->isSubmitted());
        $this->assertFalse($form->isValid());
        $this->assertEquals('POST', $form->getMethod());
    }

    function test__set_form_mode_from_request()
    {
        $builder = Factory::createBuilder();
        $builder->setFormIdentifier($identifier = uuid());
        $form = $builder->getForm();

        $form->handle($this->makeGetRequest());
        $this->assertEquals('GET', $form->getMethod());

        $form->handle($this->makePostRequest());
        $this->assertEquals('POST', $form->getMethod());
    }

    function test__builder_fields()
    {
        $builder = $this->makeFormBuilder($this->makeTestFields());
        $form = $builder->getForm();

        $this->assertInstanceOf(FormFieldCollection::class, $form->getFields());
        $this->assertEquals(2, $form->getFields()->count());

        $this->assertEquals('name', $form->getField('name')->getName());
        $this->assertEquals('email', $form->getField('email')->getName());
    }

    function test__resolves_field_values_from_initial_form_data()
    {
        $form = $this->makeFormBuilder($this->makeTestFields())
                     ->setFormData(['name' => 'SuperV User'])
                     ->getForm();

        $this->assertEquals('SuperV User', $form->getFieldValue('name'));
    }

    function test__dispatches_event_before_handling_request()
    {
        $builder = $this->makeFormBuilder($this->makeTestFields());

        Event::fake($eventName = $builder->getFormIdentifier().'.handling');
        $form = $builder->getForm();

        $form->handle($this->makeGetRequest());
        Event::assertDispatched($eventName);
    }

    function test__composes_form()
    {
        $builder = $this->makeFormBuilder($this->makeTestFields());
        Event::fake($eventName = $builder->getFormIdentifier().'.composed');

        $form = $builder->getForm();
        $payload = new Payload(['pay-load']);

        $composer = $this->bindMock(ComposeForm::class);
        $composer->shouldReceive('handle')->with($form)->once()->andReturn($payload);

        $form->compose();
        Event::assertDispatched($eventName, function ($eventName, $data) {
            $payload = $data['payload'];
            $this->assertInstanceOf(Payload::class, $payload);

            return $payload->get() === ['pay-load'];
        });

        $component = $form->render();
        $this->assertEquals($payload->get(), $component->getProps()->compose());
    }

    function test__submits_form()
    {
        $form = $this->makeFormBuilder($this->makeTestFields())->getForm();

        $data = ['name' => 'SuperV User'];

        $submitForm = $this->bindMock(SubmitForm::class);
        $submitForm->shouldReceive('handle')->with($form, $data)->once();

        $form->submit($data);
        $this->assertTrue($form->isSubmitted());
    }

    function test__validates_form()
    {
        $form = $this->makeFormBuilder($this->makeTestFields())->getForm();
        $request = $this->makePostRequest(['name' => 'SuperV User']);

        $submitForm = $this->bindMock(SubmitForm::class);
        $submitForm->shouldReceive('handle')->with($form, $request)->once();

        $form->handle($request);
        $this->assertTrue($form->isSubmitted());
    }


}
