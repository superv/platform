<?php

namespace Tests\Platform\Domains\UI\Jobs;

use SuperV\Platform\Domains\Resource\Contracts\ProvidesUIComponent;
use SuperV\Platform\Domains\UI\Components\Component;
use SuperV\Platform\Domains\UI\Components\ComponentContract;
use SuperV\Platform\Domains\UI\Jobs\MakeComponentJob;
use Tests\Platform\TestCase;

class MakeComponentJobTest extends TestCase
{
    function test__bsmllh()
    {
        $comp = MakeComponentJob::dispatch(new HeadingProvides);

        $this->assertInstanceOf(Component::class, $comp);

        $this->assertInstanceOf(Component::class,  $comp->getProp('actions.0'));
        $this->assertEquals('component-A',  $comp->getProp('actions.0')->getName());
        $this->assertInstanceOf(Component::class,  $comp->getProp('actions.1'));
        $this->assertEquals('component-C',  $comp->getProp('actions.1')->getName());

        $this->assertInstanceOf(Component::class,  $comp->getProp('actions.0')->getProp('items.0'));
        $this->assertEquals('component-B',  $comp->getProp('actions.0')->getProp('items.0')->getName());

        $this->assertInstanceOf(Component::class,  $comp->getProp('actions.1')->getProp('items.0'));
        $this->assertEquals('component-B',  $comp->getProp('actions.1')->getProp('items.0')->getName());
    }
}

class HeadingProvides implements ProvidesUIComponent
{
    public function makeComponent(): ComponentContract
    {
        return Component::make('sv-header')
                        ->setProp('actions', [
                            new ProviderA,
                            Component::make('component-C')
                                        ->setProp('items', [new ProviderB])
                        ]);
    }
}

class ProviderA implements ProvidesUIComponent
{
    public function makeComponent(): ComponentContract
    {
        return Component::make('component-A')
            ->setProp('items', [new ProviderB]);
    }
}

class ProviderB implements ProvidesUIComponent
{
    public function makeComponent(): ComponentContract
    {
        return Component::make('component-B');
    }
}