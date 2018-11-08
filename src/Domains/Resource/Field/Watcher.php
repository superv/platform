<?php

namespace SuperV\Platform\Domains\Resource\Field;

interface Watcher
{
    public function watchableUpdated($params);
}