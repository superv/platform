<?php

namespace Tests\Platform\Domains\Database;

use Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use SuperV\Platform\Domains\Database\Events\TableInsertEvent;
use SuperV\Platform\Domains\Database\Events\TableUpdateEvent;
use SuperV\Platform\Domains\Database\Model\Listener;
use SuperV\Platform\Domains\Resource\Testing\ResourceTestHelpers;

class DatabaseListenerTest
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
            function (TableInsertEvent $event) {
                return $event->table === 'pickles';
            });

        $pick->setAttribute('name', 'pixel')->save();

        Event::assertDispatched(TableUpdateEvent::class,
            function (TableUpdateEvent $event) use ($pick) {
                return $event->table === 'pickles' && $event->rowId === $pick->getId();
            });
    }
}
