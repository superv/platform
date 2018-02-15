<?php

namespace SuperV\Platform;

use SuperV\Platform\Domains\Droplet\DropletModel;
use SuperV\Platform\Events\PlatformBootedEvent;

class Platform
{
    protected $activePort;

    /**
     * @var \Illuminate\Events\Dispatcher
     */
    protected $events;

    public function boot()
    {
        $entries = DropletModel::where('enabled', true)->get();

        /** @var DropletModel $entry */
        foreach ($entries as $entry) {
            app()->register($entry->resolveDroplet()->resolveProvider());
        }

        PlatformBootedEvent::dispatch();
    }

    public function config($key, $default = null)
    {
        return config("superv.{$key}", $default);
    }

    public function activePort()
    {
        return $this->activePort;
    }

    /**
     * @param mixed $activePort
     *
     * @return Platform
     */
    public function setActivePort($activePort)
    {
        $this->activePort = $activePort;

        return $this;
    }

    public function path($prefix = null)
    {
        $path = $this->config('droplets.location').'/superv/platform';

        return $path.($prefix ? '/'.$prefix : '');
    }

    public function fullPath($prefix = null)
    {
        return base_path($this->path($prefix));
    }
}

