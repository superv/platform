<?php

namespace SuperV\Platform\Domains\Resource\Form;

use Illuminate\Support\Collection;
use SuperV\Platform\Contracts\Dispatcher;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Field\FieldModel;

class FormBuilder
{
    /** @var \SuperV\Platform\Domains\Resource\Form\FormModel */
    protected $formEntry;

    /** @var \Illuminate\Http\Request */
    protected $request;

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

        $form->setIdentifier($this->formEntry->getIdentifier());

        $form->setFields($this->buildFields($this->formEntry->getFormFields()))
             ->setUrl(sv_url()->path())
             ->make($this->formEntry->uuid);

        $this->dispatcher->dispatch($this->formEntry->getIdentifier().'.resolved', $form);

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

    public function getEntry(): EntryContract
    {
        return $this->entry;
    }

    public function setEntry(?EntryContract $entry = null): FormBuilder
    {
        $this->entry = $entry;

        return $this;
    }

    public function setFormEntry(FormModel $formEntry): FormBuilder
    {
        $this->formEntry = $formEntry;

        return $this;
    }

    public function setRequest(\Illuminate\Http\Request $request): FormBuilder
    {
        $this->request = $request;

        return $this;
    }

    /** @return static */
    public static function resolve()
    {
        return app()->make(static::class, func_get_args());
    }
}
