<?php

namespace SuperV\Platform\Domains\Task\Features;

use SuperV\Platform\Domains\Feature\AbstractFeatureRequest;

class CreateTaskRequest extends AbstractFeatureRequest
{
    public function make()
    {
        $this->transfer(['title', 'jobs']);
    }
}