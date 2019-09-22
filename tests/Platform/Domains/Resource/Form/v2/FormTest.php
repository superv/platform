<?php

namespace Tests\Platform\Domains\Resource\Form\v2;

use Event;
use Mockery;
use SuperV\Platform\Domains\Resource\Form\v2\Contracts\FormInterface;
use SuperV\Platform\Domains\Resource\Form\v2\EntryRepositoryInterface;
use SuperV\Platform\Domains\Resource\Form\v2\Form;
use SuperV\Platform\Domains\Resource\Form\v2\FormFactory;
use SuperV\Platform\Domains\Resource\Form\v2\Jobs\ComposeForm;
use SuperV\Platform\Domains\Resource\Model\AnonymousModel;
use SuperV\Platform\Support\Composer\Payload;
use Tests\Platform\Domains\Resource\Form\v2\Helpers\FormFake;
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

    function test__resolves_GET_request()
    {
        $fooEntry = Mockery::mock((new AnonymousModel(['title' => 'foo-title'])))->makePartial();
        $barEntry = Mockery::mock((new AnonymousModel(['email' => 'bar-title'])))->makePartial();

        $request = $this->makeGetRequest(['entries' => ['ab.foo.1', 'xy.bar.2']]);

        $form = FormFake::fake(function (EntryRepositoryInterface $repository) use ($barEntry, $fooEntry) {
            $repository->shouldReceive('getEntry')->with('ab.foo', 1)->andReturn($fooEntry)->once();
            $repository->shouldReceive('getEntry')->with('xy.bar', 2)->andReturn($barEntry)->once();
        })->setFakeFields([
            'ab.foo.fields:title',
            'xy.bar.fields:email',
        ]);

        $form->handle($request);

        $this->assertEquals([
            'ab.foo.fields:title' => 'foo-title',
            'xy.bar.fields:email' => 'bar-title',
        ], $form->getData());

        $this->assertEquals(['ab.foo' => 1, 'xy.bar' => 2], $form->getEntryIds());
        $this->assertEquals('foo-title', $form->getFieldValue('ab.foo.fields:title'));
    }

    function test__resolves_POST_request()
    {
        $fooEntry = Mockery::mock((new AnonymousModel(['title' => 'foo-title'])))->makePartial();
        $fooEntry->shouldReceive('setAttribute')->with('title', 'updated-foo-title')->once();

        $barEntry = Mockery::mock((new AnonymousModel(['email' => 'bar-title'])))->makePartial();
        $barEntry->shouldReceive('setAttribute')->with('email', 'updated-bar-email')->once();

        $fooEntry->shouldReceive('save')->once();
        $barEntry->shouldReceive('save')->once();

        $request = $this->makePostRequest(
            ['entries' => ['ab.foo.1', 'xy.bar.2']],
            [
                'ab.foo.fields:title' => 'updated-foo-title',
                'xy.bar.fields:email' => 'updated-bar-email',
            ]);

        $formEntry = FormFake::fake(
            function (EntryRepositoryInterface $repository) use ($barEntry, $fooEntry) {
                $repository->shouldReceive('getEntry')->with('ab.foo', 1)->andReturn($fooEntry)->once();
                $repository->shouldReceive('getEntry')->with('xy.bar', 2)->andReturn($barEntry)->once();
            }
        )->setFakeFields([
            'ab.foo.fields:title',
            'xy.bar.fields:email',
        ])->createFormEntry();

        $form = FormFactory::createBuilder($formEntry)->getForm();

        $form->handle($request);
        $fooEntry->shouldHaveReceived('save')->once();
        $barEntry->shouldHaveReceived('save')->once();

        $this->assertEquals([
            'ab.foo.fields:title' => 'updated-foo-title',
            'xy.bar.fields:email' => 'updated-bar-email',
        ], $form->getData());
    }

    function test__dispatches_event_before_handling_POST_request()
    {
        $builder = $this->makeFormBuilder($this->makeTestFields());
        Event::fake($eventName = $builder->getFormIdentifier().'.events:submitting');
        $form = $builder->getForm();

        $form->handle($this->makePostRequest());
        Event::assertDispatched($eventName);

        // but not for GET
        $builder = $this->makeFormBuilder($this->makeTestFields());
        Event::fake($eventName = $builder->getFormIdentifier().'.events:submitting');
        $form = $builder->getForm();

        $form->handle($this->makeGetRequest());
        Event::assertNotDispatched($eventName);
    }

    function test__dispatches_event_after_handling_POST_request()
    {
        $builder = $this->makeFormBuilder($this->makeTestFields());
        Event::fake($eventName = $builder->getFormIdentifier().'.events:submitted');
        $form = $builder->getForm();

        $form->handle($this->makePostRequest());
        Event::assertDispatched($eventName);
        $this->assertTrue($form->isSubmitted());

        // but not for GET
        $builder = $this->makeFormBuilder($this->makeTestFields());
        Event::fake($eventName = $builder->getFormIdentifier().'.events:submitted');
        $form = $builder->getForm();
        $form->handle($this->makeGetRequest());
        Event::assertNotDispatched($eventName);
        $this->assertFalse($form->isSubmitted());
    }

    function test__composes_form()
    {
        $builder = $this->makeFormBuilder($this->makeTestFields());
        Event::fake($eventName = $builder->getFormIdentifier().'.events:composed');

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
}
