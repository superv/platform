<?php

namespace Tests\Platform\Domains\Resource\Form;

use Current;
use SuperV\Platform\Domains\Database\Blueprint;
use SuperV\Platform\Domains\Database\Schema;
use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Resource\Form\Form;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class FormTest extends ResourceTestCase
{
    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $resource;

    protected function setUp()
    {
        parent::setUp();

        $this->newUser();
    }

    /** @test */
    function builds_create_form()
    {
        $this->resource = $this->makeResource('test_users', ['name', 'age:integer', 'bio:text']);
        $form = Form::of($this->resource)->build();

        $this->assertEquals($form, Form::fromCache($form->uuid()));

        $this->assertEquals(3, $form->getFields()->count());

        $formData = $form->compose();
        $this->assertEquals(Current::url('sv/forms/'.$form->uuid()), $formData->getUrl());
        $this->assertEquals('post', $formData->getMethod());
        $this->assertEquals(['name', 'age', 'bio'], $formData->getFieldKeys());
        $this->assertEquals('Name', $formData->getField('name')->getLabel());

        $formDataArray = $formData->toArray();

        $response = $this->getJson($this->resource->route('create'), $this->getHeaderWithAccessToken());
        $this->assertEquals(
            $formDataArray['fields'],
            $response->decodeResponseJson('data.props.page.blocks.0.props.fields')
        );
    }

    /** @test */
    function posts_create_form()
    {
        $this->resource = $this->makeResource('test_users', ['name', 'age:integer', 'bio:text']);
        $form = Form::of($this->resource)->build();

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

        $this->assertNotNull(Form::fromCache($form->uuid()));

        $formData = $form->compose();
        $this->assertEquals(Current::url('sv/forms/'.$form->uuid()), $formData->getUrl());
        $this->assertEquals('post', $formData->getMethod());

        $formDataArray = $formData->toArray();

        $valueMap = collect($formDataArray['fields'])->map(function ($field) {
            return [$field['name'], $field['value'] ?? null];
        })->toAssoc()->all();
        $this->assertEquals(['name' => 'Nicola Tesla', 'age' => 99, 'bio' => 'Dead', 'group_id' => 1], $valueMap);

        $response = $this->getJson($this->resource->route('edit'), $this->getHeaderWithAccessToken());
        $response->assertStatus(200);

        $fields = $response->decodeResponseJson('data.props.page.blocks.0.props.tabs.0.block.props.fields');
        $this->assertEquals($formDataArray['fields'], $fields);
    }

    /** @test */
    function posts_update_form()
    {
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
        $this->assertEquals(2, $entry->group_id);
    }

    /** @test */
    function removes_fields_from_form()
    {
        $this->resource = $this->makeResource('test_users', ['name', 'age:integer', 'bio:text']);
        $form = Form::of($this->resource);

        $form->removeFieldBeforeBuild(function (Field $field) {
            return $field->getName() === 'age';
        });

        $form->build();
        $this->assertEquals(2, $form->getFields()->count());


        // make sure values() applied after filter
        $this->assertEquals($form->getFields(), $form->getFields()->values());
    }

    /**
     * @return Form
     */
    protected function makeEditForm(): Form
    {
        Schema::create('test_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->entryLabel();
        });

        Schema::create('test_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('age');
            $table->text('bio');

            $table->belongsTo('test_groups', 'group')->nullable();
        });
        $this->resource = ResourceFactory::make('test_users');
//        $this->resource->build();

        $resourceModelEntry = $this->resource->create([
            'name'     => 'Nicola Tesla',
            'age'      => 99,
            'bio'      => 'Dead',
            'group_id' => 1,
        ]);

        $this->resource->loadEntry($resourceModelEntry->getKey());

        $form = new Form();
        $form->addResource($this->resource);
        $form->build();

        return $form;
    }
}