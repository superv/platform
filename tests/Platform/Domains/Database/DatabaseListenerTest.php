<?php

namespace Tests\Platform\Domains\Database;

use Event;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Platform\Domains\Database\Events\TableInsertEvent;
use SuperV\Platform\Domains\Database\Events\TableUpdateEvent;
use SuperV\Platform\Domains\Database\Model\Listener;
use SuperV\Platform\Domains\Resource\Testing\ResourceTestHelpers;
use Tests\Platform\TestCase;

class DatabaseListenerTest extends TestCase
{
    use RefreshDatabase;
    use ResourceTestHelpers;

    function test__listens_updates_and_insert_and_dispatches_events()
    {
        $pickles = $this->makeResource('pickles', ['name']);

        Event::fake([TableInsertEvent::class, TableUpdateEvent::class]);


        Listener::listen();

        $pick = $pickles->fake();

        Event::assertDispatched(TableInsertEvent::class,
            function(TableInsertEvent $event)  {
            return $event->table === 'pickles' ;
        });

        $pick->getEntry()->setAttribute('name', 'pixel')->save();

        Event::assertDispatched(TableUpdateEvent::class,
            function(TableUpdateEvent $event) use ($pick) {
            return $event->table === 'pickles' && $event->rowId === $pick->getEntry()->getId();
        });

    }


}