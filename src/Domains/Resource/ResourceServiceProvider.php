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
use SuperV\Platform\Domains\Resource\Command\ResourceImportCommand;
use SuperV\Platform\Domains\Resource\Hook\Hook;
use SuperV\Platform\Domains\Resource\Jobs\DeleteAddonResources;
use SuperV\Platform\Domains\Resource\Listeners\RegisterEntryEventListeners;
use SuperV\Platform\Domains\Resource\Relation\RelationCollection;
use SuperV\Platform\Events\PlatformInstalledEvent;
use SuperV\Platform\Providers\BaseServiceProvider;

class ResourceServiceProvider extends BaseServiceProvider
{
    protected $listeners = [
        ColumnCreatedEvent::class              => Listeners\SyncField::class,
        ColumnUpdatedEvent::class              => Listeners\SyncField::class,
        ColumnDroppedEvent::class              => Listeners\DeleteField::class,
        TableCreatingEvent::class              => Listeners\CreateResource::class,
        TableCreatedEvent::class               => Listeners\CreateResourceForm::class,
        PlatformInstalledEvent::class          => Listeners\CreatePlatformResourceForms::class,
        AddonBootedEvent::class                => Listeners\RegisterExtensions::class,
        Model\Events\EntrySavingEvent::class   => [
            Listeners\ValidateSavingEntry::class,
            Listeners\SaveUpdatedBy::class,
        ],
        Model\Events\EntrySavedEvent::class    => [
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
        Hook::class => Hook::class,
    ];

    protected $commands = [ResourceImportCommand::class];

    public function register()
    {
        parent::register();

    }

    public function boot()
    {
        if (! Platform::isInstalled()) {
            return;
        }

        RegisterEntryEventListeners::dispatch();
    }
}
