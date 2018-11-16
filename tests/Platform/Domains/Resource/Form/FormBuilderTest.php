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
            ->makeForm();

        $this->assertNotNull($form->uuid());
        $this->assertNotNull(FormBuilder::wakeup($form->uuid()));
        $this->assertEquals($fields, $form->getFields()->all());
        $this->assertEquals([
            'url'    => sv_url('sv/forms/'.$form->uuid()),
            'method' => 'post',
            'fields' => [
                $form->getField('name')->compose()->get(),
                $form->getField('age')->compose()->get(),
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
            ->makeForm();

        $this->assertEquals('Omar', $form->getField('name')->compose()->get('value'));
        $this->assertEquals(33, $form->getField('age')->compose()->get('value'));
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
