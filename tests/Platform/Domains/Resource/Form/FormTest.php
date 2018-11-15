<?php

namespace Tests\Platform\Domains\Resource\Form;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
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

    function test__saves_entry()
    {
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

    function test__returns_watcher()
    {
        $form = (new FormBuilder)
            ->addGroup('user', $user = new TestUser, $this->makeFields())
            ->prebuild()
            ->getForm();
        $this->assertEquals($user, $form->getWatcher('user'));
    }

    function test__posts_form()
    {
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

    function test__saves_multiple_entries()
    {
        $posts = $this->create('test_posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
        });

        $builder = (new FormBuilder)
            ->addGroup('test_user', $this->users->newEntryInstance(), $this->users)
            ->addGroup('test_post', $posts->newEntryInstance(), $posts)
            ->prebuild();

        $form = FormBuilder::wakeup($builder->uuid())
                           ->setRequest($this->makePostRequest([
                               'name'  => 'Omar',
                               'title' => 'Khalifa',
                               'age'   => 33,
                           ]))
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

    function test__where_are_the_field_types()
    {
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
            FieldFactory::createFromArray(['name' => 'name', 'type' => 'text']),
            FieldFactory::createFromArray(['name' => 'age', 'type' => 'number']),
        ];
    }
}