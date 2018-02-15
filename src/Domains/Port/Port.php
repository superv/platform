<?php

namespace SuperV\Platform\Domains\Port;

use SuperV\Platform\Support\Inflator;

class Port
{
    protected $slug;

    protected $hostname;

    protected $prefix = null;

    protected $theme = null;

    /**
     * @return mixed
     */
    public function slug()
    {
        return $this->slug;
    }

    /**
     * @param mixed $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * @return mixed
     */
    public function hostname()
    {
        return $this->hostname;
    }

    /**
     * @param mixed $hostname
     */
    public function setHostname($hostname)
    {
        $this->hostname = $hostname;
    }

    /**
     * @return null
     */
    public function prefix()
    {
        return $this->prefix;
    }

    /**
     * @param null $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * @return null
     */
    public function theme()
    {
        return $this->theme;
    }

    /**
     * @param null $theme
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;
    }

    public static function fromSlug($slug)
    {
        $config = \Platform::config('ports.'.$slug);

        /** @var self $port */
        $port = Inflator::inflate(app(Port::class), $config);

        $port->setSlug($slug);

        return $port;
    }


}