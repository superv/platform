<?php

namespace SuperV\Platform\Domains\Addon;

use Illuminate\Database\Eloquent\Model;

class AddonModel extends Model
{
    protected $table = 'sv_addons';

    protected $guarded = [];

    /**
     * Create new Addon instance
     *
     * @return \SuperV\Platform\Domains\Addon\Addon
     */
    public function resolveAddon()
    {
        $class = $this->addonClass();

        return new $class($this);
    }

    /**
     * @param $slug
     *
     * @return self
     */
    public static function bySlug($slug) {

        return static::query()->where('slug', $slug)->first();
    }

    public static function allKeyBySlug()
    {

        return static::query()->enabled()->get()->keyBy('slug');
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
        return ucfirst($this->shortSlug());
    }

    public function scopeEnabled($query) {
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
}