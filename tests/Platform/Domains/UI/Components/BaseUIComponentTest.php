<?php

namespace Tests\Platform\Domains\UI\Component;

use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Platform\Contracts\Hibernatable;
use SuperV\Platform\Domains\UI\Components\BaseComponent;
use SuperV\Platform\Domains\UI\Components\ComponentContract;
use SuperV\Platform\Domains\UI\Components\Props;
use SuperV\Platform\Support\Composer\Composable;
use SuperV\Platform\Support\Concerns\HibernatableConcern;
use Tests\Platform\TestCase;

/**
 * Class BaseUIComponentTest
 *
 * @package Tests\Platform\Domains\UI\Component
 * @group   resource
 */
class BaseUIComponentTest extends TestCase
{
    use RefreshDatabase;

    function test__construct()
    {
        $component = TestComponent::make();
        $this->assertInstanceOf(ComponentContract::class, $component);
        $this->assertInstanceOf(Composable::class, $component);

        $this->assertInstanceOf(Props::class, $component->getProps());
    }

    function test__compose()
    {
        $component = TestComponent::make();
        $component->addClass('w-full')
                  ->addClass('relative');

        $this->assertEquals(
            ['w-full', 'relative'],
            $component->getClasses());

        $this->assertEquals([
            'component' => 'sv-test',
            'uuid'      => $component->uuid(),
            'props'     => $component->getProps(),
            'classes'   => implode(' ', $component->getClasses()),
        ], $component->compose());
    }
}

class TestComponent extends BaseComponent implements Hibernatable
{
    use HibernatableConcern;

    public function uuid()
    {
        return 'abc-123';
    }

    public function getName(): string
    {
        return 'sv-test';
    }

    public function getProps(): Props
    {
        return $this->props->merge(['test' => 'cmp']);
    }
}