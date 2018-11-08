<?php

namespace SuperV\Platform\Domains\Resource\Concerns;

use SuperV\Platform\Domains\Resource\Field\Watcher;

trait Watchable
{
    protected $watchers = [];

    public function addWatcher(Watcher $watcher)
    {
        $this->watchers[] = $watcher;

        return $this;
    }

    public function removeWatcher(Watcher $detach)
    {
        $this->watchers = collect($this->watchers)->filter(function (Watcher $watcher) use($detach) {
            return $watcher !== $detach;
        })->filter()->values()->all();

        return $this;
    }

    public function notifyWatchers($params)
    {
        collect($this->watchers)->map(function (Watcher $watcher) use ($params) {
            $watcher->watchableUpdated($params);
        });
    }
}