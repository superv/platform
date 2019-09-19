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
            $this->update(['enabled' => false]);
            PlatformException::runtime(sprintf("Disabled unresolvable addon: [%s]", $class));
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

    /**
     * @return string
     */
    public function addonClass()
    {
        return $this->getPsrNamespace().'\\'.studly_case($this->getName().'_'.$this->getType());
    }

    public function getRelativePath()
    {
        return $this->path;
    }

    public function getName()
    {
        return $this->name;
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
        if (str_is('*.*', $identifier)) {
            list($vendor, $identifier) = explode('.', $identifier);
        }
        return static::query()->where('identifier', $identifier)
                     ->first();
    }
}
