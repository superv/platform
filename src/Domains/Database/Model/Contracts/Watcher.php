<?php

namespace SuperV\Platform\Domains\Database\Model\Contracts;

interface Watcher
{
    public function setAttribute($key, $value);

    public function getAttribute($key);

    public function save();
}