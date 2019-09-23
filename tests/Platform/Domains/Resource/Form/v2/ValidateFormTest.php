<?php

namespace Tests\Platform\Domains\Resource\Form\v2;

use SuperV\Platform\Domains\Resource\Form\v2\Jobs\ValidateForm;
use SuperV\Platform\Exceptions\ValidationException;
use Tests\Platform\Domains\Resource\Form\v2\Helpers\FormTestHelpers;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class ValidateFormTest extends ResourceTestCase
{
    use FormTestHelpers;

    function test__validate_success()
    {
        $form = $this->makeForm(
            [
                $this->makeFieldArray('ab.orders.fields:title', 'title', 'text', ['min:3']),
                $this->makeFieldArray('xy.clients.fields:email', 'email', 'email', ['email']),
                $this->makeFieldArray('xy.clients.fields:phone', 'phone', 'number', ['numeric']),
            ]
        );
        $form->setData([
            'ab.orders'  => [
                'title' => 'new-order-title',
            ],
            'xy.clients' => [
                'email' => 'client@email.com',
                'phone' => '847239847',
            ],
        ]);

        ValidateForm::resolve()->validate($form);
        $this->assertTrue($form->isValid());
    }

    function test__validate_fail()
    {
        $form = $this->makeForm(
            [
                $this->makeFieldArray('ab.orders.fields:title', 'title', 'text', ['min:3']),
                $this->makeFieldArray('xy.clients.fields:email', 'email', 'email', ['email']),
                $this->makeFieldArray('xy.clients.fields:phone', 'phone', 'number', ['numeric']),
            ]
        );
        $form->setData([
            'ab.orders'  => [
                'title' => '1',
            ],
            'xy.clients' => [
                'email' => null,
                'phone' => 'my',
            ],
        ]);

        $errors = [];
        try {
            ValidateForm::resolve()->validate($form);
        } catch (ValidationException $e) {
            $errors = $e->getErrors();
        }
        $this->assertFalse($form->isValid());

        $this->assertEquals([
            'ab.orders.title',
            'xy.clients.email',
            'xy.clients.phone',
        ], array_keys($errors));
    }
}


