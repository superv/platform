<?php

namespace SuperV\Platform\Domains\Droplet;

use Illuminate\Database\Eloquent\Model;

class DropletModel extends Model
{
    protected $table = 'droplets';

    protected $guarded = [];

    /**
     * Create new Droplet instance
     *
     * @return \SuperV\Platform\Domains\Droplet\Droplet
     */
    public function resolveDroplet()
    {
        $class = $this->dropletClass();

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
    public function dropletClass()
    {
        return $this->namespace.'\\'.$this->name;
    }

    public function shortSlug()
    {
        $parts = explode('.', $this->slug);

        return $parts[count($parts) - 1];
    }

    public function scopeEnabled($query) {
        $query->where('enabled', true);
    }
}