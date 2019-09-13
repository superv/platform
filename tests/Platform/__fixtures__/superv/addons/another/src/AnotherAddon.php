<?php

namespace SuperV\Addons\Another;

use SuperV\Platform\Domains\Addon\Addon;

class AnotherAddon extends Addon
{
    protected $installs = [
        'superv.another_sub' => [
            'path' => 'addons/themes/another_sub-addon',
            'type' => 'addon',
        ],
    ];
}
