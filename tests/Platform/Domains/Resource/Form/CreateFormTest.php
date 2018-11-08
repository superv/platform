<?php

namespace Tests\Platform\Domains\Resource\Form;

use Current;
use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Resource\Form\Form;
use SuperV\Platform\Domains\Resource\Form\Jobs\BuildForm;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class CreateFormTest extends ResourceTestCase
{
    /** @test */
    function builds_create_form()
    {
        $drop = $this->makeResource('test_users', ['name', 'age:integer', 'bio:text']);
        BuildForm::dispatch($form = Form::make(), collect([$drop]));

        $this->assertEquals($form, Form::fromCache($form->uuid()));
        $this->assertEquals(3, $form->getFields()->count());

        $formData = $form->compose();
        $this->assertEquals(Current::url('sv/forms/'.$form->uuid()), $formData->getUrl());
        $this->assertEquals('post', $formData->getMethod());
        $this->assertEquals(['name', 'age', 'bio'], $formData->getFieldKeys());
        $this->assertEquals('Name', $formData->getField('name')->getLabel());

        cache()->clear();
        $response = $this->getJsonUser($drop->route('create'));

        $this->assertEquals(
            $formData->toArray()['fields'],
            $response->decodeResponseJson('data.props.page.blocks.0.props.fields')
        );
    }

    /** @test */
    function posts_create_form()
    {
        $drop = $this->makeResource('test_users', ['name', 'age:integer', 'bio:text']);
        BuildForm::dispatch($form = Form::make(), collect([$drop]));

        $data = [
            'name' => 'Nicola Tesla',
            'age'  => 99,
            'bio'  => 'Dead',
        ];
        $this->withoutExceptionHandling();

        $response = $this->postJsonUser($form->getUrl(), $data);
        $this->assertEquals(201, $response->getStatusCode());

        $entryModel = $drop->newEntryInstance();
        $entry = $entryModel::first();
        $this->assertNotNull($entry);
        $this->assertEquals('Nicola Tesla', $entry->name);
        $this->assertEquals(99, $entry->age);
        $this->assertEquals('Dead', $entry->bio);
    }

    /** @test */
    function removes_fields_from_form()
    {
        $drop = $this->makeResource('test_users', ['name', 'age:integer', 'bio:text']);
        $form = Form::make();

        $form->removeFieldBeforeBuild(function (Field $field) {
            return $field->getName() === 'age';
        });

        BuildForm::dispatch($form, collect([$drop]));

        $this->assertEquals(2, $form->getFields()->count());
        // make sure values() applied after filter
        $this->assertEquals($form->getFields(), $form->getFields()->values());
    }
}