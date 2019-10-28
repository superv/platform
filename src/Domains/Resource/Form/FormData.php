<?php

namespace SuperV\Platform\Domains\Resource\Form;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldTypeInterface as FieldType;
use SuperV\Platform\Domains\Resource\Field\DoesNotInteractWithTable;

class FormData
{
    /** @var array */
    protected $data = [];

    /** @var array */
    protected $dataToValidate = [];

    /** @var array */
    protected $dataToDisplay = [];

    /** @var array */
    protected $dataToSave = [];

    /**
     * @var \SuperV\Platform\Domains\Resource\Form\FormFields
     */
    protected $fields;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $callbacks;

    public function __construct(FormFields $fields, array $data = [])
    {
        $this->fields = $fields;
        $this->data = $data;
    }

    public function get($key = null)
    {
        if ($key) {
            return $this->data[$key] ?? null;
        }

        $keys = $this->fields
            ->visible()
            ->bound()
            ->filter(function (FieldInterface $field) {
                return ! $field->getFieldType() instanceof DoesNotInteractWithTable;
            })
            ->keys();

        $data = \Illuminate\Support\Arr::only($this->data, $keys);

        return array_merge($data, $this->dataToSave);
    }

    public function getForValidation(?EntryContract $entry)
    {
        return array_merge($this->data, $this->dataToValidate);
    }

    public function getForDisplay($key)
    {
        return $this->dataToDisplay[$key] ?? null;
    }

    public function resolveRequest(Request $request, EntryContract $entry)
    {
        $this->fields
            ->visible()
            ->fieldTypes()
            ->map(function (FieldType $fieldType) use ($entry, $request) {
                $fieldType->resolveDataFromRequest($this, $request, $entry);
            });
    }

    public function resolveEntry(EntryContract $entry)
    {
        $this->fields
            ->visible()
            ->fieldTypes()
            ->map(function (FieldType $fieldType) use ($entry) {
                $fieldType->resolveDataFromEntry($this, $entry);
            });
    }

    public function callbacks(): Collection
    {
        if (! $this->callbacks) {
            $this->callbacks = collect();
        }

        return $this->callbacks;
    }

    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function remove($key)
    {
        unset($this->data[$key]);
    }

    public function merge(array $data)
    {
        $this->data = array_merge($this->data, $data);
    }

    public function only(array $keys)
    {
        return array_only($this->data, $keys);
    }

    public function toValidate($key, $value)
    {
        $this->dataToValidate[$key] = $value;
    }

    public function toDisplay($key, $value)
    {
        $this->dataToDisplay[$key] = $value;
    }

    public function toSave($key, $value)
    {
        $this->dataToSave[$key] = $value;
    }
}