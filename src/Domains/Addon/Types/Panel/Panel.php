<?php

namespace SuperV\Platform\Domains\Addon\Types\Panel;

use Hub;
use SuperV\Platform\Domains\Addon\Addon;
use SuperV\Platform\Domains\Port\Port;

abstract class Panel extends Addon
{
    /**
     * The panel frontend url
     *
     * @var string
     */
    protected $url;

    /**
     * The slug for panel port
     *
     * @var string
     */
    protected $portSlug;

    /**
     * Resolve panel port
     *
     * @return \SuperV\Platform\Domains\Port\Port
     */
    public function getPort(): Port
    {
        return Hub::get($this->portSlug);
    }

    public static function make($addon): Panel
    {
        return sv_addons($addon);
    }
}