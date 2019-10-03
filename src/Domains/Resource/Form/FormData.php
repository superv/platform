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

    /**
     * @var \SuperV\Platform\Domains\Resource\Form\FormFieldCollection
     */
    protected $fields;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $callbacks;

    public function __construct(FormFieldCollection $fields)
    {
        $this->fields = $fields;
    }

    public function get()
    {
        return $this->data;
    }

    public function getForValidation(EntryContract $entry)
    {
        return $this->data;
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

    public function setDataValue($key, $value)
    {
        $this->data[$key] = $value;
    }
}