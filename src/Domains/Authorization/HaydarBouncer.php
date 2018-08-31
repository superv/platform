<?php

namespace SuperV\Platform\Domains\Authorization;

use Silber\Bouncer\Bouncer;

class HaydarBouncer implements Haydar
{
    /**
     * @var \Silber\Bouncer\Bouncer
     */
    protected $bouncer;

    public function __construct(Bouncer $bouncer)
    {
        $this->bouncer = $bouncer;
    }

    public function can($ability): bool
    {
        return $this->bouncer->can($ability);
    }
}