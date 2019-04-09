<?php

namespace Tests\Platform\Domains\Resource\Http\Controllers;

use Storage;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Media\Media;
use SuperV\Platform\Domains\Resource\Form\Form;
use SuperV\Platform\Domains\Resource\Resource;
use Tests\Platform\Domains\Resource\Fixtures\HelperComponent;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class ResourceFormsTest extends ResourceTestCase
{
    use FieldTestHelper;

    protected $handleExceptions = false;

    function test__displays_create_form()
    {
        $users = $this->schema()
                      ->users(function (Blueprint $table) {
                          $table->select('gender')->options(['m' => 'Male', 'f' => 'Female']);
                          $table->createdBy()->updatedBy();
                      });

        // Get Create form
        //
        $page = $this->getPageFromUrl($users->route('create'));

        $form = HelperComponent::from($page->getProp('blocks.0'));
        $this->assertEquals(7, $form->countProp('fields'));

        $fields = collect($form->getProp('fields'))->keyBy('name');

        $group = $fields->get('group');
        $this->assertEquals('belongs_to', $group['type']);
//        $this->assertEquals(sv_resource('t_groups')->count(), count($group['meta']['options']));

//        $first = sv_resource('t_groups')->first();
//        $this->assertEquals(
//            ['value' => $first->getId(), 'text' => $first->title],
//            $group['meta']['options'][0]
//        );
    }

    function test__displays_extended_create_form()
    {
        $users = $this->schema()->users();

        // extend resource creation form
        //
        Resource::extend('t_users')->with(function (Resource $resource) {
            $resource->on('creating', function (Form $form) {
                $form->onlyFields('name', 'email', 'group');
            });
        });

        // Get Create form
        //
        $page = $this->getPageFromUrl($users->route('create'));

        $form = HelperComponent::from($page->getProp('blocks.0'));
        $this->assertEquals(3, $form->countProp('fields'));
    }

    function test__displays_update_form()
    {
        $user = $this->schema()
                     ->users(function (Blueprint $table) {
                         $table->select('gender')->options(['m' => 'Male', 'f' => 'Female']);
                         $table->createdBy()->updatedBy();
                     })
                     ->fake(['group_id' => 1]);

        // Upload an avatar for the user
        //
        Storage::fake('fakedisk');
        $this->postJsonUser($user->route('update'), ['avatar' => $this->makeUploadedFile()]);

        // Get Update form
        //
        $response = $this->getJsonUser($user->route('edit'))->assertOk();
        $form = HelperComponent::from($response->decodeResponseJson('data'));

        $this->assertEquals($user->route('update'), $form->getProp('url'));
        $this->assertEquals('post', $form->getProp('method'));

        // make sure fields is an array, not an object
        //
        $this->assertNotNull($form->getProp('fields.0'));

        $fields = collect($form->getProp('fields'))->keyBy('name');
        $this->assertEquals(7, $fields->count());

        $name = $fields->get('name');
        $this->assertNotNull($name['uuid']);
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
        $this->assertEquals('Select Gender', $gender['placeholder']);

        $group = $fields->get('group');
        $this->assertEquals('belongs_to', $group['type']);
        $this->assertEquals(1, $group['value']);

//        $this->assertEquals(sv_resource('t_groups')->count(), count($group['meta']['options']));
//
//        $first = sv_resource('t_groups')->first();
//        $this->assertEquals(
//            ['value' => $first->getId(), 'text' => $first->title],
//            $group['meta']['options'][0]
//        );
    }

    function test__displays_extended_update_form()
    {
        $user = $this->schema()
                     ->users(function (Blueprint $table) {
                         $table->select('gender')->options(['m' => 'Male', 'f' => 'Female']);
                         $table->createdBy()->updatedBy();
                     })
                     ->fake(['group_id' => 1]);

        // extend resource edit form
        //
        Resource::extend('t_users')->with(function (Resource $resource) {
            $resource->on('editing', function (Form $form) {
                $form->onlyFields('name', 'email', 'group');
            });
        });

        // Get Update form
        //
        $response = $this->getJsonUser($user->route('edit'))->assertOk();
        $form = HelperComponent::from($response->decodeResponseJson('data'));
        $this->assertEquals(3, $form->countProp('fields'));
    }

    function test__posts_create_form()
    {
        $users = $this->schema()->users();
        $post = [
            'name'     => 'Ali',
            'email'    => 'ali@superv.io',
            'group_id' => 1,
        ];
        $response = $this->postJsonUser($users->route('store'), $post);
        $response->assertOk();

        $user = $users->first();
        $this->assertEquals('Ali', $user->name);
    }

    function test__validation()
    {
        $this->withExceptionHandling();

        $users = $this->schema()->users();
        $users->fake(['email' => 'ali@superv.io']);

        $post = [
            'name'  => 'Ali Selcuk',
            'email' => 'ali@superv.io',
        ];
        $response = $this->postJsonUser($users->route('store'), $post);
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

//        $form = FormConfig::make($users)->makeForm();
        $form = Form::for($users);

        $this->assertEquals(0, count($form->compose()->get('fields')));
    }

    /**
     * @param \SuperV\Platform\Domains\Resource\Resource $resource
     * @return string
     */
    protected function getCreateRoute(Resource $resource): string
    {
        return route('resource.create', ['resource' => $resource->getHandle()]);
    }
}

