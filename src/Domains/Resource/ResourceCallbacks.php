<?php

namespace SuperV\Platform\Domains\Resource;

use Closure;

trait ResourceCallbacks
{
    public function onCreating(Closure $callable)
    {
        $this->on('creating', $callable);
    }

    public function onEditing(Closure $callable)
    {
        $this->on('editing', $callable);
    }

    public function onViewPage(Closure $callable)
    {
        $this->on('view.page', $callable);
    }

    public function onIndexPage(Closure $callable)
    {
        $this->on('index.page', $callable);
    }

    public function onIndexData(Closure $callable)
    {
        $this->on('index.data', $callable);
    }

    public function onIndexConfig(Closure $callable)
    {
        $this->on('index.config', $callable);
    }
}