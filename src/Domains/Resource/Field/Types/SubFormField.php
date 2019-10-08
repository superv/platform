<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use Illuminate\Http\Request;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Field\FieldType;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface;
use SuperV\Platform\Domains\Resource\Form\Features\SubmitForm;
use SuperV\Platform\Domains\Resource\Form\FormData;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use SuperV\Platform\Support\Composer\Payload;

class SubFormField extends FieldType
{
    protected $formData;

    protected $entryId;

    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $resource;

    protected function boot()
    {
        $this->field->on('form.composing', $this->composer());
    }

    public function saving(FormInterface $form)
    {
        $formSubmitter = (new SubmitForm)
            ->setResource($this->getResource())
            ->setFormData($this->getFormData());

        if ($form->isUpdating()) {
            $subEntryId = $form->getEntry()->getAttribute($this->getColumnName());
            $subEntry = $this->getResource()->find($subEntryId);

            $formSubmitter->setEntry($subEntry);
        }

        $subForm = $formSubmitter->submit();

        if ($form->isCreating()) {
            $form->getData()->toSave($this->getColumnName(), $subForm->getEntry()->getId());
        }
    }

    public function resolveDataFromEntry(FormData $data, EntryContract $entry)
    {
        parent::resolveDataFromEntry($data, $entry);

        $this->setEntryId($entry->getAttribute($this->getColumnName()));
    }

    public function resolveValueFromRequest(Request $request, ?EntryContract $entry = null)
    {
        $this->formData = $request->all()[$this->getColumnName()];

        return null;
    }

    public function resolveDataFromRequest(FormData $data, Request $request, ?EntryContract $entry = null)
    {
        return parent::resolveDataFromRequest($data, $request, $entry);
    }

    /**
     * @return mixed
     */
    public function getFormData()
    {
        return $this->formData;
    }

    public function setEntryId($entryId)
    {
        $this->entryId = $entryId;

        return $this;
    }

    /** @return \SuperV\Platform\Domains\Resource\Resource */
    public function getResource(): Resource
    {
        if (! $this->resource) {
            $this->resource = ResourceFactory::make($this->getConfigValue('resource'));
        }

        return $this->resource;
    }

    public function getFormUrl()
    {
        if (! $formUrl = $this->getConfigValue('form')) {
            $formUrl = $this->getResource()->router()->createForm();
        }
        if ($this->entryId) {
            $formUrl .= '/'.$this->entryId;
        }

        return $formUrl;
    }

    protected function composer()
    {
        return function (Payload $payload) {
            $payload->set('config.form', $this->getFormUrl());
            $payload->set('meta.full', true);
        };
    }
}