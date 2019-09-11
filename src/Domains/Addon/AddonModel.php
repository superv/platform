<?php

namespace SuperV\Platform\Domains\Addon;

use SuperV\Platform\Domains\Database\Model\Entry;
use SuperV\Platform\Exceptions\PlatformException;

class AddonModel extends Entry
{
    protected $table = 'sv_addons';

    /**
     * Create new Addon instance
     *
     * @return \SuperV\Platform\Domains\Addon\Addon|null
     */
    public function resolveAddon()
    {
        $class = $this->addonClass();

        if (class_exists($class)) {
            return new $class($this);
        } else {
            PlatformException::runtime("Could not resolve addon: ". $class);
        }
    }

    public function getPsrNamespace()
    {
        return $this->psr_namespace;
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @return string
     */
    public function addonClass()
    {
        return $this->getPsrNamespace().'\\'.$this->name;
    }


    public function shortSlug()
    {
        $parts = explode('.', $this->getNamespace());

        return $parts[count($parts) - 1];
    }

    public function shortName()
    {
        return studly_case($this->shortSlug());
    }

    public function getRelativePath()
    {
        return $this->path;
    }

    public function getVendor()
    {
        return $this->vendor;
    }

    public function getRealPath()
    {
        return base_path($this->getRelativePath());
    }

    public static function byNamespace($namespace): ?AddonModel
    {
        return static::query()->where('namespace', $namespace)->first();
    }

}
