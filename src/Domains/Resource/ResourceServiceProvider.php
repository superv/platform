<?php

namespace SuperV\Platform\Domains\Resource;

use Platform;
use SuperV\Platform\Domains\Addon\Events\AddonBootedEvent;
use SuperV\Platform\Domains\Addon\Events\AddonUninstallingEvent;
use SuperV\Platform\Domains\Database\Events\ColumnCreatedEvent;
use SuperV\Platform\Domains\Database\Events\ColumnDroppedEvent;
use SuperV\Platform\Domains\Database\Events\ColumnUpdatedEvent;
use SuperV\Platform\Domains\Database\Events\TableCreatedEvent;
use SuperV\Platform\Domains\Database\Events\TableCreatingEvent;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Jobs\DeleteAddonResources;
use SuperV\Platform\Domains\Resource\Relation\RelationCollection;
use SuperV\Platform\Providers\BaseServiceProvider;

class ResourceServiceProvider extends BaseServiceProvider
{
    protected $listeners = [
        ColumnCreatedEvent::class              => Listeners\SyncField::class,
        ColumnUpdatedEvent::class              => Listeners\SyncField::class,
        ColumnDroppedEvent::class              => Listeners\DeleteField::class,
        TableCreatingEvent::class              => Listeners\CreateResource::class,
        TableCreatedEvent::class               => Listeners\CreateResourceForm::class,
        AddonBootedEvent::class                => Listeners\RegisterExtensions::class,
        Model\Events\EntrySavingEvent::class   => [
            Listeners\ValidateSavingEntry::class,
            Listeners\SaveUpdatedBy::class,
            Listeners\HookSaving::class,
        ],
        Model\Events\EntrySavedEvent::class    => [
            Listeners\HookSaved::class,
        ],
        Model\Events\EntryCreatingEvent::class => Listeners\SaveCreatedBy::class,
        Resource\ResourceActivityEvent::class  => Listeners\RecordActivity::class,
        AddonUninstallingEvent::class          => DeleteAddonResources::class,
    ];

    protected $_bindings = [
        Table\Contracts\DataProvider::class => Table\EloquentDataProvider::class,
    ];

    protected $_singletons = [
        'relations' => RelationCollection::class,
    ];

    public function register()
    {
        parent::register();

        app('events')->listen('eloquent.creating:*', function ($event, $payload) {
            if (($entry = $payload[0]) instanceof EntryContract) {
                Model\Events\EntryCreatingEvent::dispatch($entry);
            }
        });

        app('events')->listen('eloquent.created:*', function ($event, $payload) {
            if (($entry = $payload[0]) instanceof EntryContract) {
                Model\Events\EntryCreatedEvent::dispatch($entry);
            }
        });
        app('events')->listen('eloquent.deleted:*', function ($event, $payload) {
            if (($entry = $payload[0]) instanceof EntryContract) {
                Model\Events\EntryDeletedEvent::dispatch($entry);
            }
        });
        app('events')->listen('eloquent.saving:*', function ($event, $payload) {
            if (($entry = $payload[0]) instanceof EntryContract) {
                Model\Events\EntrySavingEvent::dispatch($entry);
            }
        });
        app('events')->listen('eloquent.saved:*', function ($event, $payload) {
            if (($entry = $payload[0]) instanceof EntryContract) {
                Model\Events\EntrySavedEvent::dispatch($entry);
            }
        });
        app('events')->listen('eloquent.retrieved:*', function ($event, $payload) {
            if (($entry = $payload[0]) instanceof EntryContract) {
                Model\Events\EntryRetrievedEvent::dispatch($entry);
            }
        });
    }

    public function boot()
    {
        if (! Platform::isInstalled()) {
            return;
        }
    }
}