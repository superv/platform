<?php

namespace SuperV\Platform\Adapters;

use SuperV\Platform\Contracts\Dispatcher;
use SuperV\Platform\Contracts\Filesystem;
use SuperV\Platform\Contracts\ServiceProvider;
use SuperV\Platform\Contracts\Validator;

class AdapterServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(Dispatcher::class, LaravelDispatcher::class);
        $this->app->bind(Validator::class, LaravelValidator::class);
        $this->app->bind(Filesystem::class, LaravelFileSystem::class);
    }
}
