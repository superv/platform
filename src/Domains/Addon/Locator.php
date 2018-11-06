<?php

namespace SuperV\Platform\Domains\Addon;

use SuperV\Platform\Domains\Addon\Contracts\DropletLocator;

class Locator implements DropletLocator
{
    /**
     * @var string
     */
    protected $addonsPath;

    public function __construct(string $addonsPath = null)
    {
        $this->addonsPath = $addonsPath;
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

        return $this->addonsPath()."/{$vendor}/{$type}/{$name}";
    }

    protected function addonsPath()
    {
        return $this->addonsPath ?: \Platform::config('addons.location');
    }
}