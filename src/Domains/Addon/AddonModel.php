<?php

namespace SuperV\Platform\Domains\Addon;

use SuperV\Platform\Domains\Resource\Model\ResourceEntry;

class AddonModel extends ResourceEntry
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
        }
    }

    /**
     * @return string
     */
    public function addonClass()
    {
        return $this->namespace.'\\'.$this->name;
    }

    public function fullSlug()
    {
        return $this->slug;
    }

    public function shortSlug()
    {
        $parts = explode('.', $this->slug);

        return $parts[count($parts) - 1];
    }

    public function shortName()
    {
        return studly_case($this->shortSlug());
    }

    public function scopeEnabled($query)
    {
        $query->where('enabled', true);
    }

    public function getRelativePath()
    {
        return $this->path;
    }

    public function getRealPath()
    {
        return base_path($this->getRelativePath());
    }

    /**
     * @param $slug
     * @return self
     */
    public static function bySlug($slug)
    {
        return static::query()->where('slug', $slug)->first();
    }

    public static function allKeyBySlug()
    {
        return static::query()->enabled()->get()->keyBy('slug');
    }
}