<?php

namespace Tests\Platform\Domains\Resource;

use Current;
use Event;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Database\Schema\Blueprint;
use SuperV\Platform\Domains\Resource\Model\Events\EntryCreatedEvent;
use SuperV\Platform\Domains\Resource\Model\Events\EntryDeletedEvent;
use SuperV\Platform\Domains\Resource\Resource;

class ResourceEventsTest extends ResourceTestCase
{
    function test__dispatches_event_when_a_resource_entry_is_created()
    {
        Event::fake(EntryCreatedEvent::class);

        $res = $this->create('t_users', function (Blueprint $table) {
            $table->increments('id');
        });

        $fake = $res->fake();

        Event::assertDispatched(EntryCreatedEvent::class, function ($event) use ($fake) {
            return $event->entry->id === $fake->id;
        });
    }

    function test__dispatches_event_when_a_resource_entry_is_deleted()
    {
        Event::fake(EntryDeletedEvent::class);

        $res = $this->create('t_users', function (Blueprint $table) {
            $table->increments('id');
        });

        $fake = $res->fake();
        $fake->delete();

        Event::assertDispatched(EntryDeletedEvent::class, function ($event) use ($fake) {
            return $event->entry->id === $fake->id;
        });
    }

    function test__triggers_on_created_hooks()
    {
        $users = $this->create('t_users', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamp('confirmed_at')->nullable();
        });

        $confirmations = $this->create('t_confirmations', function (Blueprint $table) {
            $table->increments('id');
            $table->belongsTo('t_users', 'user');
            $table->timestamp('created_at');
        });

        Resource::extend('t_confirmations')->with(function (Resource $resource) {
            $resource->onCreated(function (EntryContract $entry) {
                $entry->load('user')->user->update(['confirmed_at' => $entry->created_at]);
            });
        });
        $user = $users->create();

        $confirmation = $confirmations->create([
            'user_id'    => $user->id,
            'created_at' => Current::time(),
        ]);

        $this->assertEquals($confirmation->created_at, $user->fresh()->confirmed_at);
    }
}