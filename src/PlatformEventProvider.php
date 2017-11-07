<?php

namespace SuperV\Platform;

use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use SuperV\Platform\Domains\Droplet\Module\Jobs\DetectActiveModule;
use SuperV\Platform\Domains\Task\Event\TaskStatusUpdatedEvent;
use SuperV\Platform\Domains\UI\Page\Jobs\InjectMatchedPage;

class PlatformEventProvider extends EventServiceProvider
{
    protected $listen = [
        'Illuminate\Routing\Events\RouteMatched'           => [
            DetectActiveModule::class,
            InjectMatchedPage::class,
        ],
        'Illuminate\Foundation\Http\Events\RequestHandled' => [],

        TaskStatusUpdatedEvent::class => [],
    ];

    public function boot()
    {
        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                app('events')->listen($event, $listener);
            }
        }

        foreach ($this->subscribe as $subscriber) {
            app('events')->subscribe($subscriber);
        }
    }
}
