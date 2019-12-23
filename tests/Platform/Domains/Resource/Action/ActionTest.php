<?php

namespace Tests\Platform\Domains\Resource\Action;

use SuperV\Platform\Domains\Resource\Action\Action;
use SuperV\Platform\Domains\Resource\Action\Contracts\ActionContract;
use SuperV\Platform\Domains\UI\Components\ActionComponent;
use SuperV\Platform\Testing\PlatformTestCase;

/**
 * Class ActionTest
 *
 * @package Tests\Platform\Domains\Resource\Action
 * @group   resource
 */
class ActionTest extends PlatformTestCase
{
    function test__construct()
    {
        $action = Action::make('some');
        $this->assertInstanceOf(ActionContract::class, $action);
    }

    function test__makes_component()
    {
        $action = Action::make('create');

        $component = $action->makeComponent();
        $this->assertInstanceOf(ActionComponent::class, $component);

        $this->assertEquals([
            'component' => 'sv-action',
            'uuid'      => $component->uuid(),
            'props'     => $component->getProps(),
        ], $component->compose());
    }
}

