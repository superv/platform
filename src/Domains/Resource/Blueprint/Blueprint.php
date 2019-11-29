<?php

namespace SuperV\Platform\Domains\Resource\Blueprint;

use Illuminate\Support\Collection;
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

    protected $nav;

    /**
     * @var \SuperV\Platform\Domains\Resource\Driver\DriverInterface
     */
    protected $driver;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $fields;

    public function __construct()
    {
        $this->fields = collect();
    }

    public function addField($fieldName, $fieldType): FieldBlueprint
    {
        $field = new FieldBlueprint($this, $fieldName, $fieldType);

        $this->fields->put($fieldName, $field);

        return $field;
    }

    public function getField($fieldName): FieldBlueprint
    {
        return $this->fields->get($fieldName);
    }

    public function getFields(): Collection
    {
        return $this->fields;
    }

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

    public function getNav()
    {
        return $this->nav;
    }

    /** * @return static */
    public static function resolve()
    {
        return app(static::class);
    }
}