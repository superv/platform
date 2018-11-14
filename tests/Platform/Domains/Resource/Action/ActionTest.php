<?php

namespace Tests\Platform\Domains\Resource\Action;

use SuperV\Platform\Domains\Resource\Action\ActionComposer;
use SuperV\Platform\Domains\Resource\Action\Contracts\ActionContract;
use SuperV\Platform\Domains\Resource\Contracts\NeedsEntry;
use SuperV\Platform\Support\Negotiator\Requirer;
use Tests\Platform\Domains\Resource\ResourceTestCase;

class ActionTest extends ResourceTestCase
{
    function test__construct()
    {
        $action = new Action;
        $this->assertInstanceOf(ActionContract::class, $action);
        $this->assertEquals('edit', $action->getName());
        $this->assertEquals('Edit Entry', $action->getTitle());
    }

    function test__composer()
    {
        $action = new Action;

        $composer = new ActionComposer($action);
        $entry = new ActionTestEntry;
        $composer->setEntry($entry);

        $this->assertEquals([
            'name'  => 'edit',
            'title' => 'Edit Entry',
            'entry' => 'test_entry_123',
        ], $composer->compose());
    }
}

class Action implements ActionContract, Requirer, RequiresActionTestEntry
{
    protected $name = 'edit';

    protected $title = 'Edit Entry';

    protected $composed = [];

    protected $requirements = [
        RequiresActionTestEntry::class,
        NeedsEntry::class,
    ];

    public function getRequirements()
    {
        return $this->requirements;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setEntry(ActionTestEntry $entry)
    {
        $this->composed['entry'] = $entry->name;
    }

    public function getComposed(): array
    {
        return $this->composed;
    }
}

class ActionTestEntry
{
    public $name = 'test_entry_123';
}

interface RequiresActionTestEntry
{
    public function setEntry(ActionTestEntry $entry);
}

