<?php

namespace SuperV\Platform\Domains\Config\Jobs;

use SuperV\Platform\Domains\Droplet\Model\DropletModel;

class EnableConfigFiles
{
    /**
     * @var DropletModel
     */
    private $droplet;

    public function __construct(DropletModel $droplet)
    {
        $this->droplet = $droplet;
    }

    public function handle()
    {
        foreach (glob($this->droplet->getBasePath('config/*')) as $path) {
            $key = pathinfo($path, PATHINFO_FILENAME);
            $config = config()->get("superv.{$key}", []);

            $fromModule = require $path;
            $merged = array_replace_recursive($fromModule, $config);

            config()->set($this->droplet->getSlug().'::'.$key, $merged);
            config()->set($this->droplet->getName().'::'.$key, $merged);
        }
    }
}