<?php

namespace SuperV\Platform\Domains\Addon\Jobs;

use SuperV\Platform\Domains\Addon\Addon;

class EnableConfigFiles
{
    /**
     * @var Addon
     */
    private $addon;

    public function __construct(Addon $addon)
    {
        $this->addon = $addon;
    }

    public function handle()
    {
        foreach (glob($this->addon->path('config/*')) as $path) {
            $key = pathinfo($path, PATHINFO_FILENAME);
            $config = config()->get("superv.{$key}", []);

            $fromModule = require $path;
            $merged = array_replace_recursive($fromModule, $config);

            config()->set($this->addon->getNamespace().'::'.$key, $merged);
        }
    }
}
