<?php namespace SuperV\Platform;

use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use SuperV\Platform\Domains\Droplet\Module\Jobs\DetectActiveModuleJob;
use SuperV\Platform\Domains\Droplet\Module\Jobs\DetectCurrentPortJob;
use SuperV\Platform\Domains\UI\Page\Jobs\InjectMatchedPageJob;

class PlatformEventProvider extends EventServiceProvider
{
    protected $listen = [
//        'superv::app.loaded' => [
//            DetectActiveModuleJob::class
//        ],
        'Illuminate\Routing\Events\RouteMatched' => [
            DetectActiveModuleJob::class,
            DetectCurrentPortJob::class,
            InjectMatchedPageJob::class
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