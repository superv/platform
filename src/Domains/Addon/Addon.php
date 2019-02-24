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

        superv('addons')->put($this->slug(), $this);

        AddonBootedEvent::dispatch($this);
    }

    public function slug()
    {
        return $this->entry->slug;
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

        return (new $class(app()))->setAddon($this);
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

    /**
     * Return Addon Entry
     *
     * @return \SuperV\Platform\Domains\Addon\AddonModel
     */
    public function entry()
    {
        return $this->entry;
    }

    public function shortSlug()
    {
        return $this->entry->shortSlug();
    }

    public function namespace()
    {
        return $this->entry->namespace;
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

            config()->set($this->slug().'::'.$key, $merged);
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