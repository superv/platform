<?php

namespace SuperV\Platform\Domains\Resource\Field;

use Closure;
use SuperV\Platform\Support\Concerns\FiresCallbacks;
use SuperV\Platform\Support\Concerns\HasConfig;
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

    /** @var Closure */
    protected $accessor;

    /**
     * @var \SuperV\Platform\Domains\Resource\Field\Watcher
     */
    protected $watcher;

    /** @var boolean */
    protected $hasDatabaseColumn;

    protected $columnName;

    protected $sid;

    protected function __construct()
    {
        $this->sid = md5(uniqid());
    }

    protected function boot()
    {
        $this->uuid = $this->uuid ?? uuid();

        $this->value = new FieldValue($this);
    }

    public function value(): FieldValue
    {
        return $this->value;
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

    public function uuid(): string
    {
        return $this->uuid;
    }

    public function getColumnName(): string
    {
        return $this->columnName;
    }

    public function setColumnName($columnName)
    {
        $this->columnName = $columnName;

        return $this;
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

    public function hasDatabaseColumn(): bool
    {
        return $this->hasDatabaseColumn;
    }

    public function setHasDatabaseColumn($doesIt)
    {
        $this->hasDatabaseColumn = $doesIt;

        return $this;
    }

    public static function make(array $params): self
    {
        $field = new static;
        $config = array_pull($params, 'config');
        $rules = array_pull($params, 'rules');

        // @TODO:fix
        if (is_string($config)) {
            $params['config'] = json_decode($config, true);
        }
        if (is_string($rules)) {
            $params['rules'] = json_decode($rules, true);
        }

        $field->hydrate($params);
        $field->boot();

        return $field;
    }
}