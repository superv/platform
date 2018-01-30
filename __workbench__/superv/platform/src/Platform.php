<?php

namespace SuperV\Platform;

use SuperV\Platform\Domains\Droplet\DropletModel;

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

    public function install($slug, $path)
    {
        list($vendor, $type, $name) = array_map(
            function ($value) {
                return str_slug(strtolower($value), '_');
            },
            explode('.', $slug)
        );

        $droplet = new DropletModel([
            'name'      => $name,
            'slug'      => $slug,
            'path'      => $path,
            'type'      => str_singular($type),
            'namespace' => ucfirst(camel_case(($vendor == 'superv' ? 'super_v' : $vendor))).'\\'.ucfirst(camel_case($type)).'\\'.ucfirst(camel_case($name)),
            'enabled'   => true,
        ]);

        $droplet->save();
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
}

