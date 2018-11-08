<?php

namespace SuperV\Platform\Domains\Resource\Field;

use Closure;
use Exception;
use Illuminate\Http\Request;
use SuperV\Platform\Domains\Resource\Contracts\NeedsEntry;
use SuperV\Platform\Domains\Resource\Model\ResourceEntryModel;
use SuperV\Platform\Exceptions\PlatformException;
use SuperV\Platform\Support\Concerns\FiresCallbacks;
use SuperV\Platform\Support\Concerns\HasConfig;
use SuperV\Platform\Support\Concerns\Hydratable;

abstract class Field implements NeedsEntry
{
    use Hydratable;
    use HasConfig;
    use FiresCallbacks;

    /**
     * @var \SuperV\Platform\Domains\Resource\Resource
     */
    protected $resource;

    /** @var \SuperV\Platform\Domains\Resource\Model\Entry */
    protected $entry;

    /**
     * @var \SuperV\Platform\Domains\Resource\Field\FieldModel
     */
    protected $fieldEntry;

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

    /** @var \SuperV\Platform\Domains\Resource\Field\FieldValue */
    protected $value;

    public function __construct(FieldModel $entry)
    {
        $this->fieldEntry = $entry;
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
            return static::fromEntry($this->fieldEntry->fresh());
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
        return $this->fieldEntry->uuid();
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

    public function getFieldEntry(): ?FieldModel
    {
        return $this->fieldEntry;
    }

    public function hasFieldEntry(): bool
    {
        return $this->fieldEntry && $this->fieldEntry->exists;
    }

    public function mergeConfig(array $config)
    {
        $this->config = array_merge($this->config, $config);
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
//        if (! $entry = $this->getEntry()) {
//            throw new PlatformException('Can not make rules without an entry');
//        }



        $rules = [];
        foreach ($this->rules as $rule) {
            if (starts_with($rule, 'unique:')) {
                $str = ($this->hasEntry() && $this->entryExists()) ? $this->getEntry()->getId() : 'NULL';
                $rule = str_replace('{entry.id}', $str, $rule);
            }
            $rules[] = $rule;
        }

        if (! $this->isRequired()) {
            $rules[] = 'nullable';
        } elseif (! $this->entryExists()) {
            $rules[] = 'sometimes';
        }

        return $rules;
    }

    public function mergeRules(array $rules)
    {
        $this->rules = Rules::make($this->rules)->merge($rules)->get();
//        $this->rules = array_merge($this->rules, $rules);
    }

    public function setEntry(\SuperV\Platform\Domains\Resource\Model\Entry $entry): Field
    {
        $this->entry = $entry;

        return $this;
    }

    public function getEntry(): ?ResourceEntryModel
    {
        return $this->entry ? $this->entry->getEntry() : null;
    }

    public function entryExists()
    {
        return optional($this->entry)->exists();
    }

    public function hasEntry()
    {
        return !is_null($this->entry);
    }

    public function presentValue()
    {
        return $this->getValue();
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

    public function getValueForValidation()
    {
        return $this->getValue();
    }

    public function setValue($value): ?Closure
    {
        if ($mutator = $this->getMutator()) {
            $value = $mutator($value);
        }

        $this->getEntry()->setAttribute($this->getColumnName(), $value);

        return null;
    }

    public function setValueFromRequest(Request $request)
    {
        return $this->setValue($request->__get($this->getColumnName()));
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