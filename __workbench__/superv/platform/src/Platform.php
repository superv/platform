<?php

namespace SuperV\Platform;

use SuperV\Platform\Packs\Droplet\DropletModel;

class Platform
{
    protected $activePort;

    public function boot()
    {
        $entries = DropletModel::where('enabled', true)->get();

        /** @var DropletModel $entry */
        foreach ($entries as $entry) {
            app()->register($entry->resolveDroplet()->resolveProvider());
        }
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
        $path = $this->config('droplets.location') . '/superv/platform';

        return $path . ($prefix ? '/'.$prefix : '');
    }

    public function fullPath($prefix = null)
    {
        return base_path($this->path($prefix));
    }
}

