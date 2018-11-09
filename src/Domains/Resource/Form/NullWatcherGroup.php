<?php

namespace SuperV\Platform\Domains\Resource\Form;

class NullWatcherGroup extends Group
{
    public function __construct($fields)
    {
        $this->fields = $fields;
        $this->handle = null;
        $this->watcher = null;
    }
}