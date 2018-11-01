<?php

namespace Tests\Platform\Domains\Resource;

use Current;
use SuperV\Platform\Domains\Resource\Form\Form;

class FormTest extends TestCase
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

        $this->assertEquals($form, Form::fromCache($form->uuid()));

        $formData = $form->compose();
        $this->assertEquals(Current::url('sv/forms/'.$form->uuid()), $formData->getUrl());
        $this->assertEquals('post', $formData->getMethod());

        $formDataArray = $formData->toArray();

        $valueMap = collect($formDataArray['fields'])->map(function ($field) {
            return [$field['name'], $field['value'] ?? null];
        })->toAssoc()->all();
        $this->assertEquals(['name' => 'Nicola Tesla', 'age' => 99, 'bio' => 'Dead'], $valueMap);

        $this->newUser();
        $response = $this->getJson($this->resource->route('edit'), $this->getHeaderWithAccessToken());
        $response->assertStatus(200);
        $fields = $response->decodeResponseJson('data.props.page.blocks.0.props.fields');
        $this->assertEquals($formDataArray['fields'], $fields);
    }

    /** @test */
    function posts_edit_form()
    {
        $this->newUser();

        $form = $this->makeEditForm();

        $data = [
            'name' => 'Updated Nicola Tesla',
            'age'  => 11,
            'bio'  => 'Live',
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
     * @return \SuperV\Platform\Domains\Resource\Form\Form
     */
    protected function makeEditForm(): \SuperV\Platform\Domains\Resource\Form\Form
    {
        $this->resource = $this->makeResource('test_users', ['name', 'age:integer', 'bio:text']);
        $this->resource->build();

        $data = [
            'name' => 'Nicola Tesla',
            'age'  => 99,
            'bio'  => 'Dead',
        ];
        $resourceModelEntry = $this->resource->getEntry()->create($data);

        $this->resource->loadEntry($resourceModelEntry->getKey());

        $form = new Form();
        $form->addResource($this->resource);
        $form->build();

        return $form;
    }
}