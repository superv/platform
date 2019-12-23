<?php

namespace SuperV\Platform\Domains\Resource\Field;

use Closure;
use Illuminate\Http\Request;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Contracts\DataMapInterface;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldValueInterface;

class FieldValue implements FieldValueInterface
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface
     */
    protected $field;

    protected $value;

    protected $requestValue;

    /** @var \SuperV\Platform\Domains\Database\Model\Contracts\EntryContract */
    protected $entry;

    /** @var \Illuminate\Http\Request */
    protected $request;

    protected $mappable = true;

    public function get()
    {
        return $this->value;
    }

    public function resolve(): FieldValueInterface
    {
        if ($this->entry) {
            $this->resolveEntry();
        }

        if ($this->request) {
            $this->resolveRequest();
        }

        return $this;
    }

    public function mapTo(DataMapInterface $dataMap): FieldValueInterface
    {
        if ($this->mappable) {
            $value = $this->get();

            if ($value instanceof Closure) {
                $dataMap->callbacks()->push($value);
                $dataMap->toValidate($this->field->getColumnName(), $this->requestValue);
            }

            $dataMap->set($this->field->getColumnName(), $value);
        }

        return $this;
    }

    public function setField(FieldInterface $field): FieldValueInterface
    {
        $this->field = $field;

        return $this;
    }

    public function set($value): FieldValueInterface
    {
        $this->value = $value;

        return $this;
    }

    public function setEntry(?EntryContract $entry): FieldValueInterface
    {
        $this->entry = $entry;

        return $this;
    }

    public function setRequest(Request $request): FieldValueInterface
    {
        $this->request = $request;

        return $this;
    }

    public static function of(FieldInterface $field): FieldValueInterface
    {
        return app(static::class)->setField($field);
    }

    protected function getFieldHandle(): string
    {
        return $this->field->getHandle();
    }

    protected function resolveRequest(): void
    {
        if (! $this->request->has($this->field->getHandle()) && ! $this->request->has($this->field->getColumnName())) {
            $this->mappable = false;
        } else {
            if (! $this->requestValue = $this->request->__get($this->field->getColumnName())) {
                $this->requestValue = $this->request->__get($this->field->getHandle());
            }
            $this->value = $this->requestValue;

//                if ($this->field->getFieldType() instanceof HasModifier) {
//                    $this->value = (new Modifier($this->field->getFieldType()))->set(['entry' => $this->entry,
//                                                                                      'value' => $this->requestValue]);
//                }

            if ($callback = $this->field->getCallback('resolving_request')) {
                $this->value = app()->call($callback, ['request' => $this->request, 'value' => $this->value]);
            }
        }
    }

    protected function resolveEntry(): void
    {
        if ($callback = $this->field->getCallback('resolving_entry')) {
            app()->call($callback, ['entry' => $this->entry, 'fieldValue' => $this]);
        } else {
            $this->value = $this->entry->getAttribute($this->field->getColumnName());
        }
    }
}