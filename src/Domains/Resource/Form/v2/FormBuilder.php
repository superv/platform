<?php

namespace SuperV\Platform\Domains\Resource\Form\v2;

use SuperV\Platform\Domains\Resource\Field\FieldModel;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormField;
use SuperV\Platform\Domains\Resource\Form\FormModel;
use SuperV\Platform\Domains\Resource\Form\v2\Contracts\FormBuilderInterface;
use SuperV\Platform\Domains\Resource\Form\v2\Contracts\FormInterface;

class FormBuilder implements FormBuilderInterface
{
    /** @var \SuperV\Platform\Domains\Resource\Form\FormModel */
    protected $formEntry;

    /**
     * @var \SuperV\Platform\Domains\Resource\Form\v2\Contracts\FormInterface
     */
    protected $form;

    /**
     * @var \SuperV\Platform\Domains\Resource\Form\v2\FormFieldCollection
     */
    protected $fields;

    protected $formIdentifier;

    protected $formUrl;

    protected $formData;

    public function __construct(FormFieldCollection $fields)
    {
        $this->fields = $fields;
    }

    public function addField(FormField $field)
    {
        $this->fields->addField($field);
    }

    public function addFields(array $fields)
    {
        $this->fields->addFields($fields);
    }

    public function setFormData($data): FormBuilderInterface
    {
        $this->formData = $data;

        return $this;
    }

    public function build()
    {
        if ($fields = array_get($this->formData, 'fields')) {
            $this->fields->fill($fields);
        }
        $form = Form::resolve($this->fields, $this->formIdentifier);

        $form->setUrl($this->formUrl);
        $form->setData($this->formData);

        return $form;
    }

    public function getForm(): FormInterface
    {
        return $this->build();
    }

    public function setFormIdentifier($formIdentifier): FormBuilderInterface
    {
        $this->formIdentifier = $formIdentifier;

        return $this;
    }

    public function setFormEntry(FormModel $formEntry): FormBuilderInterface
    {
        $this->formIdentifier = $formEntry->getIdentifier();

        $formEntry->getFormFields()->map(function (FieldModel $fieldEntry) {
            $this->fields->addFromFieldEntry($fieldEntry);
        });

        $this->formUrl = sv_route('sv::forms.show', ['identifier' => $this->formIdentifier]);

        return $this;
    }

    public function setFormUrl($formUrl): FormBuilderInterface
    {
        $this->formUrl = $formUrl;

        return $this;
    }

    public function getFormIdentifier(): string
    {
        return $this->formIdentifier;
    }
}
