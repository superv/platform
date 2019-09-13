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

    public function locate(string $identifier, string $type)
    {
        list($vendor, $package) = array_map(
            function ($value) {
                return strtolower($value);
            },
            explode('.', $identifier)
        );

        $type = str_plural($type);

        return $this->addonsPath()."/{$vendor}/{$type}/{$package}";
    }

    protected function addonsPath()
    {
        return $this->addonsPath ?: \Platform::config('addons.location');
    }
}
