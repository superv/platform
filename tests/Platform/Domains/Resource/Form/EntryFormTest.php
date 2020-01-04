<?php

namespace Tests\Platform\Domains\Resource\Form;

use Illuminate\Support\Facades\Event;
use SuperV\Platform\Domains\Database\Model\Entry;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormFieldInterface;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface;
use SuperV\Platform\Domains\Resource\Form\Form;
use SuperV\Platform\Domains\Resource\Form\FormData;
use SuperV\Platform\Domains\Resource\Form\FormFactory;
use SuperV\Platform\Domains\Resource\ResourceConfig;
use SuperV\Platform\Testing\FormComponent;
use Tests\Platform\Domains\Resource\ResourceTestCase;

/**
 * Class EntryFormTest
 *
 * @package Tests\Platform\Domains\Resource\Form
 * @group   resource
 */
class EntryFormTest extends ResourceTestCase
{
    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $users;

    function test__config()
    {
        $entry = new FormTestUser(['name' => 'Omar', 'age' => 33]);

        $form = FormFactory::builderFromEntry($entry)->getForm();

        $this->assertInstanceOf(Form::class, $form);
        $this->assertEquals(3, $form->fields()->count());
        $this->assertEquals($entry, $form->getEntry());
    }

    function test__dispatches_event_when_resolved()
    {
        $this->withoutExceptionHandling();

        $eventName = 'testing.categories.forms:default.events:resolved';
        Event::fake($eventName);

        $this->blueprints()->categories();

        FormComponent::get('testing.categories.forms:default', $this);

        Event::assertDispatched($eventName,
            function ($eventName, $payload) {
                return $payload['form']->getIdentifier() === 'testing.categories.forms:default';
            });
    }

    function test_makes_update_form()
    {
        $entry = new FormTestUser(['name' => 'Omar', 'age' => 33]);
        $form = FormFactory::builderFromEntry($entry)->getForm();

        $this->assertEquals('Omar', $this->getComposedValue($form->getField('name'), $form));
        $this->assertEquals(33, $this->getComposedValue($form->getField('age'), $form));
    }

    function test__form_data_resolves_entry()
    {
        $categories = $this->blueprints()
                           ->categories(function (Blueprint $table) {
                               $table->boolean('active')->default(false);
                               $table->string('notes')->nullable();
                           });

        $category = $categories->create(['title' => 'Books', 'active' => 'TRUE', 'notes' => 'a-b-c']);
        $builder = FormFactory::builderFromEntry($category);

        $form = $builder->getForm();
        $form->fields()->hide('notes'); // XXX behh behhhh
        $form->resolve();

        $formData = $form->getData();
        $this->assertInstanceOf(FormData::class, $formData);
        $this->assertEquals(['title' => 'Books', 'active' => true], $formData->get());
    }

    function test__form_data_resolves_request()
    {
        $categories = $this->blueprints()
                           ->categories(function (Blueprint $table) {
                               $table->boolean('active')->default(false);
                               $table->string('notes')->nullable();
                           });

        $category = $categories->create(['title' => 'Books', 'active' => 'TRUE', 'notes' => 'a-b-c']);
        $builder = FormFactory::builderFromEntry($category);
        $builder->setRequest($this->makePostRequest(['title' => 'Updated Books', 'notes' => 'bad-value']));

        $form = $builder->getForm();
        $form->fields()->hide('notes');
        $form->resolve();

        $this->assertEquals(['title' => 'Updated Books', 'active' => true], $form->getData()->get());
    }

    function test__add_field()
    {
        $form = FormFactory::builderFromEntry($entry = new FormTestUser)->resolveForm();
        $this->assertEquals(3, $form->fields()->count());

        $form->fields()->addFieldFromArray(['type' => 'text', 'handle' => 'profession']);

        $field = $form->getField('profession');
        $this->assertNotNull($field);
        $this->assertInstanceOf(FormFieldInterface::class, $field);

        $this->assertTrue($field->isUnbound());

        $form->resolveRequest($this->makePostRequest(['profession' => 'developer']), $entry);
        $this->assertArrayNotHasKey('profession', $form->getData()->get());
        $this->assertArrayHasKey('profession', $form->getData()->getForValidation($entry));
    }

    function test__saves_form()
    {
        $entry = new FormTestUser();
        $form = FormFactory::builderFromEntry($entry)
                           ->setRequest($this->makePostRequest(['name' => 'Omar', 'age' => 33]))
                           ->resolveForm();

        $form->save();

        $this->assertEquals('Omar', $this->getComposedValue('name', $form));
        $this->assertEquals(33, $this->getComposedValue('age', $form));
    }

    function test__hidden_fields()
    {
        $entry = new FormTestUser();
        $form = FormFactory::builderFromEntry($entry)
                           ->setRequest($this->makePostRequest(['name' => 'Omar', 'age' => 99, 'phone' => '1-2-3']))
                           ->getForm();

        $form->getField('phone')->hide();
        $form->resolve()->save();

        $phoneField = $form->getField('phone');
        $this->assertTrue($phoneField->isHidden());

        $this->assertEquals('Omar', $this->getComposedValue('name', $form));
        $this->assertEquals(99, $this->getComposedValue('age', $form));

        $composedFields = $form->compose()->get('fields');
        $this->assertEquals(2, count($composedFields));
        $this->assertEquals(array_values($composedFields), $composedFields);
    }

    function test__saves_entry()
    {
        $entry = new FormTestUser;
        $form = FormFactory::builderFromEntry($entry)
                           ->setRequest($this->makePostRequest(['name' => 'Omar', 'age' => 33]))
                           ->getForm();

        $form->resolve()->save();

        $this->assertEquals('Omar', $this->getComposedValue('name', $form));
        $this->assertEquals(33, $this->getComposedValue('age', $form));

        $user = $form->getEntry();
        $this->assertEquals('Omar', $user->name);
        $this->assertEquals(33, $user->age);
        $this->assertTrue($user->wasRecentlyCreated());
    }

    /**
     * @group http
     */
    function test__resource_create_over_http()
    {
        $this->withoutExceptionHandling();

        $response = $this->getJsonUser($this->users->router()->createForm());
        $response->assertOk();

        $form = $this->getUserPage($this->users->router()->createForm());

        $this->assertEquals(['identifier',
                             'url',
                             'method',
                             'fields',
                             'actions'], array_keys($form->getProps()->compose()));
        $this->assertEquals(3, $form->countProp('fields'));

        $response = $this->postJsonUser($form->getProp('url'), [
            'name' => 'Omar',
            'age'  => 33,
        ]);
        $response->assertOk();

        $user = $this->users->first();
        $this->assertEquals('Omar', $user->name);
        $this->assertEquals(33, $user->age);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->be($this->newUser());

        $this->users = $this->create('tbl_users',
            function (Blueprint $table, ResourceConfig $config) {
                $config->setIdentifier('testing.users');
                $table->increments('id');
                $table->string('name');
                $table->unsignedInteger('age');
                $table->string('phone')->nullable();
            }
        );
    }

    protected function getComposedValue($field, FormInterface $form)
    {
        if (is_string($field)) {
            $field = $form->getField($field);
        }

        return $field->getComposer()->toForm($form)->get('value');
    }
}

class FormTestUser extends Entry
{
    public $timestamps = false;

    protected $table = 'tbl_users';

    protected $guarded = [];

    public function getId()
    {
        return $this->getKey();
    }

    public function wasRecentlyCreated(): bool
    {
        return $this->wasRecentlyCreated;
    }
}
