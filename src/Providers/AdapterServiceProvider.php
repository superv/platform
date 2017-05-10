<?php namespace SuperV\Platform\Providers;

use SuperV\Platform\Adapters\Container\LaravelContainer;
use SuperV\Platform\Adapters\Events\LaravelDispatcher;
use SuperV\Platform\Adapters\Validator\LaravelValidator;
use SuperV\Platform\Contracts\Container;
use SuperV\Platform\Contracts\Dispatcher;
use SuperV\Platform\Contracts\ServiceProvider;
use SuperV\Platform\Contracts\Validator;

class AdapterServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(Container::class, LaravelContainer::class);
        $this->app->bind(Dispatcher::class, LaravelDispatcher::class);
        $this->app->bind(Validator::class, LaravelValidator::class);
    }
}