<?php

namespace Tests\Platform\Domains\Resource\Http\Controllers;

use Storage;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Media\Media;
use SuperV\Platform\Domains\Resource\Form\EntryForm;
use SuperV\Platform\Domains\Resource\Form\ResourceFormBuilder;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use SuperV\Platform\Testing\HelperComponent;
use Tests\Platform\Domains\Resource\ResourceTestCase;

/**
 * Class ResourceFormsTest
 *
 * @package Tests\Platform\Domains\Resource\Http\Controllers
 * @group   resource
 * @group   http
 */
class ResourceFormsTest extends ResourceTestCase
{
    use FieldTestHelper;

    protected $handleExceptions = false;

    function test__displays_create_form()
    {
        $this->withoutExceptionHandling();

        $users = $this->blueprints()
                      ->users(function (Blueprint $table) {
                          $table->select('gender')->options(['m' => 'Male', 'f' => 'Female']);
                          $table->createdBy()->updatedBy();
                      });

        // Get Create form
        //
        $form = $this->getUserPage($users->router()->createForm());

        $this->assertEquals(8, $form->countProp('fields'));

        $fields = collect($form->getProp('fields'))->keyBy('name');

        $group = $fields->get('group');
        $this->assertEquals('belongs_to', $group['type']);
    }

    function test__displays_update_form()
    {
        $this->withoutExceptionHandling();

        $users = $this->blueprints()
                      ->users(function (Blueprint $table) {
                          $table->select('gender')->options(['m' => 'Male', 'f' => 'Female']);
                          $table->createdBy()->updatedBy();
                      });

        $user = $users->fake(['group_id' => 1]);

        // Upload an avatar for the user
        //
        Storage::fake('fakedisk');

        $this->postJsonUser($users->router()->updateForm($user), ['avatar' => $this->makeUploadedFile()]);

        // Get Update form
        //
        $form = $this->getUserPage($users->router()->updateForm($user));

//        $this->assertEquals($users->router()->editForm($user), $form->getProp('url'));
        $this->assertEquals('post', $form->getProp('method'));

        // make sure fields is an array, not an object
        //
        $this->assertNotNull($form->getProp('fields.0'));

        $fields = collect($form->getProp('fields'))->keyBy('name');
        $this->assertEquals(8, $fields->count());

        $name = $fields->get('name');
        $this->assertNotNull($name['revision_id']);
        $this->assertEquals('text', $name['type']);
        $this->assertEquals('name', $name['name']);
        $this->assertEquals('Name', $name['label']);
        $this->assertSame($user->name, $name['value']);

        $avatar = $fields->get('avatar');
        $this->assertEquals('file', $avatar['type']);
        $this->assertNull($avatar['value'] ?? null);
        $this->assertEquals(Media::first()->getUrl(), $avatar['image_url'] ?? null);
        $this->assertFalse(isset($avatar['config']));

        $gender = $fields->get('gender');
        $this->assertEquals('select', $gender['type']);
        $this->assertEquals([
            ['value' => 'm', 'text' => 'Male'],
            ['value' => 'f', 'text' => 'Female'],
        ], $gender['meta']['options']
        );
//        $this->assertEquals('Gender Placeholder', $gender['placeholder']);

        $group = $fields->get('group');
        $this->assertEquals('belongs_to', $group['type']);
        $this->assertEquals(1, $group['value']);

//        $this->assertEquals(sv_resource('testing.groups')->count(), count($group['meta']['options']));
//
//        $first = sv_resource('testing.groups')->first();
//        $this->assertEquals(
//            ['value' => $first->getId(), 'text' => $first->title],
//            $group['meta']['options'][0]
//        );
    }

    function __displays_extended_update_form()
    {
        $users = $this->blueprints()
                      ->users(function (Blueprint $table) {
                          $table->select('gender')->options(['m' => 'Male', 'f' => 'Female']);
                          $table->createdBy()->updatedBy();
                      });
        $user = $users->fake(['group_id' => 1]);
        ResourceFactory::wipe();

        // extend resource edit form
        //
        Resource::extend('testing.users')->with(function (Resource $resource) {
            $resource->onEditing(function (EntryForm $form) {
                $form->onlyFields('name', 'email', 'group');
            });
        });

        // Get Update form
        //
        $response = $this->getJsonUser($users->router()->updateForm($user))->assertOk();
        $form = HelperComponent::fromArray($response->decodeResponseJson('data'));
        $this->assertEquals(3, $form->countProp('fields'));
    }

    function test__posts_create_form()
    {
        $this->withoutExceptionHandling();
        $users = $this->blueprints()->users();
        $post = [
            'name'     => 'Ali',
            'email'    => 'ali@superv.io',
            'group_id' => 1,
        ];

        $response = $this->postJsonUser($users->router()->createForm(), $post);
        $response->assertOk();

        $user = $users->first();
        $this->assertEquals('Ali', $user->name);
    }

    function test__validation()
    {
//        $this->withExceptionHandling();

        $users = $this->blueprints()->users();
        $users->fake(['email' => 'ali@superv.io']);

        $post = [
            'name'  => 'Ali Selcuk',
            'email' => 'ali@superv.io',
        ];
        $response = $this->postJsonUser($users->router()->createForm(), $post);
//        dd($response->decodeResponseJson());
        $response->assertStatus(422);

        $this->assertEquals(
            ['email', 'group_id'],
            array_keys($response->decodeResponseJson('errors'))
        );
    }

    function test__fields_that_should_not_show_up()
    {
        $users = $this->create('t_some', function (Blueprint $table) {
            $table->increments('id');
            $table->createdBy()->updatedBy();
            $table->restorable();
        });

        $form = ResourceFormBuilder::buildFromResource($users);
        $form->make();

        $this->assertEquals(0, count($form->compose()->get('fields')));
    }
}

