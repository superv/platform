<?php

namespace SuperV\Platform\Domains\Droplet;

use SuperV\Platform\Domains\Droplet\Contracts\DropletLocator;

class Locator implements DropletLocator
{
    /**
     * @var string
     */
    protected $dropletsPath;

    public function __construct(string $dropletsPath = null)
    {
        $this->dropletsPath = $dropletsPath;
    }

    public function locate(string $slug)
    {
        if (! str_is('*.*.*', $slug)) {
            throw new \Exception('Slug should be snake case and formatted like: {vendor}.{type}.{name}');
        }

        list($vendor, $type, $name) = array_map(
            function ($value) {
                return str_slug(strtolower($value), '_');
            },
            explode('.', $slug)
        );

        return $this->dropletsPath()."/{$vendor}/{$type}/{$name}";
    }

    protected function dropletsPath()
    {
        return $this->dropletsPath ?: \Platform::config('droplets.location');
    }
}