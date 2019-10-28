<?php

namespace Tests\Platform\Domains\Resource\Form;

use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Form\FormModel;
use Tests\Platform\Domains\Resource\ResourceTestCase;

/**
 * Class FormFactoryTest
 *
 * @package Tests\Platform\Domains\Resource\Form
 * @group   resource
 */
class FormFactoryTest extends ResourceTestCase
{
    function test__create_form_entry()
    {
        $postsResource = $this->create('testing.posts',
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
        $this->assertTrue($formEntry->isPrivate());
        $this->assertEquals('default', $formEntry->getName());
        $this->assertEquals('testing.posts', $formEntry->getNamespace());
        $this->assertEquals('testing.posts.forms:default', $formEntry->getIdentifier());
        $this->assertEquals($postsResource->id(), $formEntry->resource_id);

        $formFields = $formEntry->compileFields();
        $this->assertEquals($fieldsCount, $formFields->count());

//        $formFields->map(function (Field $field) use ($postsResource) {
//            $this->assertEquals($postsResource->id(), $field->getResource()->id());
//        });
    }
}
