<?php

namespace SuperV\Platform\Domains\Resource\Form;

use SuperV\Platform\Contracts\Validator;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Field\Contracts\Field;
use SuperV\Platform\Domains\Resource\Field\Jobs\GetRules;
use SuperV\Platform\Domains\Resource\Form\Contracts\EntryForm as EntryFormContract;
use SuperV\Platform\Domains\Resource\Resource;

class EntryForm extends Form implements EntryFormContract
{
    /** @var Resource */
    protected $resource;

    /** @var EntryContract */
    protected $entry;

    public function getEntry(): ?EntryContract
    {
        return $this->entry;
    }

    public function setEntry(EntryContract $entry): EntryForm
    {
        $this->entry = $entry;

        return $this;
    }

    public function hasEntry(): bool
    {
        return (bool)$this->entry;
    }

    public function setResource(\SuperV\Platform\Domains\Resource\Resource $resource): EntryForm
    {
        $this->resource = $resource;

        return $this;
    }

    public function validate()
    {
        /**
         * @var \SuperV\Platform\Contracts\Validator $validator
         */
        $validator = app(Validator::class);

        $rules = (new GetRules($this->getFields()))->get($this->getEntry());
        $data = $this->entry->getAttributes();
        $attributes = $this->fields
            ->map(function (Field $field) {
                return [$field->getColumnName(), $field->getLabel()];
            })->filter()
            ->toAssoc()
            ->all();

        $validator->make($data, $rules, [], $attributes);
//        ValidateForm::dispatch($this->getFields(), $this->request->all());
    }

    protected function applyExtensionCallbacks(): void
    {
        if ($this->isCreating()) {
            if ($this->resource && $callback = $this->resource->getCallback('creating')) {
                app()->call($callback, ['form' => $this]);
            }
        }

        if ($this->isUpdating()) {
            if ($this->resource && $callback = $this->resource->getCallback('editing')) {
                app()->call($callback, ['form' => $this, 'entry' => $this->getEntry()]);
            }
        }
    }
}
