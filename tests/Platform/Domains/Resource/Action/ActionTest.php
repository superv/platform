<?php

namespace Tests\Platform\Domains\Resource\Action;

use SuperV\Platform\Domains\Resource\Action\Action;
use SuperV\Platform\Domains\Resource\Action\Contracts\ActionContract;
use SuperV\Platform\Domains\UI\Components\ActionComponent;
use SuperV\Platform\Support\Composer\Payload;
use Tests\Platform\Domains\Resource\ResourceTestCase;

interface AcceptsActionTestEntry
{
    public function acceptActionTestEntry(ActionTestEntry $entry);
}

interface ProvidesActionTestEntry
{
    public function provideActionTestEntry();
}

class ActionTest extends ResourceTestCase
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

class TestPage implements ProvidesActionTestEntry
{
    public function provideActionTestEntry()
    {
        return new ActionTestEntry('test_page_entry');
    }
}

class ActionTestEntry
{
    public $name;

    public function __construct($name)
    {
        $this->name = $name;
    }
}

class EntryAction extends Action implements AcceptsActionTestEntry
{
    protected $name = 'edit';

    protected $title = 'Edit Entry';

    protected $entryName;

    public function onComposed(Payload $payload)
    {
        $payload->set('entry', $this->entryName);
    }

    public function acceptActionTestEntry(ActionTestEntry $entry)
    {
        $this->entryName = $entry->name;
    }
}

