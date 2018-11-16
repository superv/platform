<?php

namespace Tests\Platform\Domains\Resource\Form;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Media\Media;
use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Field\Types\FieldType;
use SuperV\Platform\Domains\Resource\Field\Watcher;
use SuperV\Platform\Domains\Resource\Form\Form;
use SuperV\Platform\Domains\Resource\Form\FormConfig;
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

        $config = FormConfig::make();
        $config->addGroup($fields, $watcher, 'default')->sleep();
        $config->hideField('age');

        $this->assertEquals(['age'], $config->getHiddenFields());

        $config = FormConfig::wakeup($config->uuid());
        $this->assertNotNull($config);
        $this->assertEquals(sv_url('sv/forms/'.$config->uuid()), $config->getUrl());

        $form = $config->makeForm();
        $this->assertInstanceOf(Form::class, $form);
        $this->assertEquals(2, $form->getFields()->count());
//        $this->assertEquals($fields, $form->getFields()->all());
        $this->assertEquals($watcher, $form->getWatcher());
        $this->assertEquals($config->uuid(), $form->uuid());
    }

    function test_makes_create_form()
    {
        $config = FormConfig::make()
                            ->addGroup($fields = $this->makeFields())
                            ->sleep();

        $form = FormConfig::wakeup($config->uuid())->makeForm();

        $this->assertEquals($fields, $form->getFields()->all());
        $this->assertEquals([
            'url'    => sv_url('sv/forms/'.$form->uuid()),
            'method' => 'post',
            'fields' => [
                $form->getField('name')->compose()->get(),
                $form->getField('age')->compose()->get(),
            ],
        ], $form->compose()->get());
    }

    function test_makes_create_form_without_sleep_wakeup()
    {
        $form = FormConfig::make()
                          ->addGroup($fields = $this->makeFields())
                          ->makeForm();

        $this->assertEquals($fields, $form->getFields()->all());
        $this->assertEquals([
            'url'    => sv_url('sv/forms/'.$form->uuid()),
            'method' => 'post',
            'fields' => [
                $form->getField('name')->compose()->get(),
                $form->getField('age')->compose()->get(),
            ],
        ], $form->compose()->get());
    }

    function test_makes_update_form()
    {
        $config = FormConfig::make()
                            ->addGroup(
                                $fields = $this->makeFields(),
                                $testUser = new FormTestUser(['name' => 'Omar', 'age' => 33])
                            )
                            ->sleep();

        $form = FormConfig::wakeup($config->uuid())->makeForm();

        $this->assertEquals('Omar', $form->getField('name')->compose()->get('value'));
        $this->assertEquals(33, $form->getField('age')->compose()->get('value'));
    }

    function test__saves_form()
    {
        $config = FormConfig::make()
                            ->addGroup(
                                $fields = $this->makeFields(),
                                $testUser = new FormTestUser(['name' => 'Omar', 'age' => 33])
                            )
                            ->sleep();

        $form = FormConfig::wakeup($config->uuid())
                          ->makeForm()
                          ->setRequest($this->makePostRequest(['name' => 'Omar', 'age' => 33]))
                          ->save();

        $this->assertEquals('Omar', $form->getField('name')->getValue());
        $this->assertEquals(33, $form->getField('age')->getValue());
    }

    function test__hidden_fields()
    {
        $config = FormConfig::make()
                            ->addGroup(
                                $fields = $this->makeFields(),
                                $testUser = new FormTestUser(['name' => 'Omar', 'age' => 33])
                            )
            ->hideField('name')
                            ->sleep();

        $form = FormConfig::wakeup($config->uuid())
                          ->makeForm()
                          ->setRequest($this->makePostRequest(['name' => 'Ali', 'age' => 99]))
                          ->save();

        $nameField = $form->getField('name');
        $this->assertTrue($nameField->isHidden());

        $this->assertEquals('Omar', $nameField->getValue());
        $this->assertEquals(99, $form->getField('age')->getValue());

        $composedFields = $form->compose()->get('fields');
        $this->assertEquals(1, count($composedFields));
        $this->assertEquals(array_values($composedFields), $composedFields);
    }

    function test__saves_entry()
    {
        $config = FormConfig::make()
                            ->addGroup($fields = $this->makeFields(), $user = new FormTestUser)
                            ->sleep();

        $form = FormConfig::wakeup($config->uuid())->makeForm();

        $form->setRequest($this->makePostRequest(['name' => 'Omar', 'age' => 33]))
             ->save();

        $this->assertEquals('Omar', $form->getField('name')->getValue());
        $this->assertEquals(33, $form->getField('age')->getValue());

        $user = $form->getWatcher('default');
        $this->assertEquals('Omar', $user->name);
        $this->assertEquals(33, $user->age);
        $this->assertTrue($user->wasRecentlyCreated);
    }

    function test__returns_watcher()
    {
        $config = FormConfig::make()
                            ->addGroup($fields = $this->makeFields(), $user = new FormTestUser, 'user')
                            ->sleep();

        $form = FormConfig::wakeup($config->uuid())->makeForm();

        $this->assertEquals($user, $form->getWatcher('user'));
    }

    function test__posts_form()
    {
        $config = FormConfig::make()
                            ->addGroup($fields = $this->makeFields(), $user = new FormTestUser)
                            ->sleep();

        $form = FormConfig::wakeup($config->uuid())->makeForm();

        $response = $this->postJsonUser($form->getUrl(), [
            'name' => 'Omar bin Hattab',
            'age'  => 99,
        ]);
        $response->assertOk();

        $user = FormTestUser::first();
        $this->assertEquals('Omar bin Hattab', $user->name);
        $this->assertEquals(99, $user->age);
    }

    function test__resource_create_over_http()
    {
        $response = $this->getJsonUser($this->users->route('create'));
        $response->assertOk();

        $props = $response->decodeResponseJson('data.props.page.blocks.0.props');
        $this->assertEquals(['url', 'method', 'fields'], array_keys($props));
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

    function test__resource_edit_over_http()
    {
        $user = $this->users->create(['name' => 'Omar', 'age' => 123]);

        $response = $this->getJsonUser($user->route('edit'));
        $props = $response->decodeResponseJson('data.props.page.blocks.0.props.tabs.0.block.props');
        $this->assertEquals(['url', 'method', 'fields'], array_keys($props));
        $this->assertEquals(2, count($props['fields']));

        $response = $this->postJsonUser($props['url'], [
            'name' => 'Omar bin Hattab',
            'age'  => 33,
        ]);
        $response->assertOk();

        $user = $this->users->first();
        $this->assertEquals('Omar bin Hattab', $user->name);
        $this->assertEquals(33, $user->age);
    }

    function test__saves_multiple_entries()
    {
        $posts = $this->create('test_posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
        });

        $config = FormConfig::make()
                            ->addGroup($this->users, $this->users->newResourceEntryInstance(), 'test_user')
                            ->addGroup($posts, $posts->newResourceEntryInstance(), 'test_post')
                            ->sleep();

        $form = FormConfig::wakeup($config->uuid())->makeForm()
                          ->setRequest($this->makePostRequest([
                              'name'  => 'Omar',
                              'title' => 'Khalifa',
                              'age'   => 33,
                          ]))
                          ->save();

        $this->assertEquals('Omar', $form->getField('name', 'test_user')->getValue());
        $this->assertEquals("Khalifa", $form->getField('title', 'test_post')->getValue());

        $user = $form->getWatcher('test_user');
        $this->assertEquals('Omar', $user->name);
        $this->assertTrue($user->wasRecentlyCreated);

        $post = $form->getWatcher('test_post');
        $this->assertEquals('Khalifa', $post->title);
        $this->assertTrue($post->wasRecentlyCreated);
    }

    function test__save_handles_fields_mutating_callbacks()
    {
        $fields = [
            $textField = $this->makeField(['name' => 'name', 'type' => 'text']),
            $ageField = $this->makeField(['name' => 'age', 'type' => 'number']),
            $fileField = $this->makeField(['name' => 'avatar', 'type' => 'file']),
        ];

        $config = FormConfig::make()
                            ->addGroup($fields, $testUser = new FormTestUser)
                            ->sleep();

        $file = new UploadedFile($this->basePath('__fixtures__/square.png'), 'square.png');
        $form = FormConfig::wakeup($config->uuid())
                          ->makeForm()
                          ->setRequest($this->makePostRequest(['name'   => 'Omar',
                                                               'age'    => 33,
                                                               'avatar' => $file]));

        $form->getField('avatar')->setFieldTypeResolver(function () {
            return new TestFileFieldType;
        });

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
}

class TestFileFieldType extends FieldType
{
    protected $hasColumn = false;
}