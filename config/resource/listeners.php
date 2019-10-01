<?php

use SuperV\Platform\Domains\Addon\Events\AddonBootedEvent;
use SuperV\Platform\Domains\Addon\Events\AddonUninstallingEvent;
use SuperV\Platform\Domains\Database\Events\ColumnCreatedEvent;
use SuperV\Platform\Domains\Database\Events\ColumnDroppedEvent;
use SuperV\Platform\Domains\Database\Events\ColumnUpdatedEvent;
use SuperV\Platform\Domains\Database\Events\TableCreatedEvent;
use SuperV\Platform\Domains\Database\Events\TableCreatingEvent;
use SuperV\Platform\Domains\Database\Events\TableDroppedEvent;
use SuperV\Platform\Domains\Resource\Database\Entry\Events as DatabaseEvents;
use SuperV\Platform\Domains\Resource\Events\ResourceCreatedEvent;
use SuperV\Platform\Domains\Resource\Jobs\DeleteAddonResources;
use SuperV\Platform\Domains\Resource\Jobs\DeleteResource;
use SuperV\Platform\Domains\Resource\Jobs\GetEntryResource;
use SuperV\Platform\Domains\Resource\Jobs\ModifyEntryAttributes;
use SuperV\Platform\Domains\Resource\Listeners;
use SuperV\Platform\Domains\Resource\Listeners\CreateResourceAuthActions;
use SuperV\Platform\Domains\Resource\Resource\ResourceActivityEvent;

return [
    TableDroppedEvent::class =>
        function (TableDroppedEvent $event) {
            if (! $resourceIdentifier = GetEntryResource::dispatch($event->table, $event->connection)) {
                return;
            }

            DeleteResource::dispatch($resourceIdentifier);
        },

    ColumnCreatedEvent::class                => Listeners\SaveFieldEntry::class,
    ColumnUpdatedEvent::class                => Listeners\SaveFieldEntry::class,
    ColumnDroppedEvent::class                => Listeners\DeleteField::class,
    TableCreatingEvent::class                => Listeners\CreateResource::class,
    TableCreatedEvent::class                 => Listeners\CreateResourceForm::class,
    AddonBootedEvent::class                  => Listeners\RegisterExtensions::class,
    DatabaseEvents\EntrySavingEvent::class   => [
        Listeners\SaveUpdatedBy::class,
        ModifyEntryAttributes::class,
    ],
    DatabaseEvents\EntryCreatingEvent::class => Listeners\SaveCreatedBy::class,
    ResourceActivityEvent::class             => Listeners\RecordActivity::class,
    AddonUninstallingEvent::class            => DeleteAddonResources::class,
    ResourceCreatedEvent::class              => CreateResourceAuthActions::class,
];