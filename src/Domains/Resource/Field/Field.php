<?php

namespace SuperV\Platform\Domains\Resource\Field;

use Closure;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Database\Model\Contracts\Watcher;
use SuperV\Platform\Domains\Resource\Contracts\AcceptsEntry;
use SuperV\Platform\Domains\Resource\Field\Contracts\AltersFieldComposition;
use SuperV\Platform\Domains\Resource\Field\Contracts\Field as FieldContract;
use SuperV\Platform\Domains\Resource\Field\Types\FieldType;
use SuperV\Platform\Domains\Resource\Table\Contracts\AltersTableQuery;
use SuperV\Platform\Support\Composer\Composition;
use SuperV\Platform\Support\Concerns\FiresCallbacks;
use SuperV\Platform\Support\Concerns\HasConfig;
use SuperV\Platform\Support\Concerns\Hydratable;

/**
 * Class Field
 * No closures allowed here..
 *
 * @package SuperV\Platform\Domains\Resource\Field
 */
class Field implements FieldContract
{
    use Hydratable;
    use FiresCallbacks;
    use HasConfig;

    /**
     * @var string
     */
    protected $type = 'text';

    /**
     * @var string
     */
    protected $uuid;

    /**
     * @var string
     */
    protected $name;

    protected $columnName;

    /**
     * @var string
     */
    protected $label;

    protected $value;

    /** @var boolean */
    protected $visible = true;

    /**
     * @var \SuperV\Platform\Domains\Database\Model\Contracts\Watcher
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

    /** @var \SuperV\Platform\Support\Composer\Composition */
    protected $composition;

    protected $flags = [];

    public function __construct(array $attributes = [])
    {
        $this->hydrate($attributes);

        $this->uuid = $this->uuid ?? uuid();

        $this->boot();
    }

    protected function boot() { }

    public function compose(): Composition
    {
        $composition = new Composition([
            'type'   => $this->getType(),
            'uuid'   => $this->uuid(),
            'name'   => $this->getColumnName(),
            'label'  => $this->getLabel(),
            'value'  => $this->getValue(),
            'config' => $this->config,
        ]);

        $fieldType = $this->fieldType();
        if ($fieldType instanceof AltersFieldComposition) {
            $fieldType->alterComposition($composition);
        }

        return $composition;
    }

    public function onPresenting(Closure $callback)
    {
        $this->on('presenting', $callback);
    }

    public function getPresenter()
    {
        return $this->getCallback('presenting');
    }

    public function fieldType(): FieldType
    {
        if ($this->fieldType) {
            return $this->fieldType;
        }

        if ($resolver = $this->fieldTypeResolver) {
            $this->fieldType = $resolver($this);
        } else {
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
        }

        if ($this->watcher && $this->fieldType instanceof AcceptsEntry) {
            $this->fieldType->acceptEntry($this->watcher);
        }

        return $this->fieldType;
    }

    public function present($value)
    {
        if ($callback = $this->getCallback('presenting')) {
            return $callback($value);
        }

        if ($presenter = $this->fieldType()->getPresenter()) {
            return $presenter($value);
        }

        if ($value instanceof EntryContract) {
            return $value->getAttribute($this->getName());
        }

        return $value;
    }

    public function getLabel(): string
    {
        return $this->label ?? str_unslug($this->name);
    }

    public function setLabel(string $label): FieldContract
    {
        $this->label = $label;

        return $this;
    }

    public function getAlterQueryCallback()
    {
        if ($this->fieldType() instanceof AltersTableQuery) {
            return $this->fieldType()->alterQueryCallback();
        }

        return $this->alterQueryCallback;
    }

    public function getValue()
    {
        if ($accessor = $this->fieldType()->getAccessor()) {
            return $accessor($this->value);
        }

        return $this->value;
    }

    public function setValue($value, $notify = true)
    {
        if ($this->isHidden()) {
            return null;
        }

        if ($mutator = $this->fieldType()->getMutator()) {
            $value = $mutator($value);

            if ($value instanceof Closure) {
                return $value;
            }
        }

        $this->value = $value;

        if ($notify && $this->watcher && ! $this->fieldType() instanceof DoesNotInteractWithTable) {
            $this->watcher->setAttribute($this->getColumnName(), $value);
        }
    }

    public function setValueFromWatcher()
    {
        $this->value = $this->watcher->getAttribute($this->getColumnName());
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

    public function getColumnName()
    {
        return $this->fieldType()->getColumnName();
    }

    public function isHidden(): bool
    {
        return $this->getFlag('hidden');
    }

    public function isUnique()
    {
        return $this->unique;
    }

    public function isRequired()
    {
        return $this->required;
    }

    public function doesNotInteractWithTable()
    {
        return $this->fieldType() instanceof DoesNotInteractWithTable;
    }

    public function hide(bool $value = true)
    {
        return $this->setFlag('hidden', $value);
    }

    public function isVisible(): bool
    {
        return ! $this->isHidden();
    }

    public function setVisibility(bool $visible): Field
    {
        return $this->setFlag('hidden', ! $visible);
    }

    public function setFieldTypeResolver(Closure $fieldTypeResolver): void
    {
        $this->fieldTypeResolver = $fieldTypeResolver;
    }

    public function setFlag(string $flag, bool $value): self
    {
        $this->flags[$flag] = $value;

        return $this;
    }

    public function getFlag(string $flag, $default = false): bool
    {
        return $this->flags[$flag] ?? $default;
    }

    public function uuid(): string
    {
        return $this->uuid;
    }

    public function getRules()
    {
        return Rules::make(wrap_array($this->rules))->merge($this->fieldType()->makeRules())->get();
    }
}