<?php

namespace SuperV\Platform\Providers;

use Illuminate\Support\ServiceProvider;
use Platform;
use SuperV\Platform\Domains\Droplet\DropletModel;
use SuperV\Platform\Domains\Port\PortDetectedEvent;
use SuperV\Platform\Exceptions\DropletNotFoundException;

class ThemeServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app['events']->listen(PortDetectedEvent::class,
            function (PortDetectedEvent $event) {

                $config = Platform::config('ports.'.$event->port);

                if (! $themeSlug = array_get($config, 'theme')) {
                    return;
                }

                if (!$theme = DropletModel::bySlug($themeSlug)) {
                    throw new DropletNotFoundException($themeSlug);
                }

                $this->app['view']->addNamespace('theme', base_path($theme->path.'/resources/views'));
            });
    }
}