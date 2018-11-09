<?php

namespace Tests\Platform\Domains\Resource\Form;

use Current;
use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Resource\Field\Types\FieldType;
use SuperV\Platform\Domains\Resource\Form\FormBuilder;
use SuperV\Platform\Domains\Resource\Form\Jobs\BuildFormDeprecated;
use SuperV\Platform\Domains\Resource\Model\Entry;
use SuperV\Platform\Domains\Resource\ResourceModel;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class CreateFormTest extends ResourceTestCase
{
    /** @test */
    function builds_create_form()
    {
        $drop = $this->makeResource('test_users', ['name', 'age:integer', 'bio:text']);

        $builder = (new FormBuilder)->setProvider(ResourceModel::withSlug('test_users'))->prebuild();
        $form = $builder->getForm();

        $builder = FormBuilder::wakeup($builder->uuid());
        $freshForm = $builder->build()->getForm();

        $this->assertEquals($form->uuid(), $freshForm->uuid());
        $this->assertEquals(3, $freshForm->getFields()->count());

        $this->assertEquals(Current::url('sv/forms/'.$freshForm->uuid()), $freshForm->getUrl());
        $this->assertEquals('post', $freshForm->getMethod());
        $this->assertEquals(['name', 'age', 'bio'], $freshForm->getFields()->map(function (Field $field) {
            return $field->getName();
        })->all());
        $this->assertEquals('Name', $freshForm->getField('name')->getLabel());

        cache()->clear();
        $response = $this->getJsonUser($drop->route('create'));

        $this->assertEquals(
            $freshForm->compose()['fields'],
            $response->decodeResponseJson('data.props.page.blocks.0.props.fields')
        );
    }

    /** @test */
    function posts_create_form()
    {
        $drop = $this->makeResource('test_users', ['name', 'age:integer', 'bio:text']);

        $builder = (new FormBuilder)->setProvider(ResourceModel::withSlug('test_users'));
        $builder->addEntry(new Entry($drop->newEntryInstance()));
        $builder->prebuild();
        $form = $builder->getForm();

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
//        $drop = $this->makeResource('test_users', ['name', 'age:integer', 'bio:text']);
//        $form = Form::make();
//
//        $form->removeFieldBeforeBuild(function (FieldType $field) {
//            return $field->getName() === 'age';
//        });
//
//        BuildFormDeprecated::dispatch($form, collect([$drop]));
//
//        $this->assertEquals(2, $form->getFields()->count());
//        // make sure values() applied after filter
//        $this->assertEquals($form->getFields(), $form->getFields()->values());
    }
}