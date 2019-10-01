<?php

namespace SuperV\Platform\Domains\Addon;

use Illuminate\Support\Collection;

/**
 * @method Addon get($key, $default = null)
 */
class AddonCollection extends Collection
{
    public function enabled()
    {
        return $this->filter(function (Addon $addon) {
            return $addon->entry()->enabled;
        });
    }

    public function slugs()
    {
        return $this->map(function (Addon $addon) {
            return $addon->getIdentifier();
        })->values();
    }

    public function identifiers()
    {
        return $this->map(function (Addon $addon) {
            return $addon->getIdentifier();
        })->values();
    }

    public function withSlug(string $slug): ?Addon
    {
        foreach ($this->items as $key => $addon) {
            if ($slug === $key) {
                return $addon;
            }
        }
    }

    public function withClass(string $class): ?Addon
    {
        foreach ($this->items as $key => $addon) {
            if ($class === get_class($addon)) {
                return $addon;
            }
        }
    }
}
