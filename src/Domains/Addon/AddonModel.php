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

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function getPackage()
    {
        return $this->package;
    }

    /**
     * @return string
     */
    public function addonClass()
    {
        return $this->getPsrNamespace().'\\'.studly_case($this->getPackage().'_'.$this->getType());
    }


    public function shortSlug()
    {
        $parts = explode('/', $this->getPackage());

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

    public function getType()
    {
        return $this->type;
    }

    public function getRealPath()
    {
        return base_path($this->getRelativePath());
    }

    public static function byIdentifier($identifier): ?AddonModel
    {
        return static::query()->where('identifier', $identifier)->first();
    }

}
