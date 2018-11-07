<?php

namespace SuperV\Platform\Domains\Resource;

use Platform;
use SuperV\Platform\Domains\Addon\Events\AddonBootedEvent;
use SuperV\Platform\Domains\Database\Events\ColumnCreatedEvent;
use SuperV\Platform\Domains\Database\Events\ColumnDroppedEvent;
use SuperV\Platform\Domains\Database\Events\ColumnUpdatedEvent;
use SuperV\Platform\Domains\Database\Events\TableCreatingEvent;
use SuperV\Platform\Domains\Resource\Listeners\CreateResource;
use SuperV\Platform\Domains\Resource\Listeners\DeleteField;
use SuperV\Platform\Domains\Resource\Listeners\RegisterExtensions;
use SuperV\Platform\Domains\Resource\Listeners\SyncField;
use SuperV\Platform\Providers\BaseServiceProvider;

class ResourceServiceProvider extends BaseServiceProvider
{
    protected $listeners = [
        ColumnCreatedEvent::class => SyncField::class,
        ColumnUpdatedEvent::class => SyncField::class,
        ColumnDroppedEvent::class => DeleteField::class,
        TableCreatingEvent::class => CreateResource::class,
        AddonBootedEvent::class   => RegisterExtensions::class,
    ];

    public function boot()
    {
        if (! Platform::isInstalled()) {
            return;
        }
    }
}