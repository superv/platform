<?php

namespace Tests\Platform\Domains\Resource\Form;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Database\Model\Contracts\Watcher;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Media\Media;
use SuperV\Platform\Domains\Resource\Field\Contracts\Field;
use SuperV\Platform\Domains\Resource\Field\FieldComposer;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Field\Types\FieldType;
use SuperV\Platform\Domains\Resource\Form\Form;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class FormTest extends ResourceTestCase
{
    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $users;

    protected function setUp()
    {
        parent::setUp();

        $this->users = $this->create('t_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->unsignedInteger('age');
        });
    }

    function test__config()
    {
        $fields = $this->makeFields();
        $watcher = new FormTestUser(['name' => 'Omar', 'age' => 33]);
////
////        $config = FormConfig::make()
////                            ->addGroup($fields, $watcher, 'default')
//                            ->hideField('age');

        $form = Form::for($watcher)->setFields($fields)->hideField('age')->make();

        $this->assertInstanceOf(Form::class, $form);
        $this->assertEquals(2, $form->getFields()->count());
        $this->assertEquals($watcher, $form->getEntry());
        $this->assertEquals(['age'], $form->getHiddenFields());
    }

    function test_makes_update_form()
    {
        $testUser = new FormTestUser(['name' => 'Omar', 'age' => 33]);
        $fields = $this->makeFields();

        $form = Form::for($testUser, $fields)->make();

        $this->assertEquals('Omar', $this->getComposedValue($form->getField('name'), $form));
        $this->assertEquals(33, $this->getComposedValue($form->getField('age'), $form));
    }

    protected function getComposedValue($field, $form = null)
    {
        return (new FieldComposer($field))->forForm($form)->get('value');
    }

    function test__saves_form()
    {
        $testUser = new FormTestUser(['name' => 'Omar', 'age' => 33]);
        $fields = $this->makeFields();

        $form = Form::for($testUser, $fields)
                    ->make()
                    ->setRequest($this->makePostRequest(['name' => 'Omar', 'age' => 33]))
                    ->save();

        $this->assertEquals('Omar', $form->composeField('name', $form)->get('value'));
        $this->assertEquals(33, $form->composeField('age', $form)->get('value'));
    }

    function test__hidden_fields()
    {
        $testUser = new FormTestUser(['name' => 'Omar', 'age' => 33]);
        $fields = $this->makeFields();

        $form = Form::for($testUser, $fields)
                    ->hideField('name')
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

        $form = Form::for($testUser, $fields)
                    ->make()
                    ->setRequest($this->makePostRequest(['name' => 'Omar', 'age' => 33]))
                    ->save();

        $this->assertEquals('Omar', $form->composeField('name', $form)->get('value'));
        $this->assertEquals(33, $form->composeField('age', $form)->get('value'));

        $user = $form->getEntry();
        $this->assertEquals('Omar', $user->name);
        $this->assertEquals(33, $user->age);
        $this->assertTrue($user->wasRecentlyCreated());
    }

    function test__resource_create_over_http()
    {
        $response = $this->getJsonUser($this->users->route('create'));
        $response->assertOk();

        $props = $response->decodeResponseJson('data.props.blocks.0.props');
        $this->assertEquals(['identifier', 'url', 'method', 'fields', 'actions'], array_keys($props));
        $this->assertEquals(2, count($props['fields']));

        $response = $this->postJsonUser($props['url'], [
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

        $form = Form::for($testUser = new FormTestUser, $fields)
                    ->make()
                    ->setRequest($this->makePostRequest(['name'   => 'Omar',
                                                         'age'    => 33,
                                                         'avatar' => $file]));

        $form->getField('avatar');
        $form->save();
        $this->assertNull($testUser->avatar);

        $this->assertEquals(1, Media::count());
    }

    protected function makeFields(): array
    {
        return [
            FieldFactory::createFromArray(['name' => 'name', 'type' => 'text']),
            FieldFactory::createFromArray(['name' => 'age', 'type' => 'number']),
        ];
    }

    protected function makeField(array $params): Field
    {
        return FieldFactory::createFromArray($params);
    }
}

class FormTestUser extends Model implements Watcher, EntryContract
{
    public $timestamps = false;

    protected $table = 't_users';

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

class TestFileFieldType extends FieldType
{
    protected $hasColumn = false;
}