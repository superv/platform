<?php

namespace SuperV\Platform\Domains\Resource\Field;

use Closure;
use SuperV\Platform\Support\Concerns\FiresCallbacks;
use SuperV\Platform\Support\Concerns\Hydratable;

/**
 * Class Field  IMMUTABLE!!!!!!!!
 *
 * @package SuperV\Platform\Domains\Resource\Field
 */
class Field
{
    use Hydratable;
    use FiresCallbacks;

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

    /** @var Closure */
    protected $accessor;

    /**
     * @var \SuperV\Platform\Domains\Resource\Field\Watcher
     */
    protected $watcher;

    protected function __construct()
    {
    }

    protected function boot()
    {
        $this->uuid = $this->uuid ?? uuid();

        $this->value = new FieldValue($this);
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

    public function value(): FieldValue
    {
        return $this->value;
    }

    public function getValue()
    {
        $value = $this->value->get();

        if ($this->hasCallback('accessing')) {
            $this->setAccessor($this->getCallback('accessing'));
        }

        if ($accessor = $this->getAccessor()) {
            return $accessor($value);
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
        return [
            'type'  => $this->getType(),
            'uuid'  => $this->uuid(),
            'name'  => $this->getName(),
            'label' => $this->getLabel(),
            'value' => $this->getValue(),
        ];
    }

    public function uuid(): string
    {
        return $this->uuid;
    }

    public static function make(array $params): self
    {
        $field = new static;
        $field->hydrate($params);
        $field->boot();

        return $field;
    }
}