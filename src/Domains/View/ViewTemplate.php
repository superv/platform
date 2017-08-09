<?php namespace SuperV\Platform\Domains\View;

use SuperV\Platform\Support\Collection;

class ViewTemplate extends Collection
{
    protected $loaded = false;

    public function set($key, $value)
    {
        $this->put($key, $value);

        return $this;
    }

    public function isLoaded()
    {
        return $this->loaded;
    }

    public function setLoaded($loaded)
    {
        $this->loaded = $loaded;

        return $this;
    }

//    public function __toString()
//    {
//        return '';
//    }
}