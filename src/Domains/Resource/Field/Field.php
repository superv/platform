<?php

namespace SuperV\Platform\Domains\Resource\Field;

use Closure;
use Event;
use Illuminate\Contracts\Container\Container;
use SuperV\Platform\Domains\Resource\Field\Composer\DefaultFieldComposer;
use SuperV\Platform\Domains\Resource\Field\Contracts\ComposerInterface;
use SuperV\Platform\Domains\Resource\Field\Contracts\DoesNotInteractWithTable;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldQueryInterface;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldTypeInterface;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldValueInterface;
use SuperV\Platform\Domains\Resource\Field\Contracts\MutatorInterface;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface;
use SuperV\Platform\Support\Concerns\FiresCallbacks;
use SuperV\Platform\Support\Concerns\Hydratable;
use SuperV\Platform\Support\Identifier;

class Field implements FieldInterface
{
    use Hydratable;
    use FiresCallbacks;

    /** @var \SuperV\Platform\Domains\Resource\Field\FieldType */
    protected $fieldType;

    /** @var string */
    protected $type;

    /** @var string */
    protected $revisionId;

    /** @var string */
    protected $handle;

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

    protected $config = [];

    /**
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function init(array $attributes = []): FieldInterface
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

//        $this->uuid = $this->uuid ?? uuid();

        return $this;
    }

    public function identifier(): Identifier
    {
        return sv_identifier($this->getIdentifier());
    }

    public function getLabel(): string
    {
//        if ($this->resource) {
//            $key = $this->resource->getNamespace().'.resources.'.$this->resource->getIdentifier().'.fields.'.$this->name;
//            $value = trans($key);
//            if ($value !== $key) {
//                return $value['label'] ?? $value;
//            }
//        }

//        return __($this->label);

        $label = __($this->label ?? str_unslug($this->getHandle()));

//
        return $label;
//
//        if (is_string($label)) {
//            return $label;
//        }
//
//        return str_unslug($this->getName());
    }

    public function setLabel(string $label): FieldInterface
    {
        $this->label = $label;

        return $this;
    }

    public function beforeResolvingEntry(Closure $callback): FieldInterface
    {
        $this->callbacks['resolving_entry'] = $callback;

        return $this;
    }

    public function beforeResolvingRequest(Closure $callback): FieldInterface
    {
        $this->callbacks['resolving_request'] = $callback;

        return $this;
    }

    public function beforeSaving(Closure $callback): FieldInterface
    {
        $this->callbacks['before_saving'] = $callback;

        return $this;
    }

    public function beforeCreating(Closure $callback): FieldInterface
    {
        $this->callbacks['before_creating'] = $callback;

        return $this;
    }

    public function beforeUpdating(Closure $callback): FieldInterface
    {
        $this->callbacks['before_updating'] = $callback;

        return $this;
    }

    public function beforeValidating(Closure $callback): FieldInterface
    {
        $this->callbacks['before_validating'] = $callback;

        return $this;
    }

    public function value(): FieldValueInterface
    {
        return $this->getValue();
    }

    public function getValue(): FieldValueInterface
    {
        if ($fieldValue = $this->fieldType->resolveFieldValue()) {
            return $fieldValue;
        }

        return $this->container->make(FieldValueInterface::class)->setField($this);
    }

    public function getFieldType(): FieldTypeInterface
    {
        return $this->fieldType;
    }

    public function type(): FieldTypeInterface
    {
        return $this->getFieldType();
    }

    public function getHandle(): string
    {
        return $this->handle;
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

    public function removeRules(): FieldInterface
    {
        $this->rules = [];

        return $this;
    }

    public function addRule($rule, $message = null): FieldInterface
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

    public function copyToFilters(array $params = []): FieldInterface
    {
        if ($params) {
            $this->setConfigValue('filter', $params);
        }

        return $this->addFlag('filter');
    }

    public function displayOrder($order): FieldInterface
    {
        return $this->setConfigValue('sort_order', $order);
    }

    public function setPresenter(Closure $callback): FieldInterface
    {
        $this->presenter = $callback;

        return $this;
//        $this->on('presenting', $callback);
    }

    public function getAlterQueryCallback()
    {
        return $this->alterQueryCallback;
    }

    public function addClass(string $class): FieldInterface
    {
        $previous = $this->getConfigValue('classes');

        return $this->setConfigValue('classes', trim($class.' '.$previous));
    }

    public function getType(): string
    {
        if (! $this->fieldType->getHandle()) {
            dd(get_called_class(), $this->fieldType);
        }

        return $this->fieldType->getHandle();
    }

    public function getComponent(): ?string
    {
        return $this->fieldType->getComponent();
    }

    public function setType(string $type): FieldInterface
    {
        $this->type = $type;

        return $this;
    }

    public function getDefaultValue()
    {
        return $this->config['default_value'] ?? null;
    }

    public function setNotRequired()
    {
        $this->removeFlag('required');
    }

    public function addFlag(string $flag): \SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface
    {
        $this->flags[] = $flag;

        return $this;
    }

    public function removeFlag(string $flag): FieldInterface
    {
        $this->flags = array_diff($this->flags, [$flag]);

        return $this;
    }

    public function hasFlag(string $flag): bool
    {
        return in_array($flag, $this->flags);
    }

    public function getComposer(): ComposerInterface
    {
        return $this->getFieldType()->resolveComposer();
    }

    /// config
    public function getConfigValue($key, $default = null)
    {
        return array_get($this->getConfig(), $key, $default);
    }

    public function setConfigValue($key, $value = null): FieldInterface
    {
        if (! is_null($value)) {
            array_set($this->config, $key, $value);
        }

        return $this;
    }

    public function getConfig(): array
    {
        return $this->config ?: [];
    }

    public function mergeConfig(array $config): FieldInterface
    {
        $this->config = array_replace_recursive($this->config, $config);

        return $this;
    }

    public function showOnIndex(): FieldInterface
    {
        return $this->addFlag('table.show');
    }

    //////// FLAGS
    ///

    public function hide(): FieldInterface
    {
        return $this->addFlag('hidden');
    }

    public function isHidden(): bool
    {
        return (bool)$this->hasFlag('hidden');
    }

    public function isHiddenOnView(): bool
    {
        return (bool)$this->hasFlag('view.hide');
    }

    public function isUnique()
    {
        return $this->hasFlag('unique');
    }

    public function isRequired()
    {
        return $this->hasFlag('required');
    }

    public function readOnly(): FieldInterface
    {
        return $this->setConfigValue('meta.disabled', true);
    }

    public function isUnbound()
    {
        return $this->hasFlag('unbound');
    }

    public function doesNotInteractWithTable()
    {
        return $this->fieldType instanceof DoesNotInteractWithTable;
    }

    public function searchable(): FieldInterface
    {
        return $this->addFlag('searchable');
    }

    public function isFilter()
    {
        return $this->hasFlag('filter');
    }

    public function isVisible(): bool
    {
        return ! $this->isHidden();
    }

    public function revisionId(): ?string
    {
        return $this->revisionId;
    }

    public function fireEvent($eventName)
    {
        Event::dispatch(sprintf("%s.events:%s", $this->getIdentifier(), $eventName), $this);
    }

    public function setHint($hint)
    {
        $this->setConfigValue('hint', $hint);
    }

    public function setFieldType(\SuperV\Platform\Domains\Resource\Field\FieldType $fieldType): Field
    {
        $this->fieldType = $fieldType;

        return $this;
    }
}
