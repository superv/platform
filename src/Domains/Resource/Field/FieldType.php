<?php

namespace SuperV\Platform\Domains\Resource\Field;

use Closure;
use Illuminate\Http\Request;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldTypeInterface;
use SuperV\Platform\Domains\Resource\Field\Contracts\HasModifier;
use SuperV\Platform\Domains\Resource\Form\FormData;
use SuperV\Platform\Exceptions\PlatformException;

class FieldType implements FieldTypeInterface
{
    protected $type;

    /** @var FieldInterface */
    protected $field;

    protected function boot() { }

    public function __toString()
    {
        return $this->type ?? $this->field->getType();
    }

    public function setField(FieldInterface $field): void
    {
        $this->field = $field;

        $this->boot();
    }

    public function addFlag($flag)
    {
        $this->field->addFlag($flag);
    }

    public function getName()
    {
        return $this->field->getName();
    }

    public function getColumnName()
    {
        return $this->getName();
    }

    public function getConfigValue($key, $default = null)
    {
        return $this->field->getConfigValue($key, $default);
    }

    public function setConfig(array $config)
    {
        return $this->field->setConfig($config);
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function resolveDataFromEntry(FormData $data, EntryContract $entry)
    {
        if ($this instanceof DoesNotInteractWithTable) {
            return;
        }
        $value = $entry->getAttribute($this->getColumnName());

        $data->setDataValue($this->getColumnName(), $value);
    }

    public function resolveDataFromRequest(FormData $data, Request $request, EntryContract $entry)
    {
        if (! $request->has($this->getName()) && ! $request->has($this->getColumnName())) {
            return;
        }

        if (! $value = $request->__get($this->getColumnName())) {
            $value = $request->__get($this->getName());
        }

        if ($this instanceof HasModifier) {
            $value = (new Modifier($this))->set(['entry' => $entry, 'value' => $value]);
        }

        if ($value instanceof Closure) {
            $data->callbacks()->push($value);
        } else {
            $data->setDataValue($this->getColumnName(), $value);
        }
    }

    public static function resolveType($type)
    {
        $class = static::resolveTypeClass($type);
        if (! class_exists($class)) {
            PlatformException::fail("Can not resolve field type from [".$class."]");
        }

        return new $class;
    }

    public static function resolveTypeClass($type)
    {
        $base = 'SuperV\Platform\Domains\Resource\Field\Types';

        $class = $base."\\".studly_case($type.'_field');

        // custom directory
        if (! class_exists($class)) {
            $class = $base."\\".studly_case($type)."\\".studly_case($type.'_field');
        }

        return $class;
    }
}
