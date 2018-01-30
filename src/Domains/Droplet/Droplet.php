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

    /**
     * Creates new Droplet Service Provider instance
     *
     * @return \SuperV\Platform\Domains\Droplet\ServiceProvider
     */
    public function resolveProvider()
    {
        $class = $this->providerClass();

        return (new $class(app()))->setDroplet($this);
    }

    /**
     * Returns Droplet Entry
     *
     * @return \SuperV\Platform\Domains\Droplet\DropletModel
     */
    public function entry()
    {
        return $this->entry;
    }

    /**
     * Returns Droplet Services Provider Class
     *
     * @return string
     */
    public function providerClass()
    {
        return get_class($this).'ServiceProvider';
    }
}