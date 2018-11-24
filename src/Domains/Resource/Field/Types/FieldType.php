<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use Closure;
use SuperV\Platform\Domains\Resource\Field\Contracts\Field;
use SuperV\Platform\Domains\Resource\Field\FieldModel;
use SuperV\Platform\Domains\Resource\Field\Rules;
use SuperV\Platform\Support\Concerns\FiresCallbacks;
use SuperV\Platform\Support\Concerns\HasConfig;
use SuperV\Platform\Support\Concerns\Hydratable;

abstract class FieldType
{
    use Hydratable;
    use HasConfig;
    use FiresCallbacks;

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

    protected $accessor;

    protected $mutator;

    protected $hasColumn = true;

    public function __construct(array $attributes = [])
    {
        $this->hydrate($attributes);

        $this->boot();
    }

    protected function boot() { }

    public function hasColumn(): bool
    {
        return $this->hasColumn;
    }

    public function visible(): bool
    {
        return true;
    }

    public function build(): self
    {
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getColumnName(): ?string
    {
        return $this->name;
    }

    public function getLabel()
    {
        return $this->label ?: ucwords(str_replace('_', ' ', $this->name));
    }

    public function isUnique(): bool
    {
        return $this->unique;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function getType(): string
    {
        return $this->type;
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
        $rules = [];
        foreach ($this->rules as $rule) {
            if (starts_with($rule, 'unique:')) {
//                $str = ($this->hasEntry() && $this->entryExists()) ? $this->getEntry()->id() : 'NULL';
//                $rule = str_replace('{entry.id}', $str, $rule);
            }
            $rules[] = $rule;
        }

        if (! $this->isRequired()) {
            $rules[] = 'nullable';
        }

        return $rules;
    }

    public function mergeRules(array $rules)
    {
        $this->rules = Rules::make($this->rules)->merge($rules)->get();
//        $this->rules = array_merge($this->rules, $rules);
    }

    public function getValue()
    {
        if (! $this->hasEntry()) {
            return null;
        }

        $value = $this->getEntry()->getAttribute($this->getColumnName());

        if ($accessor = $this->getAccessor()) {
            return $accessor($value);
        }

        return $value;
    }

    public function setValue($value): ?Closure
    {
        if ($mutator = $this->getMutator()) {
            $value = $mutator($value);
        }

        $this->getEntry()->setAttribute($this->getColumnName(), $value);

        return null;
    }

    public function getValueForValidation()
    {
        return $this->getValue();
    }

    public function hasAccessor()
    {
        return ! is_null($this->getAccessor());
    }

    public function getAccessor(): ?Closure
    {
        return $this->accessor;
    }

    public function setAccessor(?Closure $accessor)
    {
        $this->accessor = $accessor;

        return $this;
    }

    public function getMutator(): ?Closure
    {
        return null;
    }

    public function getPresenter(): ?Closure
    {
        return null;
    }

    public static function resolve($type): FieldType
    {
        $class = static::resolveClass($type);

        return new $class;
    }

    public static function resolveClass($type)
    {
        $base = 'SuperV\Platform\Domains\Resource\Field\Types';

        /** @var \SuperV\Platform\Domains\Resource\Field\Types\FieldType $class */
        $class = $base."\\".studly_case($type);

        return $class;
    }
}