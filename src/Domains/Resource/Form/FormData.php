<?php

namespace SuperV\Platform\Domains\Resource\Form;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldTypeInterface as FieldType;

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

    public function __construct(FormFields $fields)
    {
        $this->fields = $fields;
    }

    public function get($key = null)
    {
        if ($key) {
            return $this->data[$key] ?? null;
        }

        $keys = $this->fields
            ->visible()
            ->bound()
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