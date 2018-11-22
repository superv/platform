<?php

namespace Tests\Platform\Domains\UI\Component;

use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Platform\Domains\UI\Components\BaseUIComponent;
use SuperV\Platform\Domains\UI\Components\UIComponent;
use SuperV\Platform\Support\Composer\Composable;
use Tests\Platform\TestCase;

class BaseUIComponentTest extends TestCase
{
    use RefreshDatabase;

    function test__construct()
    {
        $component = TestComponent::make();
        $this->assertInstanceOf(UIComponent::class, $component);
        $this->assertInstanceOf(Composable::class, $component);
    }

    function test__compose()
    {
        $component = TestComponent::make();
        $component->addClass('w-full')
                  ->addClass('relative');

        $this->assertEquals(
            ['w-full', 'relative'],
            $component->getClasses())
        ;

        $this->assertEquals([
            'component' => 'sv-test',
            'uuid'      => $component->uuid(),
            'props'     => $component->getProps(),
            'class'     => $component->getClasses(),
        ], $component->compose());
    }

    function test__responds_over_http()
    {
        $component = TestComponent::make();
        $this->assertNotNull($component->uuid());
        $this->assertNotNull($url = $component->hibernate());

        $this->withoutExceptionHandling();
        $response = $this->getJsonUser($url);
        $response->assertOk();

        $this->assertEquals(
            $component->compose(),
            $response->decodeResponseJson('data')
        );
    }
}

class TestComponent extends BaseUIComponent
{
    public function uuid(): string
    {
        return 'abc-123';
    }

    public function getName(): string
    {
        return 'sv-test';
    }

    public function getProps(): array
    {
        return ['test' => 'cmp'];
    }
}