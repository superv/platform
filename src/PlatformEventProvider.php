<?php

namespace SuperV\Platform;

use Illuminate\Foundation\Http\Events\RequestHandled;
use SuperV\Platform\Domains\Droplet\Port\Jobs\RegisterActivePortRoutes;
use SuperV\Platform\Domains\Task\Event\TaskStatusUpdatedEvent;
use SuperV\Platform\Domains\UI\Page\Jobs\InjectMatchedPageJob;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use SuperV\Platform\Domains\Droplet\Module\Jobs\DetectActivePort;
use SuperV\Platform\Domains\Droplet\Module\Jobs\DetectActiveModuleJob;

class PlatformEventProvider extends EventServiceProvider
{
    protected $listen = [
        'Illuminate\Routing\Events\RouteMatched' => [
            DetectActiveModuleJob::class,
            DetectActivePort::class,
            InjectMatchedPageJob::class,
        ],
        'Illuminate\Foundation\Http\Events\RequestHandled' => [
        ],

        TaskStatusUpdatedEvent::class => [

        ],
    ];

    public function boot()
    {
        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $key => $listener) {
                app('events')->listen($event, $listener);
            }
        }

        foreach ($this->subscribe as $subscriber) {
            app('events')->subscribe($subscriber);
        }
    }
}
