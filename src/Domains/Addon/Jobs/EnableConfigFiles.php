<?php

namespace SuperV\Platform\Domains\Addon\Jobs;

use SuperV\Platform\Domains\Addon\Addon;

class EnableConfigFiles
{
    /**
     * @var Addon
     */
    private $droplet;

    public function __construct(Addon $droplet)
    {
        $this->droplet = $droplet;
    }

    public function handle()
    {
        foreach (glob($this->droplet->path('config/*')) as $path) {
            $key = pathinfo($path, PATHINFO_FILENAME);
            $config = config()->get("superv.{$key}", []);

            $fromModule = require $path;
            $merged = array_replace_recursive($fromModule, $config);

            config()->set($this->droplet->slug().'::'.$key, $merged);
        }
    }
}