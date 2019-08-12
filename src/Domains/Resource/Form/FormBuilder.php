<?php

namespace SuperV\Platform\Domains\Resource\Form;

use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Field\FieldModel;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\ResourceFactory;

class FormBuilder
{
    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $resource;

    /** @var \SuperV\Platform\Domains\Database\Model\Contracts\EntryContract */
    protected $entry;

    public function build(): Form
    {
        $form = new Form();

        if (! $this->resource && $this->entry) {
            $this->resource = ResourceFactory::make($this->entry);
            $form->setEntry($this->entry);
        }

        if ($this->resource && ! $this->entry) {
            $form->setEntry($this->resource->newEntryInstance());
        }

        if ($this->resource) {
//            $form->setFields($this->resource->getFields());

            $form->setFields($this->buildFields($this->resource->getFieldEntries()));
            $form->setResource($this->resource);
            $form->setIdentifier($this->resource->config()->getResourceKey());
        }

        return $form;
    }

    public function buildFields(Collection $fields)
    {
        $fields = $fields->map(function (FieldModel $field) {
            $field = FieldFactory::createFromEntry($field, FormField::class);

            if ($this->resource) {
                $field->setResource($this->resource);
            }

            return $field;
        });

        return $fields;
    }

    public function getResource(): Resource
    {
        return $this->resource;
    }

    public function setResource(?Resource $resource): FormBuilder
    {
        if ($resource) {
            $this->resource = $resource;
        }

        return $this;
    }

    public function getEntry(): EntryContract
    {
        return $this->entry;
    }

    public function setEntry(?EntryContract $entry = null): FormBuilder
    {
        $this->entry = $entry;

        return $this;
    }

    public static function buildFromEntry(EntryContract $entry): Form
    {
        return (new static)->setEntry($entry)->build();
    }

    public static function buildFromResource(Resource $resource): Form
    {
        return (new static())->setResource($resource)->build();
    }
}