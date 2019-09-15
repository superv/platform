<?php

namespace SuperV\Platform\Resources;

use SuperV\Platform\Domains\Resource\ResourceConfig;

class FormsConfig extends ResourceConfig
{
    public static $identifier = 'platform::forms';

    public function getLabel()
    {
        return 'Form Entries';
    }
}
