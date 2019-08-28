<?php

namespace SuperV\Platform;

use SuperV\Platform\Domains\Addon\Addon;
use SuperV\Platform\Domains\Addon\AddonModel;
use SuperV\Platform\Domains\Port\Port;
use SuperV\Platform\Events\PlatformBootedEvent;
use SuperV\Platform\Events\PlatformBootingEvent;
use SuperV\Platform\Support\Concerns\FiresCallbacks;

class Platform extends Addon
{
    use FiresCallbacks;

    protected $booted = false;

    /**
     * @var \SuperV\Platform\Domains\Port\Port
     */
    protected $port;

    /**
     * @var \Illuminate\Events\Dispatcher
     */
    protected $events;

    /**
     * Boot enabled addons
     */
    public function boot()
    {
        PlatformBootingEvent::dispatch();

        $entries = AddonModel::query()
                             ->where('enabled', true)
                             ->get();

        /** @var AddonModel $entry */
        foreach ($entries as $entry) {
            if ($addon = $entry->resolveAddon()) {
                $addon->boot();
            }
        }

        $this->booted = true;
        PlatformBootedEvent::dispatch();
    }

    public function path($prefix = null)
    {
        $path = realpath(__DIR__.'/../');

        return $path.($prefix ? '/'.$prefix : '');
    }

    public function realPath($prefix = null)
    {
        return $this->path($prefix);
    }

    public function getNamespace()
    {
        return 'platform';
    }

    public function getPsrNamespace()
    {
        return "SuperV\\Platform";
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

    public function fullPath($prefix = null)
    {
        return $this->path($prefix);
    }

    public function instance()
    {
        return $this;
    }

    public function isInstalled(): bool
    {
        return config('superv.installed') === true;
    }

    public function tables()
    {
        return [
            'sv_auth_actions',
            'sv_auth_assigned_actions',
            'sv_auth_roles',
            'sv_auth_assigned_roles',

            'sv_addons',
            'sv_resources',
            'sv_fields',
            'sv_relations',
            'sv_navigation',
            'sv_activities',

            'sv_profiles',
        ];
    }

    /**
     * @return bool
     */
    public function hasBooted(): bool
    {
        return $this->booted;
    }
}

