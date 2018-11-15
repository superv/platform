<?php

namespace Tests\Platform\Domains\Resource\Form;

use Illuminate\Database\Eloquent\Model;
use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Field\Watcher;
use SuperV\Platform\Domains\Resource\Form\FormBuilder;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class FormBuilderTest extends ResourceTestCase
{
    /** @test */
    function builds_create_form()
    {
        $form = (new FormBuilder)
            ->addFields($fields = $this->makeFields())
            ->sleep()
            ->getForm();

        $this->assertNotNull($form->uuid());
        $this->assertNotNull(FormBuilder::wakeup($form->uuid()));
        $this->assertEquals($fields, $form->getFields()->all());
        $this->assertEquals([
            'url'    => sv_url('sv/forms/'.$form->uuid()),
            'method' => 'post',
            'fields' => [
                $form->getField('name')->compose(),
                $form->getField('age')->compose(),
            ],
        ], $form->compose());
    }

    /** @test */
    function builds_update_form()
    {
        $fields = $this->makeFields();

        $form = (new FormBuilder)
            ->addGroup('default', new TestUser(['name' => 'Omar', 'age' => 33]), $fields)
            ->sleep()
            ->getForm();

        $this->assertEquals('Omar', $form->getField('name')->compose()['value']);
        $this->assertEquals(33, $form->getField('age')->compose()['value']);
    }

    function test__removes_field()
    {
        $form = (new FormBuilder)
            ->addGroup('default', new TestUser(['name' => 'Omar', 'age' => 33]), $this->makeFields())
            ->removeField('name')
            ->sleep()
            ->getForm();

        $this->assertEquals(1, $form->getFields()->count());
        $this->assertNull($form->getField('name'));

        // make sure to get values after filter
        $this->assertEquals($form->getFields()->values(), $form->getFields());
    }

    public function makeFields(): array
    {
        return [
            FieldFactory::createFromArray(['name' => 'name', 'type' => 'text']),
            FieldFactory::createFromArray(['name' => 'age', 'type' => 'number']),
        ];
    }
}

class TestUser extends Model implements Watcher
{
    public $timestamps = false;

    protected $guarded = [];
}
