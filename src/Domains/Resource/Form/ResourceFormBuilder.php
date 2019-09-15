<?php

namespace SuperV\Platform\Domains\Resource\Form;

use Illuminate\Support\Collection;
use SuperV\Platform\Contracts\Dispatcher;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Field\FieldModel;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\ResourceFactory;

class ResourceFormBuilder
{
    /** @var \SuperV\Platform\Domains\Resource\Form\FormModel */
    protected $formEntry;

    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $resource;

    /** @var \SuperV\Platform\Domains\Database\Model\Contracts\EntryContract */
    protected $entry;

    /**
     * @var \SuperV\Platform\Contracts\Dispatcher
     */
    protected $dispatcher;

    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function build(): Form
    {
        $form = new Form();

        if (! $this->resource && $this->entry) {
//            PlatformException::runtime('over here');
            $this->resource = ResourceFactory::make($this->entry);
            $form->setEntry($this->entry);
        }

        if ($this->resource && ! $this->entry) {
            $form->setEntry($this->resource->newEntryInstance());
        }

        if ($this->resource) {
            $form->setFields($this->buildFields($this->resource->getFieldEntries()));
            $form->setResource($this->resource);
            $form->setIdentifier($this->resource->config()->getResourceKey());
        }

        return $form;
    }

    /**
     * Rebuild resource fields with FormField
     * and inject the resource
     *
     * @param \Illuminate\Support\Collection $fields
     * @return \Illuminate\Support\Collection
     */
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

    public function setResource(?Resource $resource): ResourceFormBuilder
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

    public function setEntry(?EntryContract $entry = null): ResourceFormBuilder
    {
        $this->entry = $entry;

        return $this;
    }

    public function setFormEntry(FormModel $formEntry): ResourceFormBuilder
    {
        $this->formEntry = $formEntry;

        return $this;
    }

    public static function buildFromEntry(EntryContract $entry): Form
    {
        return static::resolve()->setEntry($entry)->build();
    }

    public static function buildFromResource(Resource $resource): Form
    {
        return static::resolve()->setResource($resource)->build();
    }

    /** @return static */
    public static function resolve()
    {
        return app()->make(static::class, func_get_args());
    }
}
