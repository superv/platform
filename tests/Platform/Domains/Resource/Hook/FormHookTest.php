<?php

namespace Tests\Platform\Domains\Resource\Hook;

use SuperV\Platform\Domains\Resource\Form\FormBuilder;
use SuperV\Platform\Domains\Resource\Form\FormModel;
use SuperV\Platform\Testing\FormComponent;
use Tests\Platform\Domains\Resource\Fixtures\Resources\OrdersFormDefault;

/**
 * Class FormHookTest
 *
 * @package Tests\Platform\Domains\Resource
 * @group   resource
 */
class FormHookTest extends HookTestCase
{
    function test_resolved()
    {
        $this->blueprints()->orders();

        $form = FormComponent::get(OrdersFormDefault::class, $this);

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

        $form->save();

        $this->assertNotNull($_SERVER['__hooks::form.validating']);
    }


}
