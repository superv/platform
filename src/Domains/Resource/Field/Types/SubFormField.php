<?php

namespace SuperV\Platform\Domains\Resource\Field\Types;

use Illuminate\Http\Request;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Database\Entry\EntryRepository;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldTypeInterface;
use SuperV\Platform\Domains\Resource\Field\FieldType;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface;
use SuperV\Platform\Domains\Resource\Form\Features\SubmitForm;
use SuperV\Platform\Domains\Resource\Form\FormData;
use SuperV\Platform\Domains\Resource\Form\FormFactory;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use SuperV\Platform\Support\Composer\Payload;

class SubFormField extends FieldType
{
    protected $handle = 'sub_form';

    protected $component = 'sv_sub_form_field';

    protected $formData;

    protected $entryId;

    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $resource;

    protected function boot()
    {
        $this->field->on('form.composing', $this->composer());
    }

    public function getParentType(): FieldTypeInterface
    {
        return $this->getConfigValue('parent_type');
    }

    public function getColumnName()
    {
        return $this->getParentType()->getColumnName();
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

        $subForm = $formSubmitter->getForm();

        $this->bindFields($subForm, $form);

        $subForm->validate();
        $subForm->submit();

        if ($form->isCreating()) {
            $form->getData()->toSave($this->getColumnName(), $subForm->getEntry()->getId());
        }

        if ($handler = $this->getConfigValue('on_create')) {
            if (class_exists($handler)) {
                (new $handler)->handle($subForm->getEntry(), $this->getParentType()->getConfig());
            }
        }
    }

    public function resolveDataFromEntry(FormData $data, EntryContract $entry)
    {
        parent::resolveDataFromEntry($data, $entry);

        $this->setEntryId($entry->getAttribute($this->getColumnName()));
    }

    public function resolveValueFromRequest(Request $request, ?EntryContract $entry = null)
    {
        $all = $request->all();

        $this->formData = array_get($all, $this->getFieldHandle());

        return null;
    }

    public function bindFields(FormInterface $subForm, FormInterface $parentForm)
    {
        // normalize
        foreach ($this->getConfigValue('bind', []) as $parentKey => $subKey) {
            $parentKey = is_numeric($parentKey) ? $subKey : $parentKey;

            $subForm->getData()->set($subKey, $parentForm->getData()->get($parentKey));
        }
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

    public function getFormComponent()
    {
        $builder = FormFactory::builderFromResource($this->getResource());

        if ($this->entryId) {
            $builder->setEntry(EntryRepository::for($this->getResource())->find($this->entryId));
        }

        $form = $builder->resolveForm();

        if ($bindFields = $this->getConfigValue('bind')) {
            $form->fields()->hide($bindFields);
        }
        $composed = $form->compose();

        return $composed->get('fields');
    }

    protected function composer()
    {
        return function (Payload $payload) {
            $payload->set('config.fields', $this->getFormComponent());
            $payload->set('meta.full', true);
        };
    }
}