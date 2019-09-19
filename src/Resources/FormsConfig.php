<?php

namespace SuperV\Platform\Resources;

use SuperV\Platform\Domains\Resource\ResourceConfig;

class FormsConfig
{
    public static $identifier = 'platform.forms';

    public function resolved(ResourceConfig $config)
    {
        $config->label('Form Entries');
    }
}
