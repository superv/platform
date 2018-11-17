<?php

namespace SuperV\Platform\Domains\Resource;

use SuperV\Platform\Support\Concerns\Hydratable;

abstract class BaseConfig
{
    use Hydratable;

    /** @var string */
    protected $uuid;

    /** @var string */
    protected $url;

    /** @var bool */
    protected $hibernating = false;

    protected function __construct()
    {
        $this->boot();
    }

    protected function boot()
    {
        $this->uuid = uuid();
        $this->url = $this->makeUrl();
    }

    public function hibernate()
    {
        cache()->forever($this->cacheKey($this->uuid()), serialize($this));

        $this->hibernating = true;

        return $this;
    }

    protected function makeUrl()
    {
        return sv_url(sprintf('sv/%s/%s', static::getHandle(), $this->uuid));
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function uuid(): string
    {
        return $this->uuid;
    }

    public static function cacheKey(string $uuid): string
    {
        return sprintf('sv:%:%', static::getHandle(), $uuid);
    }

    public static function wakeup($uuid)
    {
        if ($config = cache(static::cacheKey($uuid))) {
            return unserialize($config);
        }

        return null;
    }

    abstract public static function getHandle(): string;
}