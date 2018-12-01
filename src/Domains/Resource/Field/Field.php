<?php

namespace SuperV\Platform\Domains\Resource\Field;

use Closure;
use Illuminate\Http\Request;
use stdClass;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Database\Model\Contracts\Watcher;
use SuperV\Platform\Domains\Resource\Field\Contracts\Field as FieldContract;
use SuperV\Platform\Domains\Resource\Field\Types\FieldType;
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

    use FieldFlags;

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

    protected $placeholder;

    /** @var Closure */
    protected $mutator;

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

    /**
     * @var \SuperV\Platform\Domains\Database\Model\Contracts\Watcher
     */
    protected $watcher;

    protected $rules;

    protected $alterQueryCallback;

    protected $doesNotInteractWithTable;

    /** @var \SuperV\Platform\Support\Composer\Payload */
    protected $payload;

    protected $flags = [];

    /**
     * @var \SuperV\Platform\Domains\Resource\Resource
     */
    protected $resource;

    public function __construct(array $attributes = [])
    {
//        $this->flags = array_pull($attributes, 'config.flags', []);

        $this->hydrate($attributes);

        $this->uuid = $this->uuid ?? uuid();

        $this->boot();
    }

    protected function boot() { }

    public function getLabel(): string
    {
        return $this->label ?? str_unslug($this->name);
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

        if ($mutator = $this->getMutator('form')) {
            $value = ($mutator)($value, $entry);

            if ($value instanceof Closure) {
                return $value;
            }
        }

        if ($entry && ! $this->doesNotInteractWithTable) {
            $entry->setAttribute($this->getColumnName(), $value);
        }

        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
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
        return $this->columnName ?? $this->name;
//        return $this->fieldType()->getColumnName();
    }

    public function getRules()
    {
        return $this->rules;
//        $fieldTypeRules = $this->fieldType()->makeRules();
//
//        return Rules::make(wrap_array($this->rules))->merge($fieldTypeRules)->get();
    }

    public function resolveFromEntry($entry)
    {
        $attribute = $this->getColumnName();

        if ($entry instanceof EntryContract) {
            return $entry->getAttribute($attribute);
        } elseif ($entry instanceof stdClass) {
            return $entry->{$attribute};
        } elseif (is_array($entry)) {
            return $entry[$attribute];
        }
    }

    public function getPlaceholder()
    {
        return $this->placeholder;
    }

    public function resolveFieldType(): FieldType
    {
        $class = FieldType::resolveClass($this->type);

        return new $class($this);
    }

    public function bindFieldType()
    {
        $type = $this->resolveFieldType();

        $this->columnName = $type->getColumnName();
        $this->doesNotInteractWithTable = $type instanceof DoesNotInteractWithTable;

        if (method_exists($type, 'makeRules')) {
            if ($rules = $type->makeRules()) {
                $this->rules = Rules::make($rules)->merge(wrap_array($this->rules))->get();
            }
        }
        if (method_exists($type, 'mergeConfig')) {
            if ($config = $type->mergeConfig()) {
                $this->config = array_merge($this->config, $config);
            }
        }

        $this->mergeCallbacks($type->getCallbacks());

        return $type;
    }

    public function removeWatcher()
    {
        $this->watcher = null;

        return $this;
    }

    public function uuid(): string
    {
        return $this->uuid;
    }

    public function setPresenter(Closure $callback)
    {
        $this->on('presenting', $callback);
    }

    public function getPresenter($for)
    {
        return $this->getCallback("{$for}.presenting");
    }

    public function getAccessor($for)
    {
        return $this->getCallback("{$for}.accessing");
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
}