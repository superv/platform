<?php

namespace SuperV\Addons\Another;

use SuperV\Platform\Domains\Addon\Addon;

class AnotherAddon extends Addon
{
    protected $installs = [
        'superv.addons.another_sub' => 'addons/themes/another_sub-addon',
    ];
}
