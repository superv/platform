<?php

namespace SuperV\Platform;

use SuperV\Platform\Domains\Droplet\DropletModel;
use SuperV\Platform\Domains\Port\Port;
use SuperV\Platform\Events\PlatformBootedEvent;

class Platform
{
    /**
     * @var \SuperV\Platform\Domains\Port\Port
     */
    protected $port;

    /**
     * @var \Illuminate\Events\Dispatcher
     */
    protected $events;

    /**
     * Boot enable droplets
     */
    public function boot()
    {
        $entries = DropletModel::where('enabled', true)->get();

        /** @var DropletModel $entry */
        foreach ($entries as $entry) {
            $droplet = $entry->resolveDroplet();
            app()->register($droplet->resolveProvider());

            superv('droplets')->put($droplet->slug(), $droplet);
        }

        PlatformBootedEvent::dispatch();
    }

    public function config($key, $default = null)
    {
        return config("superv.{$key}", $default);
    }

    /**
     * @return \SuperV\Platform\Domains\Port\Port
     */
    public function port()
    {
        return $this->port;
    }

    /**
     * @param \SuperV\Platform\Domains\Port\Port $port
     *
     * @return Platform
     */
    public function setPort(Port $port)
    {
        $this->port = $port;

        return $this;
    }

    public function path($prefix = null)
    {
        $path = $this->config('droplets.location').'/superv/platform';
        $path = realpath(__DIR__ . '/../');

        return $path.($prefix ? '/'.$prefix : '');
    }

    public function fullPath($prefix = null)
    {
        return $this->path($prefix);
//        return base_path($this->path($prefix));
    }
}

