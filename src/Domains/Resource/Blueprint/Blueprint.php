<?php

namespace SuperV\Platform\Domains\Resource\Blueprint;

use SuperV\Platform\Domains\Resource\Driver\DatabaseDriver;
use SuperV\Platform\Domains\Resource\Driver\DriverInterface;

class Blueprint
{
    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var string
     */
    protected $handle;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var array
     */
    protected $nav;

    /**
     * @var \SuperV\Platform\Domains\Resource\Driver\DriverInterface
     */
    protected $driver;

    public function namespace($namespace): self
    {
        $this->namespace = $namespace;

        return $this;
    }

    public function handle($handle): self
    {
        $this->handle = $handle;

        return $this;
    }

    public function label(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function nav($nav): self
    {
        if (is_string($nav)) {
            $nav = ['parent' => $nav];
        }

        $this->nav = $nav;

        return $this;
    }

    public function getIdentifier()
    {
        return $this->getNamespace().'.'.$this->getHandle();
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getHandle(): string
    {
        return $this->handle;
    }

    public function driver(DriverInterface $driver): Blueprint
    {
        $this->driver = $driver;

        return $this;
    }

    public function databaseDriver(): DatabaseDriver
    {
        return $this->driver = DatabaseDriver::resolve();
    }

    public function getDriver(): ?DriverInterface
    {
        return $this->driver;
    }

    /** * @return static */
    public static function resolve()
    {
        return app(static::class);
    }
}