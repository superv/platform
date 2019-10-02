<?php

namespace Tests\Platform\Domains\Resource\Form;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use SuperV\Platform\Domains\Database\Model\Entry;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Media\Media;
use SuperV\Platform\Domains\Resource\Field\FieldComposer;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Form\Form;
use SuperV\Platform\Domains\Resource\Form\FormField;
use SuperV\Platform\Domains\Resource\Form\ResourceFormBuilder;
use SuperV\Platform\Domains\Resource\ResourceConfig;
use SuperV\Platform\Testing\FormComponent;
use Tests\Platform\Domains\Resource\ResourceTestCase;

/**
 * Class FormTest
 *
 * @package Tests\Platform\Domains\Resource\Form
 * @group   resource
 */
class FormTest extends ResourceTestCase
{
    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $users;

    function test__config()
    {
        $fields = $this->makeFields();
        $watcher = new FormTestUser(['name' => 'Omar', 'age' => 33]);

        $form = ResourceFormBuilder::buildFromEntry($watcher);
        $form = $form->setFields($fields)->make()->hideField('age');

        $this->assertInstanceOf(Form::class, $form);
        $this->assertEquals(2, $form->getFields()->count());
        $this->assertEquals($watcher, $form->getEntry());
        $this->assertEquals(['age'], $form->getHiddenFields());
    }

    function test__dispatches_event_when_resolved()
    {
        $this->withoutExceptionHandling();

        $eventName = 'testing.categories.forms:default.events:resolved';
        Event::fake($eventName);

        $this->blueprints()->categories();

        FormComponent::get('testing.categories.forms:default', $this);

        Event::assertDispatched($eventName, function ($eventName, Form $form) {
            return $form->getIdentifier() === 'testing.categories.forms:default';
        });
    }

    function test_makes_update_form()
    {
        $testUser = new FormTestUser(['name' => 'Omar', 'age' => 33]);
        $fields = $this->makeFields();
        $form = ResourceFormBuilder::buildFromEntry($testUser);
        $form->setFields($fields)->make();

        $this->assertEquals('Omar', $this->getComposedValue($form->getField('name'), $form));
        $this->assertEquals(33, $this->getComposedValue($form->getField('age'), $form));
    }

    function test__add_field()
    {
        $form = ResourceFormBuilder::buildFromEntry($testUser = new FormTestUser);
        $this->assertEquals(2, $form->getFields()->count());

        $form->addField($this->makeField(['type' => 'text', 'name' => 'profession']));

        $field = $form->getField('profession');
        $this->assertNotNull($field);
        $this->assertInstanceOf(\SuperV\Platform\Domains\Resource\Form\Contracts\FormField::class, $field);
    }

    function test__saves_form()
    {
        $testUser = new FormTestUser(['name' => 'Omar', 'age' => 33]);
        $fields = $this->makeFields();
        $form = ResourceFormBuilder::buildFromEntry($testUser);
        $form->setFields($fields)->make();
        $form->setRequest($this->makePostRequest(['name' => 'Omar', 'age' => 33]))
             ->save();

        $this->assertEquals('Omar', $form->composeField('name', $form)->get('value'));
        $this->assertEquals(33, $form->composeField('age', $form)->get('value'));
    }

    function test__hidden_fields()
    {
        $testUser = new FormTestUser(['name' => 'Omar', 'age' => 33]);
        $fields = $this->makeFields();
        $form = ResourceFormBuilder::buildFromEntry($testUser);
        $form->setFields($fields)->make();

        $form->hideField('name')
             ->make()
             ->setRequest($this->makePostRequest(['name' => 'Omar', 'age' => 99]))
             ->save();

        $nameField = $form->getField('name');
        $this->assertTrue($nameField->isHidden());

        $this->assertEquals('Omar', $form->composeField('name', $form)->get('value'));
        $this->assertEquals(99, $form->composeField('age', $form)->get('value'));

        $composedFields = $form->compose()->get('fields');
        $this->assertEquals(1, count($composedFields));
        $this->assertEquals(array_values($composedFields), $composedFields);
    }

    function test__saves_entry()
    {
        $testUser = new FormTestUser;
        $fields = $this->makeFields();
        $form = ResourceFormBuilder::buildFromEntry($testUser);
        $form->setFields($fields)->make();
        $form->setRequest($this->makePostRequest(['name' => 'Omar', 'age' => 33]))
             ->save();

        $this->assertEquals('Omar', $form->composeField('name', $form)->get('value'));
        $this->assertEquals(33, $form->composeField('age', $form)->get('value'));

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

//        $createPage = HelperComponent::from($response->decodeResponseJson('data'));
//        $formBlock = HelperComponent::from($createPage->getProp('blocks.0'));
        $form = $this->getUserPage($this->users->router()->createForm());

        $this->assertEquals(['identifier',
                             'url',
                             'method',
                             'fields',
                             'actions'], array_keys($form->getProps()->compose()));
        $this->assertEquals(2, $form->countProp('fields'));

        $response = $this->postJsonUser($form->getProp('url'), [
            'name' => 'Omar',
            'age'  => 33,
        ]);
        $response->assertOk();

        $user = $this->users->first();
        $this->assertEquals('Omar', $user->name);
        $this->assertEquals(33, $user->age);
    }

    function test__save_handles_fields_mutating_callbacks()
    {
        $fields = [
            $textField = $this->makeField(['name' => 'name', 'type' => 'text']),
            $ageField = $this->makeField(['name' => 'age', 'type' => 'number']),
            $fileField = $this->makeField(['name' => 'avatar', 'type' => 'file']),
        ];

        $file = new UploadedFile($this->basePath('__fixtures__/square.png'), 'square.png');
        $form = ResourceFormBuilder::buildFromEntry($testUser = new FormTestUser);
        $form->setFields($fields)->make();

        $form->setRequest($this->makePostRequest(['name'   => 'Omar',
                                                  'age'    => 33,
                                                  'avatar' => $file]));

        $form->getField('avatar');
        $form->save();
        $this->assertNull($testUser->avatar);

        $this->assertEquals(1, Media::count());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->users = $this->create('tbl_users', function (Blueprint $table, ResourceConfig $config) {
            $config->setIdentifier('testing.users');
            $table->increments('id');
            $table->string('name');
            $table->unsignedInteger('age');
        });
    }

    protected function getComposedValue($field, $form = null)
    {
        return (new FieldComposer($field))->forForm($form)->get('value');
    }

    protected function makeFields(): array
    {
        return [
            $this->makeField(['name' => 'name', 'type' => 'text']),
            $this->makeField(['name' => 'age', 'type' => 'number']),
        ];
    }

    protected function makeField(array $params): \SuperV\Platform\Domains\Resource\Form\Contracts\FormField
    {
        if (! isset($params['identifier'])) {
            $params['identifier'] = uuid();
        }

        return FieldFactory::createFromArray($params, FormField::class);
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
