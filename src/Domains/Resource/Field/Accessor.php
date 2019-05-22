<?php

namespace SuperV\Platform\Domains\Resource\Field;

use SuperV\Platform\Domains\Resource\Field\Contracts\HasAccessor;

class Accessor
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Field\Contracts\HasAccessor
     */
    protected $accessible;

    public function __construct(HasAccessor $accessible)
    {
        $this->accessible = $accessible;
    }

    public function get(array $params = [])
    {
        $callback = $this->accessible->getAccessor();

        return app()->call($callback, $params);
    }
}