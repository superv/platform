<?php

namespace SuperV\Droplets\Another;

use SuperV\Platform\Domains\Droplet\Droplet;

class AnotherDroplet extends Droplet
{
    protected $installs = [
        'themes.another_sub' => 'droplets/themes/another_sub-droplet',
    ];
}