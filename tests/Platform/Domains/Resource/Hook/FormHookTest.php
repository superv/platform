<?php

namespace Tests\Platform\Domains\Resource\Hook;

use SuperV\Platform\Domains\Resource\Form\FormBuilder;
use SuperV\Platform\Domains\Resource\Form\FormModel;
use SuperV\Platform\Domains\Resource\Hook\Hook;
use SuperV\Platform\Exceptions\ValidationException;
use SuperV\Platform\Testing\FormComponent;
use Tests\Platform\Domains\Resource\Fixtures\Resources\OrdersFormDefault;
use Tests\Platform\Domains\Resource\ResourceTestCase;

/**
 * Class FormHookTest
 *
 * @package Tests\Platform\Domains\Resource
 * @group   resource
 */
class FormHookTest extends ResourceTestCase
{
    function test_resolved()
    {
        $this->blueprints()->orders();

        $form = FormComponent::get(OrdersFormDefault::class, $this);

//        dd( Hook::resolve()->get('testing.orders'));


        $this->assertNotNull($form);

        $this->assertEquals(2, $form->getFieldCount());
    }

    function __validating()
    {
        $this->blueprints()->orders();

        $form = FormBuilder::resolve()
                           ->setRequest(['number' => 1, 'status' => 'pending'])
                           ->setFormEntry(FormModel::withIdentifier(OrdersFormDefault::$identifier))
                           ->build();

        $this->assertNotNull($form);

        $_SERVER['__hooks::form.validating'] = null;

        try {
            $form->save();
        } catch (ValidationException $e) {
//            dd($e->getErrorsAsString());
        }
        $this->assertNotNull($_SERVER['__hooks::form.validating']);
    }


    protected function setUp()
    {
        parent::setUp();

        Hook::resolve()->scan(__DIR__.'/../Fixtures/Resources');
    }

    protected function tearDown()
    {
        Hook::resolve()->flush();

        parent::tearDown();
    }
}
