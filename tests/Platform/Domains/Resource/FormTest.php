<?php

namespace Tests\Platform\Domains\Resource;

use Current;
use SuperV\Platform\Domains\Resource\Form\Form;

class FormTest extends TestCase
{
    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $resource;

    /** @test */
    function builds_form()
    {
        $form = $this->makeResourceForm();

        $this->assertEquals($form, Form::fromCache($form->uuid()));

        $formData = $form->compose();
        $this->assertEquals(Current::url('sv/forms/'.$form->uuid()), $formData->getUrl());
        $this->assertEquals('post', $formData->getMethod());
        $this->assertEquals(['name', 'age', 'bio'], $formData->getFieldKeys());
        $this->assertEquals('Name', $formData->getField('name')->getLabel());
    }

    protected function makeResourceForm(): Form
    {
        $this->resource = $this->makeResource('test_users', ['name', 'age:integer', 'bio:text']);
        $this->resource->build();
        $form = new Form();
        $form->addResource($this->resource);
        $form->build();

        return $form;
    }

    /** @test */
    function posts_form()
    {
        $this->newUser();

        $form = $this->makeResourceForm();

        $response = $this->postJson($form->getUrl(), ['name' => 'Nicola Tesla',
                                                      'age'  => 99,
                                                      'bio'  => 'Dead'], $this->getHeaderWithAccessToken());

        $this->assertEquals(201, $response->getStatusCode());

        $entryModel = $this->resource->resolveModel();
        $entry = $entryModel::first();
        $this->assertNotNull($entry);
        $this->assertEquals('Nicola Tesla', $entry->name);
        $this->assertEquals(99, $entry->age);
        $this->assertEquals('Dead', $entry->bio);
    }
}