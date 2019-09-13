<?php

namespace SuperV\Platform\Domains\Addon;

use SuperV\Platform\Domains\Addon\Events\AddonBootedEvent;
use SuperV\Platform\Support\Concerns\FiresCallbacks;

class Addon
{
    use FiresCallbacks;

    /**
     * @var \SuperV\Platform\Domains\Addon\AddonModel
     */
    protected $entry;

    protected $seeder;

    protected $installs;

    public function __construct(AddonModel $entry)
    {
        $this->entry = $entry;
    }

    public function boot()
    {
        app()->register($this->resolveProvider());

        superv('addons')->put($this->getIdentifier(), $this);

        AddonBootedEvent::dispatch($this);
    }

    public function installs()
    {
        return $this->installs;
    }

    /**
     * Create a new Service Provider instance
     *
     * @return \SuperV\Platform\Domains\Addon\AddonServiceProvider
     */
    public function resolveProvider()
    {
        $class = $this->providerClass();

        /** @var \SuperV\Platform\Domains\Addon\AddonServiceProvider $provider */
        $provider = new $class(app());

        return $provider->setAddon($this);
    }

    public function path($prefix = null)
    {
        return rtrim($this->entry()->path.'/'.ltrim($prefix, '/'), '/');
    }

    public function realPath($prefix = null)
    {
        return base_path($this->path($prefix));
    }

    public function resourcePath($prefix = null)
    {
        return $this->path('resources/'.$prefix);
    }

    public function entry(): AddonModel
    {
        return $this->entry;
    }

    public function getIdentifier()
    {
        return $this->entry->getIdentifier();
    }

    public function getPsrNamespace()
    {
        return $this->entry->getPsrNamespace();
    }

    public function getVendor()
    {
        return $this->entry->getVendor();
    }

    public function getType()
    {
        return $this->entry->getType();
    }

    /**
     * Return Service Provider Class
     *
     * @return string
     */
    public function providerClass()
    {
        return get_class($this).'ServiceProvider';
    }

    public function loadConfigFiles()
    {
        foreach (glob($this->realPath('config/*.php')) as $path) {
            $key = pathinfo($path, PATHINFO_FILENAME);
            $config = config()->get("superv.{$key}", []);

            $fromModule = require $path;
            $merged = array_replace_recursive($fromModule, $config);

            config()->set($this->getIdentifier().'::'.$key, $merged);
        }
    }

    /**
     * Return seeder class
     *
     * @return string
     */
    public function seederClass()
    {
        return $this->seeder;
    }
}
