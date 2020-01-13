<?php

namespace SuperV\Platform\Domains\Resource\Field;

use Closure;
use Illuminate\Http\Request;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Builder\FieldBlueprint;
use SuperV\Platform\Domains\Resource\Driver\DatabaseDriver;
use SuperV\Platform\Domains\Resource\Driver\DriverInterface;
use SuperV\Platform\Domains\Resource\Field\Contracts\ComposerInterface;
use SuperV\Platform\Domains\Resource\Field\Contracts\DoesNotInteractWithTable;
use SuperV\Platform\Domains\Resource\Field\Contracts\FakerInterface;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldTypeInterface;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldValueInterface;
use SuperV\Platform\Domains\Resource\Field\Contracts\HasModifier;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface;
use SuperV\Platform\Domains\Resource\Form\FormData;
use SuperV\Platform\Exceptions\PlatformException;
use SuperV\Platform\Support\Composer\Payload;

abstract class FieldType implements FieldTypeInterface
{
    /** @var string */
    protected $handle;

    /** @var string */
    protected $component;

    /** @var FieldInterface */
    protected $field;

    protected static $registry = [];

    protected function boot() { }

    public function __toString()
    {
        return $this->handle ?? $this->field->getType();
    }

    public function setField(FieldInterface $field): FieldTypeInterface
    {
        $this->field = $field;

        $this->boot();

        return $this;
    }

    public function addFlag($flag)
    {
        $this->field->addFlag($flag);
    }

    public function getFieldHandle()
    {
        return $this->field->getHandle();
    }

    public function getColumnName()
    {
        return $this->getFieldHandle();
    }

    public function getConfigValue($key, $default = null)
    {
        return $this->field->getConfigValue($key, $default);
    }

    public function getConfig()
    {
        return $this->field->getConfig();
    }

    public function getHandle(): ?string
    {
        return $this->handle;
    }

    public function saving(FormInterface $form)
    {
    }

    public function saved(FormInterface $form)
    {
    }

    public function formComposed(Payload $formPayload, FormInterface $form)
    {
    }

    public function fieldComposed(Payload $payload, $context = null)
    {
    }

    public function getComponent(): ?string
    {
        return $this->component;
    }

    public function resolveComposer(): ComposerInterface
    {
        $class = str_replace_last(class_basename(get_called_class()), 'Composer', get_called_class());

        if (! class_exists($class)) {
            $class = FieldComposer::class;
        }

        return app($class)->setField($this->field);
    }

    public function resolveFaker(): ?FakerInterface
    {
        $className = class_basename(get_called_class());

        $class = str_replace_last($className, 'Faker', get_called_class());

        if (class_exists($class)) {
            return app($class);
        }

        return null;
    }

    public function resolveController()
    {
        $className = class_basename(get_called_class());

        $class = str_replace_last($className, 'Controller', get_called_class());

        if (class_exists($class)) {
            return app($class);
        }

        return null;
    }

    public function resolveFieldValue(): ?FieldValueInterface
    {
        $class = str_replace_last(class_basename(get_called_class()), 'Value', get_called_class());

        if (class_exists($class)) {
            return $class::of($this->field);
        }

        return null;
    }

    public function handleDriver(DriverInterface $driver, FieldBlueprint $blueprint)
    {
        if ($driver instanceof DatabaseDriver) {
            $options = [
                'Notnull' => ! $blueprint->hasFlag('nullable'),
                'default' => $blueprint->getDefaultValue(),
            ];
            $this->handleDatabaseDriver($driver, $blueprint, array_filter_null($options));
        }
    }

    public function ____resolveDataFromEntry(FormData $data, EntryContract $entry)
    {
        if ($callback = $this->field->getCallback('resolving_entry')) {
            $fieldType = $this;

            return app()->call($callback, compact('data', 'entry', 'fieldType'));
        }

        if ($this instanceof DoesNotInteractWithTable) {
            return null;
        }
        $this->field->getValue()->resolve($entry)->mapTo($data);
    }

    public function ___resolveValueFromRequest(Request $request, ?EntryContract $entry = null)
    {
        if (! $request->has($this->getFieldHandle()) && ! $request->has($this->getColumnName())) {
            return null;
        }

        if (! $requestValue = $request->__get($this->getColumnName())) {
            $requestValue = $request->__get($this->getFieldHandle());
        }

        $value = $requestValue;
        if ($this instanceof HasModifier) {
            $value = (new Modifier($this))->set(['entry' => $entry, 'value' => $requestValue]);
        }

        if ($callback = $this->field->getCallback('resolving_request')) {
            $value = app()->call($callback, ['request' => $request, 'value' => $value]);
        }

        return [$value, $requestValue];
    }

    public function ___resolveDataFromRequest(FormData $data, Request $request, ?EntryContract $entry = null)
    {
        if (! $request->has($this->getFieldHandle()) && ! $request->has($this->getColumnName())) {
            return null;
        }

        [$value, $requestValue] = $this->resolveValueFromRequest($request, $entry);

        if ($value instanceof Closure) {
            $data->callbacks()->push($value);
            $data->toValidate($this->getColumnName(), $requestValue);
        } else {
            $data->set($this->getColumnName(), $value);
        }
    }

    public function mapValueFromEntry(FormData $data, EntryContract $entry)
    {
        $value = $entry->getAttribute($this->getColumnName());
        $data->set($this->getColumnName(), $value);

        return $value;
    }

    public function handleDatabaseDriver(DatabaseDriver $driver, FieldBlueprint $blueprint, array $options = [])
    {
    }

    public static function register($class)
    {
        /** @var FieldTypeInterface $instance */
        $instance = $class::resolve();
        static::$registry[$instance->getHandle()] = $class;
    }

    public static function resolveType($type): FieldTypeInterface
    {
        $class = static::resolveTypeClass($type);
        if (! class_exists($class)) {
            PlatformException::fail("Can not resolve field type from [".$class."]");
        }

        return $class::resolve();
    }

    public static function resolveTypeClass($type)
    {
        if ($class = static::$registry[$type] ?? null) {
            return $class;
        }
        $base = 'SuperV\Platform\Domains\Resource\Field\Types';

        $class = $base."\\".studly_case($type.'_field');

        // custom directory
        if (! class_exists($class)) {
            $class = $base."\\".studly_case($type)."\\".studly_case($type.'_field');
            // with type suffix
            if (! class_exists($class)) {
                $class = $base."\\".studly_case($type)."\\".studly_case($type.'_type');
            }
        }

        return $class;
    }

    /** * @return static */
    public static function resolve()
    {
        return app(static::class);
    }
}
