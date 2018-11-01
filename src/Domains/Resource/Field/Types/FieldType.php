<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use Closure;
use Exception;
use SuperV\Platform\Domains\Resource\Field\FieldModel;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\ResourceEntryModel;
use SuperV\Platform\Support\Concerns\Hydratable;

abstract class FieldType
{
    use Hydratable;

    /**
     * @var \SuperV\Platform\Domains\Resource\Resource
     */
    protected $resource;

    /**
     * @var \SuperV\Platform\Domains\Resource\Field\FieldModel
     */
    protected $entry;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $rules = [];

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var Closure
     */
    protected $accessor;

    /**
     * @var boolean
     */
    protected $built = false;

    public function __construct(FieldModel $entry)
    {
        $this->entry = $entry;
    }

    public static function make($name): self
    {
        return static::fromEntry(new FieldModel(['name' => $name]));
    }

    public static function fromEntry(FieldModel $entry): self
    {
        $field = new static($entry);

        $field->hydrate($entry->toArray());

        return $field;
    }

    public function build(): self
    {
        $this->built = true;

        return $this;
    }

    public function compose()
    {
        $this->checkState();

        return array_filter([
            'uuid'   => $this->uuid(),
            'name'   => $this->getName(),
            'label'  => $this->getLabel(),
            'type'   => $this->getType(),
            'config' => $this->getConfig(),
        ]);
    }

    public function checkState()
    {
        if (! $this->isBuilt()) {
            throw new Exception('Field is not built yet');
        }
    }

    /**
     * @return bool
     */
    public function isBuilt()
    {
        return $this->built;
    }

    public function uuid()
    {
        return $this->entry->uuid();
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label ?: ucwords(str_replace('_', ' ', $this->name));
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function setConfig(array $config): self
    {
        $this->config = $config;

        return $this;
    }

    public function getEntry(): ?FieldModel
    {
        return $this->entry;
    }

    public function mergeRules(array $rules)
    {
        $this->rules = array_merge($this->rules, $rules);
    }

    public function mergeConfig(array $config)
    {
        $this->config = array_merge($this->config, $config);
    }

    public function getResource(): Resource
    {
        return $this->resource;
    }

    public function setResource(Resource $resource): self
    {
        $this->resource = $resource;

        return $this;
    }

    public function getRules(): array
    {
        return $this->rules;
    }

    public function setRules(array $rules): self
    {
        $this->rules = $rules;

        return $this;
    }

    public function getValue()
    {
        if (! $this->getResourceEntry() || ! $this->getResourceEntry()->exists) {
            return null;
        }

        if ($accessor = $this->getAccessor()) {
            return $accessor($this->getResourceEntry(), $this);
        }

        return $this->getResourceEntry()->getAttribute($this->getName());
    }

    public function setValue($value): ?Closure
    {
        ($this->getMutator())($this->getResourceEntry(), $value);

        return null;
    }

    public function getResourceEntry(): ?ResourceEntryModel
    {
        return $this->resource->getEntry();
    }

    public function getAccessor(): ?Closure
    {
        return $this->accessor;
    }

    public function setAccessor(Closure $accessor)
    {
        $this->accessor = $accessor;

        return $this;
    }

    public function getMutator()
    {
        return function (ResourceEntryModel $entry, $value) {
            $entry->setAttribute($this->getName(), $value);
        };
    }
}