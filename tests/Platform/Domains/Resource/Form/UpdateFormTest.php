<?php

namespace Tests\Platform\Domains\Resource\Form;

use Current;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Database\Schema\Schema;
use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Resource\Form\Form;
use SuperV\Platform\Domains\Resource\Form\FormBuilder;
use SuperV\Platform\Domains\Resource\Form\Jobs\BuildFormDeprecated;
use SuperV\Platform\Domains\Resource\Model\Entry;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use SuperV\Platform\Domains\Resource\ResourceModel;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class UpdateFormTest extends ResourceTestCase
{
    protected function setUp()
    {
        parent::setUp();

        Schema::create('test_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->entryLabel();
        });

        $groups = ResourceFactory::make('test_groups');
        $groups->create(['id' => 1, 'title' => 'Group A']);
        $groups->create(['id' => 2, 'title' => 'Group B']);

        Schema::create('test_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('age');
            $table->text('bio');

            $table->belongsTo('test_groups', 'group')->nullable();
        });
    }

    /** @test */
    function builds_update_form()
    {
        $drop = ResourceFactory::make('test_users');

        $resourceModelEntry = $drop->create([
            'name'     => 'Nicola Tesla',
            'age'      => 99,
            'bio'      => 'Dead',
            'group_id' => 1,
        ]);

        $drop->loadEntry($resourceModelEntry->getKey());

//        BuildFormDeprecated::dispatch($form = Form::make(), collect([$drop]));
//        $formFields = $form->getFields();
//        $this->assertNotNull(Form::fromCache($form->uuid()));
//        $this->assertEquals(4, $formFields->count());

        $builder = (new FormBuilder)->setProvider(ResourceModel::withSlug('test_users'))->prebuild();
        $builder->addEntry(new Entry($drop->getEntry()));
        $form = $builder->getForm();

        $this->assertEquals(Current::url('sv/forms/'.$form->uuid()), $form->getUrl());
        $this->assertEquals('post', $form->getMethod());

        $builder = FormBuilder::wakeup($builder->uuid());
        $freshForm = $builder->build()->getForm();

        $valueMap = $freshForm->getFields()->map(function (Field $field) {
            return [$field->getName(), $field->getValue() ?? null];
        })->toAssoc()->all();

        $this->assertEquals([
            'name'     => 'Nicola Tesla',
            'age'      => 99,
            'bio'      => 'Dead',
            'group_id' => 1,
        ], $valueMap);

        cache()->clear();
        $response = $this->getJsonUser($drop->route('edit'));
        $response->assertStatus(200);

        $fields = $response->decodeResponseJson('data.props.page.blocks.0.props.tabs.0.block.props.fields');
        $this->assertEquals($formDataArray['fields'], $fields);
    }

    /** @test */
    function posts_update_form()
    {
        $resource = ResourceFactory::make('test_users');
        $nicola = $resource->create([
            'name'     => 'Nicola Tesla',
            'age'      => 99,
            'bio'      => 'Dead',
            'group_id' => 1,
        ]);

        $resource->loadEntry($nicola->getKey());

        BuildFormDeprecated::dispatch($form = Form::make(), collect([$resource]));

        $data = [
            'name'     => 'Updated Nicola Tesla',
            'age'      => 11,
            'bio'      => 'Live',
            'group_id' => 2,
        ];
        $response = $this->postJsonUser($form->getUrl(), $data);
        $response->assertStatus(201);

        $entry = $nicola->fresh();
        $this->assertNotNull($entry);
        $this->assertEquals('Updated Nicola Tesla', $entry->name);
        $this->assertEquals(11, $entry->age);
        $this->assertEquals('Live', $entry->bio);
        $this->assertEquals(2, $entry->group_id);
    }
}