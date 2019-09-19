<?php

namespace Tests\Platform\Domains\Resource\Form\v2;

use Event;
use SuperV\Platform\Domains\Resource\Form\v2\Contracts\FormInterface;
use SuperV\Platform\Domains\Resource\Form\v2\Form;
use SuperV\Platform\Domains\Resource\Form\v2\FormFactory;
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
        $this->assertInstanceOf(FormInterface::class, $form);

        $this->assertFalse($form->isSubmitted());
        $this->assertFalse($form->isValid());
        $this->assertEquals('POST', $form->getMethod());
    }

    function test__sets_form_url_from_route_if_not_given()
    {
        $builder = FormFactory::createBuilder();
        $builder->setFormIdentifier($identifier = uuid());
        $form = $builder->getForm();

        $this->assertEquals(sv_route(Form::ROUTE, ['identifier' => $identifier]), $form->getUrl());
    }

    function test__set_form_mode_from_request()
    {
        $form = $this->makeForm();

        $form->handle($this->makeGetRequest());
        $this->assertEquals('GET', $form->getMethod());

        $form->handle($this->makePostRequest());
        $this->assertTrue($form->isMethod('POST'));
        $this->assertTrue($form->isMethod('post'));
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

    function __validates_form()
    {
        $form = $this->makeFormBuilder($this->makeTestFields())->getForm();
        $request = $this->makePostRequest(['name' => 'SuperV User']);

        $submitForm = $this->bindMock(SubmitForm::class);
        $submitForm->shouldReceive('handle')->with($form, $request)->once();

        $form->handle($request);
        $this->assertTrue($form->isSubmitted());
    }
}
