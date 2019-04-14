<?php

namespace Tests\Platform\Platform\Domains\Resource\Form;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Resource\Form\FormModel;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class FormFactoryTest extends ResourceTestCase
{
    function test__create_form_entry()
    {
        $postsResource = $this->create('test_posts',
            function (Blueprint $table) {
                $table->id();
                $table->nullableBelongsTo('sv_resources', 'resource');
                $table->string('title');
                $table->boolean('is_published');
            });

        $fieldsCount = $postsResource->getFields()->count();
        $this->assertTrue($fieldsCount > 0);

        /** @var FormModel $formEntry */
        $formEntry = FormModel::query()->where('resource_id', $postsResource->id())->first();
        $this->assertInstanceOf(FormModel::class, $formEntry);
        $this->assertFalse($formEntry->isPublic());
        $this->assertEquals('Test Posts Form', $formEntry->title);
        $this->assertEquals($postsResource->id(), $formEntry->resource_id);

        $formFields = $formEntry->compileFields();
        $this->assertEquals($fieldsCount, $formFields->count());

        $formFields->map(function (Field $field) use ($postsResource) {
            $this->assertEquals($postsResource->id(), $field->getResource()->id());
        });
    }
}