<?php

namespace Tests\Platform\Domains\Resource\Form\v2;

use Event;
use Mockery;
use SuperV\Platform\Contracts\Arrayable;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Form\FormModel;
use SuperV\Platform\Domains\Resource\Form\v2\Contracts\FormInterface;
use SuperV\Platform\Domains\Resource\Form\v2\EntryRepositoryInterface;
use SuperV\Platform\Domains\Resource\Form\v2\Form;
use SuperV\Platform\Domains\Resource\Form\v2\FormFactory;
use SuperV\Platform\Domains\Resource\Form\v2\FormFieldComposer;
use SuperV\Platform\Domains\Resource\Form\v2\FormResponse;
use SuperV\Platform\Domains\Resource\Form\v2\Jobs\ComposeForm;
use SuperV\Platform\Domains\Resource\Form\v2\Jobs\ValidateForm;
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

    function test__resolves_create_form_data_from_GET_request()
    {
        $form = $this->setupForm();

        $this->assertEquals([
            'ab.crm_tickets' => ['title'],
            'xy.clients'     => ['email', 'phone'],
        ], $form->getFields()->getIdentifierMap()->all());

        $form->handle($this->makeGetRequest());

        $this->assertEquals([], $form->getData());
    }

    function test__resolves_create_form_data_from_POST_request()
    {
        $request = $this->makePostRequest([
            'ab__crm_tickets__title' => 'new-ticket-title',
            'xy__clients__email'     => 'new-client-email',
            'xy__clients__phone'     => 'new-client-phone',
        ]);

        $form = $this->setupForm();

        $form->handle($request);

        $this->assertEquals([
            'ab.crm_tickets' => [
                'title' => 'new-ticket-title',
            ],
            'xy.clients'     => [
                'email' => 'new-client-email',
                'phone' => 'new-client-phone',
            ],
        ], $form->getData());
    }

    function test__resolves_update_form_data_from_GET_requestt()
    {
        $this->app->bind(EntryRepositoryInterface::class, FakeEntryRepository::class);

        $request = $this->makeGetRequest(['entries' => ['ab.crm_tickets:12', 'xy.clients:34']]);

        $form = $this->setupForm();

        $form->handle($request);

        $this->assertEquals([
            'ab.crm_tickets' => [
                'title' => 'ticket-title',
            ],
            'xy.clients'     => [
                'email' => 'client-email',
                'phone' => 'client-phone',
            ],
        ], $form->getData());

        $form->render();
    }

    function test__update_form_handle_POST_request()
    {
        $this->app->bind(EntryRepositoryInterface::class, FakeEntryRepository::class);

        $request = $this->makePostRequest(
            ['entries' => ['ab.crm_tickets:12', 'xy.clients:34']],
            [
                'ab.crm_tickets.title' => 'updated-ticket-title',
                'xy__clients__email'   => 'updated-client-email',
            ]);

        $form = $this->setupForm()->handle($request);

        $this->assertEquals([
            'ab.crm_tickets' => [
                'title' => 'updated-ticket-title',
            ],
            'xy.clients'     => [
                'email' => 'updated-client-email',
            ],
        ], $form->getData());

        $form->render();
    }

    function test__submits_create_form()
    {
        $repoMock = $this->bindMock(EntryRepositoryInterface::class);

        $repoMock->shouldReceive('create')
                 ->with('ab.crm_tickets', [
                     'title' => 'new-ticket-title',
                 ])->once();
        $repoMock->shouldReceive('create')
                 ->with('xy.clients', [
                     'email' => 'new-client-email',
                     'phone' => 'new-client-phone',
                 ])->once();

        $form = $this->setupForm();

        $form->setData([
            'ab.crm_tickets' => [
                'title' => 'new-ticket-title',
            ],
            'xy.clients'     => [
                'email' => 'new-client-email',
                'phone' => 'new-client-phone',
            ],
        ]);

        $form->submit();
    }

    function test__submits_update_form()
    {
        $repoMock = $this->bindMock(EntryRepositoryInterface::class);
        $repoMock->shouldReceive('update')->with('ab.crm_tickets:12', ['title' => 'updated-ticket-title'])->once();
        $repoMock->shouldReceive('update')->with('xy.clients:34', ['email' => 'updated-client-email'])->once();
        $repoMock->shouldReceive('getEntry'); // FormResponse 🤬

        $request = $this->makePostRequest(['entries' => ['ab.crm_tickets:12', 'xy.clients:34']],
            [
                'ab__crm_tickets__title' => 'updated-ticket-title',
                'xy__clients__email'     => 'updated-client-email',
            ]);

        $validatorMock = $this->bindMock(ValidateForm::class);
        $form = $this->setupForm();
        $form->handle($request);

        $validatorMock->shouldReceive('validate')->with($form)->once();
        $form->submit();
    }

    function test__submit_response()
    {
        $categories = $this->blueprints()->categories();

        $form = FormFactory::createBuilder(FormModel::withIdentifier($categories->getIdentifier().'.forms:default'))->getForm();

        $form->handle($this->makePostRequest(['testing__categories__title' => 'category-title',
                                              '__form_action'              => 'the-action']));

        $form->submit();

        $response = $form->getResponse();

        $this->assertInstanceOf(FormResponse::class, $response);
        $this->assertInstanceOf(Arrayable::class, $response);

        $responseArray = $response->get();
        $this->assertEquals(['message', 'action', 'redirect_to'], array_keys($responseArray));

        $this->assertEquals($categories->router()->dashboardSPA(), $responseArray['redirect_to']);
    }

    function __dispatches_event_before_handling_POST_request()
    {
        $builder = $this->makeFormBuilder($this->makeTestFields());
        Event::fake($eventName = $builder->getFormIdentifier().'.events:submitting');
        $form = $builder->getForm();

        $form->submit();
        Event::assertDispatched($eventName);

        // but not for GET
        $builder = $this->makeFormBuilder($this->makeTestFields());
        Event::fake($eventName = $builder->getFormIdentifier().'.events:submitting');
        $form = $builder->getForm();

        $form->handle($this->makeGetRequest());
        Event::assertNotDispatched($eventName);
    }

    function __dispatches_event_after_handling_POST_request()
    {
        $builder = $this->makeFormBuilder($this->makeTestFields());
        Event::fake($eventName = $builder->getFormIdentifier().'.events:submitted');
        $form = $builder->getForm();

//        $form->handle($this->makePostRequest());
        $form->submit();
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

    function test__field_composer_form_data()
    {
        $this->app->bind(EntryRepositoryInterface::class, FakeEntryRepository::class);

        $form = $this->setupForm();

        $form->handle($this->makeGetRequest(['entries' => ['ab.crm_tickets:12', 'xy.clients:34']]));

        $composite = (new FormFieldComposer())->toForm($form, $form->getField('ab.crm_tickets.title'));

        $this->assertArrayHasKey('value', $composite);

        $this->assertEquals('ticket-title', $composite['value']);
    }

    protected function setupForm($fields = null): FormFake
    {
        $fields = $fields ?? [
                'ab.crm_tickets.fields:title',
                'xy.clients.fields:email',
                'xy.clients.fields:phone',
            ];

        $formEntry = FormFake::fake()
                             ->setFakeFields($fields)->createFormEntry();

        $form = FormFactory::createBuilder($formEntry)->getForm();

        return $form;
    }
}

class FakeEntryRepository implements EntryRepositoryInterface
{
    public $mocks = [];

    public function __construct()
    {
        $this->mocks = [
            'ab.crm_tickets' => [
                12 => Mockery::mock((new AnonymousModel([
                    'id'    => 12,
                    'title' => 'ticket-title',
                ])))->makePartial(),
                56 => Mockery::mock((new AnonymousModel([
                    'id'    => 56,
                    'title' => 'order-B-title',
                ])))->makePartial(),
            ],
            'xy.clients'     => [
                34 => Mockery::mock((new AnonymousModel([
                    'id'    => 34,
                    'email' => 'client-email',
                    'phone' => 'client-phone',
                ])))->makePartial(),

            ],
        ];
    }

    public function getEntry(string $identifier, int $id = null): ?EntryContract
    {
        if (is_null($id)) {
            [$identifier, $id] = explode(':', $identifier);
        }

        return $this->mocks[$identifier][$id];
    }

    public function create(string $identifier, array $attributes = [])
    {
    }

    public function update(string $identifier, array $attributes = [])
    {
    }

    public function newEntryInstance(string $identifier): EntryContract
    {
    }

    /** * @return static */
    public static function resolve()
    {
        return app(static::class);
    }
}
