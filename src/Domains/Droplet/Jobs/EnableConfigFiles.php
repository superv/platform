<?php

namespace SuperV\Platform\Domains\Droplet\Jobs;

use SuperV\Platform\Domains\Droplet\Droplet;

class EnableConfigFiles
{
    /**
     * @var Droplet
     */
    private $droplet;

    public function __construct(Droplet $droplet)
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