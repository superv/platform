<?php

namespace SuperV\Platform\Domains\Resource\Field;

use Closure;
use SuperV\Platform\Domains\Resource\Field\Types\FieldType;
use SuperV\Platform\Domains\Resource\Table\Contracts\AltersTableQuery;
use SuperV\Platform\Support\Concerns\FiresCallbacks;
use SuperV\Platform\Support\Concerns\HasConfig;
use SuperV\Platform\Support\Concerns\Hydratable;

/**
 * Class Field
 * No closures allowed here..
 *
 * @package SuperV\Platform\Domains\Resource\Field
 */
class Field
{
    use Hydratable;
    use FiresCallbacks;
    use HasConfig;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $uuid;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var \SuperV\Platform\Domains\Resource\Field\FieldValue
     */
    protected $value;

    /** @var boolean */
    protected $visible = true;

    /**
     * @var \SuperV\Platform\Domains\Resource\Field\Watcher
     */
    protected $watcher;

    protected $rules;

    protected $unique;

    protected $required;

    protected $alterQueryCallback;

    /**
     * tmp. TODO.ali: remove this
     *
     * @var
     */
    protected $fieldType;

    /**
     * @var \Closure
     */
    protected $fieldTypeResolver;

    public function __construct(array $attributes = [])
    {
        $this->hydrate($attributes);
        $this->boot();
    }

    protected function boot()
    {
        $this->uuid = $this->uuid ?? uuid();

        if ($this->unique) {
            $this->rules[] = 'unique:{resource.handle},'.$this->getName().',{entry.id},id';
        }
        if ($this->required) {
            $this->rules[] = 'required';
        }
    }

    public function build(): self
    {
        $fieldType = $this->resolveType();

        if ($fieldType instanceof AltersTableQuery) {
            $this->alterQueryCallback = $fieldType->alterQueryCallback();
        }

        $this->on('accessing', $fieldType->getAccessor());

        $this->on('mutating', $fieldType->getMutator());

        $this->on('presenting', $fieldType->getPresenter());

        $this->setVisible($fieldType->visible());

        return $this;
    }

    public function resolveType(): FieldType
    {
        if ($this->fieldType) {
            return $this->fieldType;
        }

        if ($resolver = $this->fieldTypeResolver) {
            return $this->fieldType = $resolver($this);
        }

        $class = FieldType::resolveClass($this->type);
        $this->fieldType = new $class([
            'type'     => $this->getType(),
            'name'     => $this->getName(),
            'label'    => $this->getLabel(),
            'config'   => $this->config,
            'rules'    => $this->rules,
            'required' => $this->required,
            'unique'   => $this->unique,

        ]);

        if ($this->watcher) {
//            $fieldType->setEntry($this->watcher);
        }

        return $this->fieldType;
    }

    public function getValue()
    {
        if ($this->hasCallback('accessing')) {
            $callback = $this->getCallback('accessing');

            return $callback($this->value);
        }

        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function updateValue($value)
    {
        if ($mutator = $this->resolveType()->getMutator()) {
            $value = $mutator($value);
        }

        $this->setValue($value);
        if ($this->watcher) {
            $this->watcher->setAttribute($this->getName(), $value);
        }
    }

    public function setWatcher(Watcher $watcher)
    {
        $this->watcher = $watcher;

        return $this;
    }

    public function removeWatcher()
    {
        $this->watcher = null;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLabel(): string
    {
        return $this->label ?? str_unslug($this->name);
    }

    public function compose(): array
    {
        return array_filter([
            'type'   => $this->getType(),
            'uuid'   => $this->uuid(),
            'name'   => $this->getName(),
            'label'  => $this->getLabel(),
            'value'  => $this->getValue(),
            'config' => $this->config,
        ]);
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): Field
    {
        $this->visible = $visible;

        return $this;
    }

    public function getAlterQueryCallback()
    {
        return $this->alterQueryCallback;
    }

    /**
     * @param \Closure $fieldTypeResolver
     */
    public function setFieldTypeResolver(\Closure $fieldTypeResolver): void
    {
        $this->fieldTypeResolver = $fieldTypeResolver;
    }

    public function uuid(): string
    {
        return $this->uuid;
    }
}