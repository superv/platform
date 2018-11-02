<?php

namespace Tests\Platform\Domains\Resource\Form;

use Current;
use SuperV\Platform\Domains\Database\Blueprint;
use SuperV\Platform\Domains\Database\Schema;
use SuperV\Platform\Domains\Resource\Form\Form;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class FormTest extends ResourceTestCase
{
    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $resource;

    /** @test */
    function builds_create_form()
    {
        $form = $this->makeCreateForm();

        $this->assertEquals($form, Form::fromCache($form->uuid()));

        $formData = $form->compose();
        $this->assertEquals(Current::url('sv/forms/'.$form->uuid()), $formData->getUrl());
        $this->assertEquals('post', $formData->getMethod());
        $this->assertEquals(['name', 'age', 'bio'], $formData->getFieldKeys());
        $this->assertEquals('Name', $formData->getField('name')->getLabel());

        $formDataArray = $formData->toArray();

        $this->newUser();
        $response = $this->getJson($this->resource->route('create'), $this->getHeaderWithAccessToken());
        $response->assertStatus(200);
        $fields = $response->decodeResponseJson('data.props.page.blocks.0.props.fields');
        $this->assertEquals($formDataArray['fields'], $fields);
    }

    /** @test */
    function posts_create_form()
    {
        $this->newUser();

        $form = $this->makeCreateForm();

        $data = [
            'name' => 'Nicola Tesla',
            'age'  => 99,
            'bio'  => 'Dead',
        ];
        $response = $this->postJson($form->getUrl(), $data, $this->getHeaderWithAccessToken());

        $this->assertEquals(201, $response->getStatusCode());

        $entryModel = $this->resource->resolveModel();
        $entry = $entryModel::first();
        $this->assertNotNull($entry);
        $this->assertEquals('Nicola Tesla', $entry->name);
        $this->assertEquals(99, $entry->age);
        $this->assertEquals('Dead', $entry->bio);
    }

    /** @test */
    function builds_update_form()
    {
        $form = $this->makeEditForm();

//        $this->assertEquals($form, Form::fromCache($form->uuid()));

        $formData = $form->compose();
        $this->assertEquals(Current::url('sv/forms/'.$form->uuid()), $formData->getUrl());
        $this->assertEquals('post', $formData->getMethod());

        $formDataArray = $formData->toArray();

        $valueMap = collect($formDataArray['fields'])->map(function ($field) {
            return [$field['name'], $field['value'] ?? null];
        })->toAssoc()->all();
        $this->assertEquals(['name' => 'Nicola Tesla', 'age' => 99, 'bio' => 'Dead', 'group_id' => 1], $valueMap);

        $this->newUser();
        $response = $this->getJson($this->resource->route('edit'), $this->getHeaderWithAccessToken());
        $response->assertStatus(200);
        $fields = $response->decodeResponseJson('data.props.page.blocks.0.props.fields');
        $this->assertEquals($formDataArray['fields'], $fields);
    }

    /** @test */
    function posts_edit_form()
    {
        $this->withoutExceptionHandling();

        $this->newUser();

        $form = $this->makeEditForm();

        $groups = ResourceFactory::make('test_groups');
        $groups->create(['id' => 1, 'title' => 'Group A']);
        $groups->create(['id' => 2, 'title' => 'Group B']);

        $data = [
            'name'     => 'Updated Nicola Tesla',
            'age'      => 11,
            'bio'      => 'Live',
            'group_id' => 2,
        ];
        $response = $this->postJson($form->getUrl(), $data, $this->getHeaderWithAccessToken());
        $response->assertStatus(201);

        $entryModel = $this->resource->resolveModel();
        $entry = $entryModel::first();
        $this->assertNotNull($entry);
        $this->assertEquals('Updated Nicola Tesla', $entry->name);
        $this->assertEquals(11, $entry->age);
        $this->assertEquals('Live', $entry->bio);
    }

    protected function makeCreateForm(): Form
    {
        $this->resource = $this->makeResource('test_users', ['name', 'age:integer', 'bio:text']);
        $this->resource->build();
        $form = new Form();
        $form->addResource($this->resource);
        $form->build();

        return $form;
    }

    /**
     * @return Form
     */
    protected function makeEditForm(): Form
    {
        Schema::create('test_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->titleColumn();
        });

        Schema::create('test_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('age');
            $table->text('bio');

            $table->belongsToResource('test_groups', 'group')->nullable();
        });
        $this->resource = ResourceFactory::make('test_users');
        $this->resource->build();

        $resourceModelEntry = $this->resource->create([
            'name' => 'Nicola Tesla',
            'age'  => 99,
            'bio'  => 'Dead',
            'group_id' => 1
        ]);

        $this->resource->loadEntry($resourceModelEntry->getKey());

        $form = new Form();
        $form->addResource($this->resource);
        $form->build();

        return $form;
    }
}