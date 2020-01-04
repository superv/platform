<?php

namespace Tests\Platform\Domains\Resource\Hook;

use SuperV\Platform\Domains\Resource\Form\FormFactory;
use SuperV\Platform\Exceptions\ValidationException;
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
    function test_resolving()
    {
        $_SERVER['__hooks::form.resolving'] = null;
        $this->blueprints()->orders();

        $formComponent = FormComponent::get(OrdersFormDefault::class, $this);
        $this->assertNotNull($_SERVER['__hooks::form.resolving']);
        $formComponent->assertIdentifier($_SERVER['__hooks::form.resolving']);
    }

    function test_resolved()
    {
        $_SERVER['__hooks::form.resolved'] = null;
        $this->blueprints()->orders();

        $formComponent = FormComponent::get(OrdersFormDefault::class, $this);
        $this->assertNotNull($_SERVER['__hooks::form.resolved']);
        $formComponent->assertIdentifier($_SERVER['__hooks::form.resolved']);
    }

    function test__validating()
    {
        $this->be($this->newUser());

        $_SERVER['__hooks::form.validating'] = null;
        $orders = $this->blueprints()->orders();

        $this->expectException(ValidationException::class);
        FormFactory::builderFromResource($orders)->resolveForm()->validate();

        $this->assertNotNull($_SERVER['__hooks::form.validating']);
    }
}
