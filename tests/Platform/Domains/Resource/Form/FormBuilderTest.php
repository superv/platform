<?php

namespace Tests\Platform\Domains\Resource\Form;

use Illuminate\Database\Eloquent\Model;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Resource\Field\Watcher;
use SuperV\Platform\Domains\Resource\Form\Form;
use SuperV\Platform\Domains\Resource\Form\FormBuilder;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use SuperV\Platform\Domains\Resource\ResourceModel;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class FormBuilderTest extends ResourceTestCase
{
    /** @test */
    function builds_create_form()
    {
        $form = (new FormBuilder)
            ->addFields($fields = $this->makeFields())
            ->prebuild()
            ->getForm();

        $this->assertNotNull($form->uuid());
        $this->assertNotNull(FormBuilder::wakeup($form->uuid()));
        $this->assertEquals($fields, $form->getFields()->all());
        $this->assertEquals([
            'url'    => sv_url('sv/forms/'.$form->uuid()),
            'method' => 'post',
            'fields' => [
                $form->getField('name')->compose(),
                $form->getField('age')->compose(),
            ],
        ], $form->compose());
    }

    /** @test */
    function builds_update_form()
    {
        $fields = $this->makeFields();

        $form = (new FormBuilder)
            ->addGroup('test_user', new TestUser(['name' => 'Omar', 'age' => 33]), $fields)
            ->prebuild()
            ->getForm();

        $this->assertEquals('Omar', $form->getField('name')->compose()['value']);
        $this->assertEquals(33, $form->getField('age')->compose()['value']);
    }

    function test__removes_field()
    {
        $form = (new FormBuilder)
            ->addGroup('test_user', new TestUser(['name' => 'Omar', 'age' => 33]), $this->makeFields())
            ->removeField('name')
            ->prebuild()
            ->getForm();


        $this->assertEquals(1, $form->getFields()->count());
        $this->assertNull($form->getField('name'));

        // make sure to get values after filter
        $this->assertEquals($form->getFields()->values(), $form->getFields());
    }

    /** @test */
    function saves_form()
    {
        $builder = (new FormBuilder)
            ->addFields($fields = $this->makeFields())
            ->prebuild();

        $form = FormBuilder::wakeup($builder->uuid())
                           ->setRequest($this->makePostRequest(['name' => 'Omar', 'age' => 33]))
                           ->build()
                           ->getForm()
                           ->save();

        $this->assertEquals('Omar', $form->getField('name')->value()->get());
        $this->assertEquals(33, $form->getField('age')->value()->get());
    }

    /** @test */
    function saves_entry()
    {
        $this->create('test_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->unsignedInteger('age');
        });

        $builder = (new FormBuilder)
            ->addGroup('test_user', $user = new TestUser, $this->makeFields())
            ->prebuild();

        $form = FormBuilder::wakeup($builder->uuid())
                           ->setRequest($this->makePostRequest(['name' => 'Omar', 'age' => 33]))
                           ->build()
                           ->getForm()
                           ->save();

        $this->assertEquals('Omar', $form->getField('name')->value()->get());
        $this->assertEquals(33, $form->getField('age')->value()->get());

        $user = $form->getWatcher('test_user');
        $this->assertEquals('Omar', $user->name);
        $this->assertEquals(33, $user->age);
        $this->assertTrue($user->wasRecentlyCreated);
    }

    /** @test */
    function posts_form()
    {
        $this->create('test_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->unsignedInteger('age');
        });

        $form = (new FormBuilder)
            ->addGroup('test_user', $user = new TestUser, $this->makeFields())
            ->prebuild()
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

    /** @test */
    function saves_multiple_entries()
    {
        $users = $this->create('test_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });

        $posts = $this->create('test_posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
        });

        $builder = (new FormBuilder)
            ->addGroup('test_user', $users->newEntryInstance(), $users)
            ->addGroup('test_post', $posts->newEntryInstance(), $posts)
            ->prebuild();

        $form = FormBuilder::wakeup($builder->uuid())
                           ->setRequest($this->makePostRequest(['name' => 'Omar', 'title' => "Khalifa"]))
                           ->build()
                           ->getForm()
                           ->save();

        $this->assertEquals('Omar', $form->getField('name')->value()->get());
        $this->assertEquals("Khalifa", $form->getField('title')->value()->get());

        $user = $form->getWatcher('test_user');
        $this->assertEquals('Omar', $user->name);
        $this->assertTrue($user->wasRecentlyCreated);

        $post = $form->getWatcher('test_post');
        $this->assertEquals('Khalifa', $post->title);
        $this->assertTrue($post->wasRecentlyCreated);
    }

    /** @test */
    function where_are_the_field_types()
    {
        $this->create('test_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->unsignedInteger('age');
        });

        $builder = (new FormBuilder)
            ->addGroup('test_user', $user = new TestUser, ResourceFactory::make('test_users'))
            ->prebuild();

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
            $name = Field::make(['name' => 'name', 'type' => 'text']),
            $age = Field::make(['name' => 'age', 'type' => 'number']),
        ];
    }
}

class TestUser extends Model implements Watcher
{
    public $timestamps = false;

    protected $guarded = [];
}
