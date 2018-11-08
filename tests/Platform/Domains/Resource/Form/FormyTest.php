<?php

namespace Tests\Platform\Domains\Resource\Form;

use Illuminate\Http\Request;
use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Resource\Form\Formy;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class FormyTest extends ResourceTestCase
{
    /** @test */
    function builds_form_with_minimal_data()
    {
        $fields = [
            $name = new Field('name', 'text'),
            $age = new Field('age', 'number'),
        ];

        $form = (new Formy($fields))->boot();
        $this->assertNotNull(Formy::wakeup($form->uuid()));

        $this->assertNotNull($form->uuid());
        $this->assertEquals([
            'url'    => sv_url('sv/forms/'.$form->uuid()),
            'method' => 'post',
            'fields' => [
                $name->compose(),
                $age->compose(),
            ],
        ], $form->compose());
    }

    /** @test */
    function saves_form()
    {
        $fields = [
            $name = new Field('name', 'text'),
            $age = new Field('age', 'number'),
        ];

        $form = (new Formy($fields))->boot();

        $form->request(Request::create('', 'POST', ['name' => 'Omar', 'age' => 33]));

        $form->save();

        $this->assertEquals('Omar', $name->value()->get());
        $this->assertEquals(33, $age->value()->get());
    }
}