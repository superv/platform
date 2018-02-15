<?php

namespace SuperV\Platform\Domains\Droplet;

class Droplet
{
    /**
     * @var \SuperV\Platform\Domains\Droplet\DropletModel
     */
    protected $entry;

    public function __construct(DropletModel $entry)
    {
        $this->entry = $entry;
    }

    public function slug()
    {
        return $this->entry->slug;
    }

    /**
     * Create a new Service Provider instance
     *
     * @return \SuperV\Platform\Domains\Droplet\DropletServiceProvider
     */
    public function resolveProvider()
    {
        $class = $this->providerClass();

        return (new $class(app()))->setDroplet($this);
    }

    public function path($prefix = null)
    {
        return rtrim($this->entry()->path.'/'.$prefix, '/');
    }

    public function resourcePath($prefix = null)
    {
        return $this->path('resources/'.$prefix);
    }

    /**
     * Return Droplet Entry
     *
     * @return \SuperV\Platform\Domains\Droplet\DropletModel
     */
    public function entry()
    {
        return $this->entry;
    }

    /**
     * Return Service Provider Class
     *
     * @return string
     */
    public function providerClass()
    {
        return get_class($this).'ServiceProvider';
    }
}