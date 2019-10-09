<?php

namespace SuperV\Platform\Domains\Resource\Form\Features;

use Illuminate\Http\Request;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Database\Entry\EntryRepository;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface;
use SuperV\Platform\Domains\Resource\Form\FormFactory;
use SuperV\Platform\Domains\Resource\ResourceFactory;

class SubmitForm
{
    /** @var \SuperV\Platform\Domains\Resource\Form\Features\Resource */
    protected $resource;

    /** @var array */
    protected $formData;

    /** @var \SuperV\Platform\Domains\Database\Model\Contracts\EntryContract */
    protected $entry;

    public function setResource($resource): SubmitForm
    {
        $this->resource = is_string($resource) ? ResourceFactory::make($resource) : $resource;

        return $this;
    }

    public function setFormData($formData): SubmitForm
    {
        $this->formData = $formData;

        return $this;
    }

    public function submit(): FormInterface
    {
        $form = $this->getForm();

        $form->save();

        return $form;
    }

    public function setEntry(EntryContract $entry): SubmitForm
    {
        $this->entry = $entry;

        return $this;
    }

    public function getForm(): FormInterface
    {
        $builder = FormFactory::builderFromResource($this->resource);
        $builder->setEntry($this->entry ?? EntryRepository::for($this->resource)->newInstance());

        $builder->setRequest(new Request($this->formData));

        $form = $builder->getForm()
                        ->resolve();

        return $form;
    }
}