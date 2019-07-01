<?php

namespace SuperV\Platform\Domains\Resource\Field;

use Closure;
use Illuminate\Http\Request;
use stdClass;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Database\Model\Contracts\Watcher;
use SuperV\Platform\Domains\Resource\Field\Contracts\Field as FieldContract;
use SuperV\Platform\Domains\Resource\Field\Contracts\HasModifier;
use SuperV\Platform\Domains\Resource\Form\Contracts\Form;
use SuperV\Platform\Support\Concerns\FiresCallbacks;
use SuperV\Platform\Support\Concerns\HasConfig;
use SuperV\Platform\Support\Concerns\Hydratable;

class Field implements FieldContract
{
    use Hydratable;
    use FiresCallbacks;
    use HasConfig;
    use FieldFlags;

    /**
     * @var \SuperV\Platform\Domains\Resource\Field\FieldType
     */
    protected $fieldType;

    /** @var string */
    protected $type;

    /**
     * @var string
     */
    protected $uuid;

    /**
     * @var string
     */
    protected $name;

    protected $columnName;

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

    /**
     * @var \SuperV\Platform\Domains\Database\Model\Contracts\Watcher
     */
    protected $watcher;

    protected $rules;

    protected $alterQueryCallback;

    /** @var \SuperV\Platform\Support\Composer\Payload */
    protected $payload;

    protected $flags = [];

    /**
     * @var \SuperV\Platform\Domains\Resource\Resource
     */
    protected $resource;

    /** @var Form */
    protected $form;

    public function __construct(FieldType $fieldType, array $attributes = [])
    {
        $this->fieldType = $fieldType;
        $this->fieldType->setField($this);

        $this->hydrate($attributes);

        $this->uuid = $this->uuid ?? uuid();

        if (method_exists($this->fieldType, 'makeRules')) {
            if ($rules = $this->fieldType->makeRules()) {
                $this->rules = Rules::make($rules)->merge(wrap_array($this->rules))->get();
            }
        }

        $this->boot();
    }

    protected function boot() { }

    public function getForm(): Form
    {
        return $this->form;
    }

    public function setForm(Form $form): void
    {
        $this->form = $form;
    }

    public function getLabel(): string
    {
//        if ($this->resource) {
//            return sv_trans($this->resource->getAddon().'::'.$this->resource->getHandle().'.'.$this->name, []);
//        }

        return $this->label ?? str_unslug($this->getName());
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

    public function setWatcher(Watcher $watcher)
    {
        $this->watcher = $watcher;

        return $this;
    }

    public function getFieldType(): FieldType
    {
        return $this->fieldType;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getColumnName(): ?string
    {
        if (method_exists($this->fieldType, 'getColumnName')) {
            return $this->fieldType->getColumnName();
        }

        return $this->columnName ?? $this->getName();
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
        return $this->placeholder ?? $this->getLabel();
    }

    public function observe(FieldContract $parent, ?EntryContract $entry = null)
    {
        $parent->setConfigValue('meta.on_change_event', $parent->getName().':'.$parent->getColumnName().'={value}');

        $this->mergeConfig([
            'meta' => [
                'listen_event' => $parent->getName(),
                'autofetch'    => false,
            ],
        ]);

        if ($entry) {
            $this->mergeConfig([
                'meta' => [
                    'query'     => [$parent->getColumnName() => $entry->{$parent->getColumnName()}],
                    'autofetch' => false,
                ],
            ]);
        }
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

    public function uuid(): string
    {
        return $this->uuid;
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

    public function setHint($hint)
    {
        $this->setConfigValue('hint', $hint);
    }

    public function getResource(): \SuperV\Platform\Domains\Resource\Resource
    {
        return $this->resource;
    }

    public function setResource(\SuperV\Platform\Domains\Resource\Resource $resource): void
    {
        $this->resource = $resource;
    }
}