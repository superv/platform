<?php

namespace Tests\Platform\Domains\Resource\Form;

use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Resource\Form\Formy;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class BuildFormTest extends ResourceTestCase
{
    /** @test */
    function builds_form_with_minimal_data()
    {
        $fields = [
            new Field('text', 'ab-01', 'title', 'Title'),
            new Field('number', 'ab-02', 'age', 'Age'),
        ];

        $form = new Formy($fields);

        $this->assertNotNull($form->uuid());

        $this->assertEquals([
            'url'    => sv_url('sv/forms/'.$form->uuid()),
            'method' => 'post',
            'fields' => [
                [
                    'type'  => 'text',
                    'uuid'  => 'ab-01',
                    'name'  => 'title',
                    'label' => 'Title',
                ],
                [
                    'type'  => 'number',
                    'uuid'  => 'ab-02',
                    'name'  => 'age',
                    'label' => 'Age',
                ],
            ],
        ], $form->compose());
    }
}