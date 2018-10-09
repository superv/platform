<?php

namespace SuperV\Platform;

use SuperV\Platform\Domains\Droplet\Droplet;
use SuperV\Platform\Domains\Droplet\DropletModel;
use SuperV\Platform\Domains\Port\Port;
use SuperV\Platform\Events\PlatformBootedEvent;

class Platform extends Droplet
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
     * Boot enabled droplets
     */
    public function boot()
    {
        $entries = DropletModel::enabled()->get();

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
     * @return Platform
     */
    public function setPort(Port $port)
    {
        $this->port = $port;

        return $this;
    }

    public function path($prefix = null)
    {
        $path = realpath(__DIR__.'/../');

        return $path.($prefix ? '/'.$prefix : '');
    }

    public function fullPath($prefix = null)
    {
        return $this->path($prefix);
    }

    public function realPath($prefix = null)
    {
        return $this->path($prefix);
    }

    public function instance()
    {
        return $this;
    }

    public function slug()
    {
        return 'platform';
    }

    public function namespace()
    {
        return "SuperV\\Platform";
    }
}

