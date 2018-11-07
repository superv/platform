<?php

namespace SuperV\Platform\Domains\Resource\Field;

use Closure;
use Exception;
use Illuminate\Http\Request;
use SuperV\Platform\Domains\Resource\Contracts\HasResource;
use SuperV\Platform\Domains\Resource\Model\ResourceEntryModel;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Exceptions\PlatformException;
use SuperV\Platform\Support\Concerns\FiresCallbacks;
use SuperV\Platform\Support\Concerns\HasConfig;
use SuperV\Platform\Support\Concerns\Hydratable;

abstract class Field implements HasResource
{
    use Hydratable;
    use HasConfig;
    use FiresCallbacks;

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

    /** @var bool */
    protected $unique = false;

    /** @var bool */
    protected $required = false;

    /**
     * @var array
     */
    protected $rules = [];

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var boolean
     */
    protected $built = false;

    /**
     * Indicate if field type needs a database column
     *
     * @var bool
     */
    protected $hasColumn = true;

    public function __construct(FieldModel $entry)
    {
        $this->entry = $entry;
    }

    public function show(): bool
    {
        return true;
    }

    public function build()
    {
        if ($this->isBuilt()) {
            throw new Exception('Field is already built');
        }

        $this->built = true;

        return $this;
    }

    public function buildForView($query)
    {
        return $this;
    }

    public function copy(): self
    {
        if ($this->isBuilt()) {
            return static::fromEntry($this->entry->fresh());
        }

        return clone $this;
    }

    public function compose(): array
    {
        if (! $this->isBuilt()) {
            throw new Exception('Field is not built yet');
        }

        return array_filter([
            'uuid'   => $this->uuid(),
            'name'   => $this->getColumnName(),
            'label'  => $this->getLabel(),
            'type'   => $this->getType(),
            'config' => $this->getConfig(),
            'value'  => $this->getValue(),
        ]);
    }

    public function isBuilt(): bool
    {
        return $this->built;
    }

    public function uuid()
    {
        return $this->entry->uuid();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function hasColumn(): bool
    {
        return $this->hasColumn;
    }

    public function getColumnName(): ?string
    {
        return $this->name;
    }

    public function getLabel()
    {
        return $this->label ?: ucwords(str_replace('_', ' ', $this->name));
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getEntry(): ?FieldModel
    {
        return $this->entry;
    }

    public function hasEntry(): bool
    {
        return $this->entry && $this->entry->exists;
    }

    public function mergeConfig(array $config)
    {
        $this->config = array_merge($this->config, $config);
    }

    public function getResource(): ?Resource
    {
        return $this->resource;
    }

    public function setResource(Resource $resource)
    {
        if ($this->isBuilt()) {
            throw new PlatformException('Can not set resource after field is built');
        }
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

    public function makeRules()
    {
        if (! $entry = $this->getResourceEntry()) {
            throw new PlatformException('Can not make rules without an entry');
        }

        $rules = [];
        foreach ($this->rules as $rule) {
            if (starts_with($rule, 'unique:')) {
                $rule = str_replace('{entry.id}', $entry->exists ? $entry->id : 'NULL', $rule);
            }
            $rules[] = $rule;
        }

        if (! $this->isRequired()) {
            $rules[] = 'nullable';
        } elseif (! $this->resourceExists()) {
            $rules[] = 'sometimes';
        }

        return $rules;
    }

    public function mergeRules(array $rules)
    {
        $this->rules = Rules::make($this->rules)->merge($rules)->get();
//        $this->rules = array_merge($this->rules, $rules);
    }

    public function presentValue()
    {
        return $this->getValue();
    }

    public function getValue()
    {
        if (! $this->resourceExists()) {
            return null;
        }

        $value = $this->getResourceEntry()->getAttribute($this->getColumnName());

        if ($accessor = $this->getAccessor()) {
            return $accessor($value);
        }

        return $value;
    }

    public function getValueForValidation()
    {
        return $this->getValue();
    }

    public function setValue($value): ?Closure
    {
        if ($mutator = $this->getMutator()) {
            $value = $mutator($value);
        }

        $this->getResourceEntry()->setAttribute($this->getColumnName(), $value);

        return null;
    }

    public function setValueFromRequest(Request $request)
    {
        return $this->setValue($request->__get($this->getColumnName()));
    }

    public function resourceExists(): bool
    {
        return (bool)$this->resource;
    }

    public function getResourceEntry(): ?ResourceEntryModel
    {
        return $this->resource ? $this->resource->getEntry() : null;
    }

    public function getAccessor(): ?Closure
    {
        return null;
    }

    public function setAccessor(Closure $accessor)
    {
        $this->accessor = $accessor;

        return $this;
    }

    public function getMutator(): ?Closure
    {
        return null;
    }

    public function isUnique(): bool
    {
        return $this->unique;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public static function make($name): self
    {
        return static::fromEntry(new FieldModel([
            'name' => $name,
            'type' => strtolower(class_basename(get_called_class())),
        ]));
    }

    public static function fromEntry(FieldModel $entry): self
    {
        $field = new static($entry);

        $field->hydrate($entry->toArray());
        $field->setRules($entry->getRules()); // @TODO: refactor, very problematic

        return $field;
    }

    public static function resolve($type)
    {
        $class = static::resolveClass($type);

        return new $class(new FieldModel);
    }

    public static function resolveClass($type)
    {
        $base = 'SuperV\Platform\Domains\Resource\Field\Types';

        /** @var \SuperV\Platform\Domains\Resource\Field\Types\FieldType $class */
        $class = $base."\\".studly_case($type);

        return $class;
    }
}