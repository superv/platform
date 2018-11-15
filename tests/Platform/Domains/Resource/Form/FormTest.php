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
use SuperV\Platform\Domains\Resource\Form\FormBuilder;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class FormTest extends ResourceTestCase
{
    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $users;

    protected function setUp()
    {
        parent::setUp();

        $this->users = $this->create('test_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->unsignedInteger('age');
        });
    }

    function test__saves_form()
    {
        $builder = (new FormBuilder)
            ->addGroup('default', null, $fields = $this->makeFields())
            ->sleep();

        $form = FormBuilder::wakeup($builder->uuid())
                           ->setRequest($this->makePostRequest(['name' => 'Omar', 'age' => 33]))
                           ->build()
                           ->getForm()
                           ->save();

        $this->assertEquals('Omar', $form->getField('name')->getValue());
        $this->assertEquals(33, $form->getField('age')->getValue());
    }

    function test__saves_entry()
    {
        $builder = (new FormBuilder)
            ->addGroup('default', $user = new TestUser, $this->makeFields())
            ->sleep();

        $form = FormBuilder::wakeup($builder->uuid())
                           ->setRequest($this->makePostRequest(['name' => 'Omar', 'age' => 33]))
                           ->build()
                           ->getForm()
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
        $form = (new FormBuilder)
            ->addGroup('user', $user = new TestUser, $this->makeFields())
            ->sleep()
            ->getForm();
        $this->assertEquals($user, $form->getWatcher('user'));
    }

    function test__posts_form()
    {
        $form = (new FormBuilder)
            ->addGroup('test_user', $user = new TestUser, $this->makeFields())
            ->sleep()
            ->getForm();

        $response = $this->postJsonUser($form->getUrl(), [
            'name' => 'Omar bin Hattab',
            'age'  => 99,
        ]);
        $response->assertOk();

        $user = TestUser::first();
        $this->assertEquals('Omar bin Hattab', $user->name);
        $this->assertEquals(99, $user->age);
    }

    function test__saves_multiple_entries()
    {
        $posts = $this->create('test_posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
        });

        $builder = (new FormBuilder)
            ->addGroup('test_user', $this->users->newEntryInstance(), $this->users)
            ->addGroup('test_post', $posts->newEntryInstance(), $posts)
            ->sleep();

        $form = FormBuilder::wakeup($builder->uuid())
                           ->setRequest($this->makePostRequest([
                               'name'  => 'Omar',
                               'title' => 'Khalifa',
                               'age'   => 33,
                           ]))
                           ->build()
                           ->getForm()
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

    function test__where_are_the_field_types()
    {
        $builder = (new FormBuilder)
            ->addGroup('test_user', $user = new TestUser, ResourceFactory::make('test_users'))
            ->sleep();

        $form = FormBuilder::wakeup($builder->uuid())
                           ->setRequest($this->makePostRequest(['name' => 'Omar', 'age' => 33]))
                           ->build()
                           ->getForm();

        $form->save();

        $user = $form->getWatcher('test_user');

        $this->assertEquals('Omar', $user->name);
        $this->assertEquals(33, $user->age);
        $this->assertTrue($user->wasRecentlyCreated);
    }

    public function makeFields(): array
    {
        return [
            FieldFactory::createFromArray(['name' => 'name', 'type' => 'text']),
            FieldFactory::createFromArray(['name' => 'age', 'type' => 'number']),
        ];
    }

    public function makeField(array $params): Field
    {
        return FieldFactory::createFromArray($params);
    }

    function test__save_handles_fields_mutating_callbacks()
    {
        $fields = [
            $textField = $this->makeField(['name' => 'name', 'type' => 'text']),
            $ageField = $this->makeField(['name' => 'age', 'type' => 'number']),
            $fileField = $this->makeField(['name' => 'avatar', 'type' => 'file']),
        ];
        $builder = (new FormBuilder)
            ->addGroup('default', $testUser = new FormTestUser, $fields)
            ->sleep();

        $form = FormBuilder::wakeup($builder->uuid())
                           ->setRequest($this->makePostRequest(['name' => 'Omar', 'age' => 33, 'avatar' => new UploadedFile($this->basePath('__fixtures__/square.png'), 'square.png')]))
                           ->build()
                           ->getForm();

        $form->getField('avatar')->setFieldTypeResolver(function () {
            return new TestFileFieldType;
        });

        $form->save();

        $this->assertNull($testUser->avatar);

        $this->assertEquals(1, Media::count());

    }
}

class FormTestUser extends Model implements Watcher, EntryContract
{
    protected $table = 'test_users';

    public $timestamps = false;

    protected $guarded = [];

    public function setAttribute($key, $value)
    {
        parent::setAttribute($key, $value);
    }

    public function id()
    {
        return $this->getKey();
    }
}

class TestFileFieldType extends FieldType
{
    protected $hasColumn = false;
}