<?php

namespace SuperV\Platform\Domains\Resource\Field;

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

    public function __construct(array $attributes = [])
    {
        $this->hydrate($attributes);
        $this->boot();
    }

    protected function boot()
    {
        $this->uuid = $this->uuid ?? uuid();

        $this->value = new FieldValue($this);

//        $rules = array_filter($this->rules ?? []);

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

        if ($fieldType->hasAccessor()) {
            $this->on('accessing', $fieldType->getAccessor());
        }

        if ($presenting = $fieldType->getPresentingCallback()) {
            $this->on('presenting', $presenting);
        }

        $this->setVisible($fieldType->visible());

        return $this;
    }

    public function value(): FieldValue
    {
        return $this->value;
    }

    public function resolveType(): FieldType
    {
        $class = FieldType::resolveClass($this->type);
        $fieldType = new $class([
            'type'     => $this->getType(),
            'name'     => $this->getName(),
            'label'    => $this->getLabel(),
            'config'   => $this->config,
            'rules'    => $this->rules,
            'required' => $this->required,
            'unique'   => $this->unique,

        ]);

        if ($this->watcher) {
            $fieldType->setEntry($this->watcher);
        }

        return $fieldType;
    }

    public function tmp_makeRules()
    {
        $rules = array_filter($this->rules ?? []);
        if ($this->isUnique()) {
            $rules[] = 'unique:'.$this->getResourceTable().','.$this->getName().',{entry.id},id';
        }
        if ($this->isRequired()) {
            $rules[] = 'required';
        }

        return $rules;
    }

    public function getValue()
    {
        $value = $this->value->get();

        if ($this->hasCallback('accessing')) {
            $callback = $this->getCallback('accessing');

            return $callback($value);
        }

        return $value;
    }

    public function setValue($value)
    {
        $this->value->set($value);

        if ($this->watcher) {
            $this->watcher->setAttribute($this->getName(), $this->value->get());
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

    public function setValueFromWatcher()
    {
        $value = $this->watcher->getAttribute($this->getName());
        $this->setValue($value);
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
            'type'  => $this->getType(),
            'uuid'  => $this->uuid(),
            'name'  => $this->getName(),
            'label' => $this->getLabel(),
            'value' => $this->getValue(),
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

    public function uuid(): string
    {
        return $this->uuid;
    }
}