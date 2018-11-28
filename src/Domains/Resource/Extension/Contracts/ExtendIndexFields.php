<?php

namespace SuperV\Platform\Domains\Resource\Extension\Contracts;

use SuperV\Platform\Domains\Resource\Resource\Fields;

interface ExtendIndexFields
{
    public function extendIndexFields(Fields $fields);
}