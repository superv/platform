<?php

namespace SuperV\Platform\Domains\Resource\Field;

use Closure;
use Event;
use Illuminate\Http\Request;
use stdClass;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface as FieldContract;
use SuperV\Platform\Domains\Resource\Field\Contracts\HasModifier;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface;
use SuperV\Platform\Support\Concerns\FiresCallbacks;
use SuperV\Platform\Support\Concerns\HasConfig;
use SuperV\Platform\Support\Concerns\Hydratable;
use SuperV\Platform\Support\Identifier;

class Field implements FieldContract
{
    use Hydratable;
    use FiresCallbacks;
    use HasConfig;

    /** @var \SuperV\Platform\Domains\Resource\Field\FieldType */
    protected $fieldType;

    /** @var string */
    protected $type;

    /** @var string */
    protected $revisionId;

    /** @var string */
    protected $name;

    /** @var string */
    protected $identifier;

    /** @var string */
    protected $columnName;

    /** @var string */
    protected $placeholder;

    /** @var Closure */
    protected $mutator;

    /** @var Closure */
    protected $modifier;

    /** @var Closure */
    protected $accessor;

    /** @var Closure */
    protected $composer;

    /** @var Closure */
    protected $presenter;

    /**
     * @var string
     */
    protected $label;

    protected $value;

    protected $defaultValue;

    protected $rules;

    protected $alterQueryCallback;

    /** @var \SuperV\Platform\Support\Composer\Payload */
    protected $payload;

    protected $flags = [];

    /**
     * @var \SuperV\Platform\Domains\Resource\Resource
     */
    protected $resource;

    /** @var FormInterface */
    protected $form;

    public function __construct(array $attributes = [])
    {
        $this->hydrate($attributes);

        if ($this->fieldType) {
            $this->fieldType->setField($this);

            if (method_exists($this->fieldType, 'makeRules')) {
                if ($rules = $this->fieldType->makeRules()) {
                    $this->rules = Rules::make($rules)->merge(wrap_array($this->rules))->get();
                }
            }
        }

        $this->uuid = $this->uuid ?? uuid();
    }

    public function identifier(): Identifier
    {
        return sv_identifier($this->getIdentifier());
    }

    public function getLabel(): string
    {
        if ($this->resource) {
            $key = $this->resource->getNamespace().'.resources.'.$this->resource->getIdentifier().'.fields.'.$this->name;
            $value = trans($key);
            if ($value !== $key) {
                return $value['label'] ?? $value;
            }
        }

        $label = __($this->label ?? str_unslug($this->getName()));

        if (is_string($label)) {
            return $label;
        }

        return str_unslug($this->getName());
    }

    public function setLabel(string $label): FieldContract
    {
        $this->label = $label;

        return $this;
    }

    public function resolveRequest(Request $request, ?EntryContract $entry = null)
    {
        if (! $request->has($this->getName())
            && ! $request->has($this->getColumnName())) {
            return null;
        }

        if (! $value = $request->__get($this->getColumnName())) {
            $value = $request->__get($this->getName());
        }

        if ($this->fieldType instanceof HasModifier) {
            $value = (new Modifier($this->fieldType))->set(['entry' => $entry, 'value' => $value]);
        } elseif ($mutator = $this->getMutator('form')) {
            $value = ($mutator)($value, $entry);
        }

        if ($value instanceof Closure) {
            return $value;
        }

        if ($entry && ! $this->doesNotInteractWithTable()) {
            $entry->setAttribute($this->getColumnName(), $value);
        }

        $this->setValue($value);
    }

    public function getValue()
    {
        return $this->value ?? $this->defaultValue;
    }

    public function setValue($value): void
    {
        $this->value = $value;
    }

    public function resolveFromEntry($entry)
    {
        $attribute = $this->getColumnName();

        if ($entry instanceof EntryContract) {
            return $entry->getAttribute($attribute);
        } elseif ($entry instanceof stdClass) {
            return $entry->{$attribute};
        } elseif (is_array($entry)) {
            return $entry[$attribute] ?? null;
        }

        return null;
    }

    public function fillFromEntry(EntryContract $entry)
    {
        $this->value = $this->resolveFromEntry($entry);
    }

    public function getFieldType(): FieldType
    {
        return $this->fieldType;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function getColumnName(): ?string
    {
        return $this->fieldType->getColumnName();
//
//        if (method_exists($this->fieldType, 'getColumnName')) {
//            return $this->fieldType->getColumnName();
//        }
//
//        return $this->columnName ?? $this->getName();
    }

    public function getRules()
    {
        return $this->rules;
    }

    public function removeRules(): FieldContract
    {
        $this->rules = [];

        return $this;
    }

    public function addRule($rule, $message = null): FieldContract
    {
        if ($message) {
            $this->rules[] = ['rule' => $rule, 'message' => $message];
        } else {
            $this->rules[] = $rule;
        }

        return $this;
    }

    public function getPlaceholder()
    {
        return $this->placeholder;
    }

    public function copyToFilters(array $params = []): FieldContract
    {
        if ($params) {
            $this->setConfigValue('filter', $params);
        }

        return $this->addFlag('filter');
    }

    public function displayOrder($order): FieldContract
    {
        return $this->setConfigValue('sort_order', $order);
    }

    public function revisionId(): ?string
    {
        return $this->revisionId;
    }

    public function setPresenter(Closure $callback): FieldContract
    {
        $this->presenter = $callback;

        return $this;
//        $this->on('presenting', $callback);
    }

    public function getComposer($for)
    {
        return $this->getCallback("{$for}.composing");
    }

    public function getMutator($for)
    {
        return $this->getCallback("{$for}.mutating");
    }

    public function getAlterQueryCallback()
    {
        return $this->alterQueryCallback;
    }

    public function addClass(string $class): FieldContract
    {
        $previous = $this->getConfigValue('classes');

        return $this->setConfigValue('classes', trim($class.' '.$previous));
    }

    public function getType(): string
    {
        return $this->fieldType->getType() ?? $this->type;
    }

    public function setType(string $type): FieldContract
    {
        $this->type = $type;

        return $this;
    }

    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    public function setDefaultValue($defaultValue): void
    {
        $this->defaultValue = $defaultValue;
    }

//    public function getResource(): Resource
//    {
//        return $this->resource;
//    }
//
//    public function setResource(\SuperV\Platform\Domains\Resource\Resource $resource): void
//    {
//        $this->resource = $resource;
//    }

    public function setNotRequired()
    {
        $this->removeFlag('required');
    }

    public function addFlag(string $flag): \SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface
    {
        $this->flags[] = $flag;

        return $this;
    }

    public function removeFlag(string $flag): FieldContract
    {
        $this->flags = array_diff($this->flags, [$flag]);

        return $this;
    }

    public function hasFlag(string $flag): bool
    {
        return in_array($flag, $this->flags);
    }


    //////// FLAGS
    ///

    public function showOnIndex(): FieldContract
    {
        return $this->addFlag('table.show');
    }

    public function hide(): FieldContract
    {
        return $this->addFlag('hidden');
    }

    public function isHidden(): bool
    {
        return (bool)$this->hasFlag('hidden');
    }

    public function isUnique()
    {
        return $this->hasFlag('unique');
    }

    public function isRequired()
    {
        return $this->hasFlag('required');
    }

    public function isUnbound()
    {
        return $this->hasFlag('unbound');
    }

    public function doesNotInteractWithTable()
    {
        return $this->fieldType instanceof DoesNotInteractWithTable;
    }

    public function fireEvent($eventName)
    {
        Event::dispatch(sprintf("%s.events:%s", $this->getIdentifier(), $eventName), $this);
    }

    public function searchable(): FieldContract
    {
        return $this->addFlag('searchable');
    }

    public function setHint($hint)
    {
        $this->setConfigValue('hint', $hint);
    }

    public function isFilter()
    {
        return $this->hasFlag('filter');
    }

    public function isVisible(): bool
    {
        return ! $this->isHidden();
    }
}
