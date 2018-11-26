<?php

namespace SuperV\Platform\Domains\Resource\Field;

use Closure;
use Illuminate\Http\Request;
use SuperV\Platform\Contracts\FiresCallbacks as FiresCallbacksContract;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Database\Model\Contracts\Watcher;
use SuperV\Platform\Domains\Resource\Field\Contracts\AltersFieldComposition;
use SuperV\Platform\Domains\Resource\Field\Contracts\Field as FieldContract;
use SuperV\Platform\Domains\Resource\Field\Types\FieldTypeV2;
use SuperV\Platform\Domains\Resource\Table\Contracts\AltersTableQuery;
use SuperV\Platform\Support\Composer\Composable;
use SuperV\Platform\Support\Composer\Composition;
use SuperV\Platform\Support\Composer\Tokens;
use SuperV\Platform\Support\Concerns\FiresCallbacks;
use SuperV\Platform\Support\Concerns\HasConfig;
use SuperV\Platform\Support\Concerns\Hydratable;

/**
 * Class Field
 * No closures allowed here..
 *
 * @package SuperV\Platform\Domains\Resource\Field
 */
class Field implements FieldContract, Composable, FiresCallbacksContract
{
    use Hydratable;
    use FiresCallbacks;
    use HasConfig;

    /**
     * @var string
     */
    protected $type = 'text';

    /** @var EntryContract */
    protected $entry;

    /**
     * @var string
     */
    protected $uuid;

    /**
     * @var string
     */
    protected $name;

    protected $columnName;

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

    protected $unique;

    protected $required;

    protected $alterQueryCallback;

    protected $alterCompositionCallback;

    protected $doesNotInteractWithTable;

    /** @var \SuperV\Platform\Support\Composer\Composition */
    protected $composition;

    protected $flags = [];

    /**
     * @var \SuperV\Platform\Domains\Resource\Resource
     */
    protected $resource;

    public function __construct(array $attributes = [])
    {
        $this->hydrate($attributes);

        $this->uuid = $this->uuid ?? uuid();

        $this->boot();
    }

    protected function boot() { }

    public function compose(Tokens $tokens = null)
    {
        $composition = new Composition([
            'type'   => $this->getType(),
            'uuid'   => $this->uuid(),
            'name'   => $this->getColumnName(),
            'label'  => $this->getLabel(),
            'value'  => $this->getValue(),
            'config' => $this->config,
        ]);

        if ($this->alterCompositionCallback) {
            ($this->alterCompositionCallback)($composition);
        }

        return $composition;
    }

    public function onPresenting(Closure $callback)
    {
        $this->on('presenting', $callback);
    }

    public function getPresenter()
    {
        return $this->getCallback('presenting') ?? $this->presenter;
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
        return $this->alterQueryCallback;
    }

    public function composeForView(EntryContract $entry)
    {
        $value = $this->resolveFromEntry($entry);

        if ($this->accessor) {
            $value = app()->call($this->accessor, ['entry' => $entry, 'value' => $value, 'field' => $this]);
        }

        $composition = (new Composition([
            'type'  => $this->getType(),
            'uuid'  => $this->uuid(),
            'name'  => $this->getColumnName(),
            'label' => $this->getLabel(),
            'value' => $value,
        ]))->setFilterNull(false);

        if ($this->composer) {
            app()->call($this->composer, ['entry' => $entry, 'composition' => $composition]);
        }

        return $composition;
    }

    public function bindFieldType()
    {
        $class = FieldTypeV2::resolveClass($this->type);

        /** @var FieldTypeV2 $type */
        $type = new $class($this);
        $this->columnName = $type->getColumnName();
        $this->mutator = method_exists($type, 'getMutator') ? $type->getMutator() : null;
        $this->accessor = method_exists($type, 'getAccessor') ? $type->getAccessor() : null;
        $this->composer = method_exists($type, 'getComposer') ? $type->getComposer() : null;
        $this->presenter = method_exists($type, 'getPresenter') ? $type->getPresenter() : null;

        $this->doesNotInteractWithTable = $type instanceof DoesNotInteractWithTable;
        if ($type instanceof AltersTableQuery) {
            $this->alterQueryCallback = $type->getAlterQueryCallback();
        }

        if ($type instanceof AltersFieldComposition) {
            $this->alterCompositionCallback = function (Composition $composition) use ($type) {
                return $type->alterComposition($composition);
            };
        }

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

        return $type;
    }

    public function present($value)
    {
        if ($callback = $this->getCallback('presenting')) {
            return $callback($value);
        }

        if ($this->presenter) {
            return ($this->presenter)($value);
        }

        if ($value instanceof EntryContract) {
            return $value->getAttribute($this->getName());
        }

        return $value;
    }

    public function getValue()
    {
        if ($this->accessor) {
            return ($this->accessor)($this->value);
        }

        if ($this->entry) {
            return $this->entry->getAttribute($this->getColumnName());
        }

        return $this->value;
    }

    public function setValue($value, $notify = true)
    {
        if ($this->isHidden()) {
            return null;
        }

        if ($this->mutator) {
            $value = ($this->mutator)($value);

            if ($value instanceof Closure) {
                return $value;
            }
        }

//        elseif ($mutator = $this->fieldType()->getMutator()) {
//            $value = $mutator($value);
//
//            if ($value instanceof Closure) {
//                return $value;
//            }
//        }

        $this->value = $value;

        if ($notify && $this->watcher && ! $this->doesNotInteractWithTable) {
            $this->watcher->setAttribute($this->getColumnName(), $value);
        }
    }

    public function resolveRequestToEntry(Request $request, EntryContract $entry)
    {
        if (! $request->has($this->getColumnName())) {
            return null;
        }

        $value = $request->__get($this->getColumnName());

        if ($this->mutator) {
            $value = ($this->mutator)($value, $entry);

            if ($value instanceof Closure) {
                return $value;
            }
        }

        if (! $this->doesNotInteractWithTable) {
            $entry->setAttribute($this->getColumnName(), $value);
        }

        $this->value = $value;
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
        return $this->doesNotInteractWithTable;
    }

    public function hide(bool $value = true)
    {
        return $this->setFlag('hidden', $value);
    }

    public function getRules()
    {
        return $this->rules;
//        $fieldTypeRules = $this->fieldType()->makeRules();
//
//        return Rules::make(wrap_array($this->rules))->merge($fieldTypeRules)->get();
    }

    public function removeWatcher()
    {
        $this->watcher = null;

        return $this;
    }

    public function isVisible(): bool
    {
        return ! $this->isHidden();
    }

    public function setVisibility(bool $visible): Field
    {
        return $this->setFlag('hidden', ! $visible);
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

    protected function resolveFromEntry(EntryContract $entry)
    {
        return $entry->getAttribute($this->getColumnName());
    }

    public function uuid(): string
    {
        return $this->uuid;
    }

    public function setEntry(EntryContract $entry): FieldContract
    {
        $this->entry = $entry;

        return $this;
    }

    public function getEntry(): EntryContract
    {
        return $this->entry;
    }

    public function hasEntry(): bool
    {
        return ! is_null($this->entry);
    }
}