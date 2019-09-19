<?php

namespace SuperV\Platform\Extensions;

use SuperV\Platform\Domains\Resource\Extension\Contracts\ExtendsResource;

class FormsExtension implements ExtendsResource
{
    public function extend(\SuperV\Platform\Domains\Resource\Resource $resource)
    {
        $resource->searchable(['uuid']);
    }

    public function extends(): string
    {
        return 'sv_forms';
    }
}
