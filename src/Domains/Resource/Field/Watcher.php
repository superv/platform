<?php

namespace SuperV\Platform\Domains\Resource\Field;

interface Watcher
{
    public function setAttribute($key, $value);

    public function getAttribute($key);

    public function save();
}