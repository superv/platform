<?php

namespace SuperV\Platform\Domains\Addon;

use SuperV\Platform\Domains\Addon\Contracts\AddonLocator;

class Locator implements AddonLocator
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
        list($vendor, $type, $name) = array_map(
            function ($value) {
                return strtolower($value);
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