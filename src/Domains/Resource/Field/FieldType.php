<?php

namespace SuperV\Platform\Domains\Resource\Field;

use Closure;
use Illuminate\Http\Request;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Driver\DriverInterface;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldTypeInterface;
use SuperV\Platform\Domains\Resource\Field\Contracts\HasModifier;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface;
use SuperV\Platform\Domains\Resource\Form\FormData;
use SuperV\Platform\Domains\Resource\Relation\RelationConfig;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use SuperV\Platform\Exceptions\PlatformException;
use SuperV\Platform\Support\Composer\Payload;

class FieldType implements FieldTypeInterface
{
    /** @var string */
    protected $type;

    /** @var string */
    protected $component;

    /** @var FieldInterface */
    protected $field;

    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $relatedResource;

    /** @var RelationConfig */
    protected $relationConfig;

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

    public function getConfig()
    {
        return $this->field->getConfig();
    }

    public function setConfig(array $config)
    {
        return $this->field->setConfig($config);
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function saving(FormInterface $form)
    {
    }

    public function saved(FormInterface $form)
    {
    }

    public function driverCreating(DriverInterface $driver)
    {
    }

    public function formComposed(Payload $formPayload, FormInterface $form)
    {
    }

    public function fieldComposed(Payload $payload, $context = null)
    {
    }

    public function resolveDataFromEntry(FormData $data, EntryContract $entry)
    {
        if ($callback = $this->field->getCallback('resolving_entry')) {
            $fieldType = $this;

            return app()->call($callback, compact('data', 'entry', 'fieldType'));
        }

        if ($this instanceof DoesNotInteractWithTable) {
            return null;
        }
        $value = $entry->getAttribute($this->getColumnName());

        $data->set($this->getColumnName(), $value);
    }

    public function resolveDataFromRequest(FormData $data, Request $request, ?EntryContract $entry = null)
    {
        if (! $request->has($this->getName()) && ! $request->has($this->getColumnName())) {
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

    public function resolveValueFromRequest(Request $request, ?EntryContract $entry = null)
    {
        if (! $request->has($this->getName()) && ! $request->has($this->getColumnName())) {
            return null;
        }

        if (! $requestValue = $request->__get($this->getColumnName())) {
            $requestValue = $request->__get($this->getName());
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

    public function getComponent(): ?string
    {
        return $this->component;
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

    /**
     * @return \SuperV\Platform\Domains\Resource\Resource
     * @throws \Exception
     */
    protected function getRelatedResource()
    {
        if (! $this->relatedResource) {
            $this->relatedResource = ResourceFactory::make($this->getRelationConfig()->getRelatedResource());
        }

        return $this->relatedResource;
    }

    protected function getRelationConfig(): RelationConfig
    {
        if (! $this->relationConfig) {
            $this->relationConfig = RelationConfig::create($this->field->getType(), $this->field->getConfig());
        }

        return $this->relationConfig;
    }

    protected function getRelatedEntryLabel(?EntryContract $relatedEntry = null)
    {
        if (is_null($relatedEntry)) {
            return null;
        }

        return sv_resource($relatedEntry)->getEntryLabel($relatedEntry);
    }
}
