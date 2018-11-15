<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use Closure;
use Illuminate\Http\Request;
use SuperV\Platform\Domains\Resource\Contracts\NeedsEntry;
use SuperV\Platform\Domains\Resource\Field\Field;
use SuperV\Platform\Domains\Resource\Field\FieldModel;
use SuperV\Platform\Domains\Resource\Field\Rules;
use SuperV\Platform\Domains\Resource\Model\ResourceEntryModel;
use SuperV\Platform\Support\Concerns\FiresCallbacks;
use SuperV\Platform\Support\Concerns\HasConfig;
use SuperV\Platform\Support\Concerns\Hydratable;

abstract class FieldType implements NeedsEntry
{
    use Hydratable;
    use HasConfig;
    use FiresCallbacks;

    /**
     * @var \SuperV\Platform\Domains\Resource\Resource
     */
    protected $resource;

    /** @var \SuperV\Platform\Domains\Resource\Model\ResourceEntry */
    protected $entry;

    /**
     * @var \SuperV\Platform\Domains\Resource\Field\Field
     */
    protected $field;

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

    /** @var \SuperV\Platform\Domains\Resource\Field\FieldValue */
    protected $value;

    protected $accessor;

    protected $mutator;

    protected $hasColumn = true;

    public function __construct(array $attributes = [])
    {
        $this->hydrate($attributes);
    }

    public function hasColumn(): bool
    {
        return $this->hasColumn;
    }

    public function visible(): bool
    {
        return true;
    }

    public function buildForView($query)
    {
        return $this;
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
//
//    public function getFieldEntry(): ?FieldModel
//    {
//        return null;
//    }
//
//    public function hasFieldEntry(): bool
//    {
//        return false;
////        return $this->fieldEntry && $this->fieldEntry->exists;
//    }

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
                $str = ($this->hasEntry() && $this->entryExists()) ? $this->getEntry()->id() : 'NULL';
                $rule = str_replace('{entry.id}', $str, $rule);
            }
            $rules[] = $rule;
        }

        if (! $this->isRequired()) {
            $rules[] = 'nullable';
        } elseif (! $this->entryExists()) {
//            $rules[] = 'sometimes';
        }

        return $rules;
    }

    public function mergeRules(array $rules)
    {
        $this->rules = Rules::make($this->rules)->merge($rules)->get();
//        $this->rules = array_merge($this->rules, $rules);
    }

    public function getEntry(): ?ResourceEntryModel
    {
        return $this->entry ? $this->entry->getEntry() : null;
    }

    public function setEntry(\SuperV\Platform\Domains\Resource\Model\ResourceEntry $entry): FieldType
    {
        $this->entry = $entry;

        return $this;
    }

    public function entryExists()
    {
        return optional($this->entry)->exists();
    }

    public function hasEntry()
    {
        return ! is_null($this->entry);
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

    public function setValueFromRequest(Request $request)
    {
        return $this->setValue($request->__get($this->getColumnName()));
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

    public function getPresentingCallback(): ?Closure {
        return null;
    }

//    public static function make($name): self
//    {
//        return static::fromEntry(new FieldModel([
//            'name' => $name,
//            'type' => strtolower(class_basename(get_called_class())),
//        ]));
//    }

    public static function resolveType(FieldModel $fieldEntry): FieldType
    {
        $class = FieldType::resolveClass($fieldEntry->getType());

        return $class::fromEntry($fieldEntry);
    }

    public static function fromField(Field $field)
    {
        $class = FieldType::resolveClass($field->getType());

        /** @var \SuperV\Platform\Domains\Resource\Field\Types\FieldType $fieldType */
        $fieldType = new $class;

        return $fieldType;
    }
//
//    public static function fromEntry(FieldModel $fieldEntry): self
//    {
//        $class = FieldType::resolveClass($fieldEntry->getType());
//
//        $field = new $class($fieldEntry);
//
//        $field->hydrate($fieldEntry->toArray());
//        $field->setRules($fieldEntry->getRules()); // @TODO: refactor, very problematic
//
//        return $field;
//    }

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