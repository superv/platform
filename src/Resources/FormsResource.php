<?php

namespace SuperV\Platform\Resources;

use SuperV\Platform\Domains\Resource\Hook\Contracts\ConfigResolvedHook;
use SuperV\Platform\Domains\Resource\ResourceConfig;

class FormsResource implements ConfigResolvedHook
{
    public static $identifier = 'platform.forms';

    public function configResolved(ResourceConfig $config)
    {
        $config->label('Form Entries');
    }
}
