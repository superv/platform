<?php

namespace SuperV\Platform\Domains\Resource\Form\v2;

use SuperV\Platform\Domains\Resource\Field\FieldModel;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormField;
use SuperV\Platform\Domains\Resource\Form\FormModel;
use SuperV\Platform\Domains\Resource\Form\v2;
use SuperV\Platform\Domains\Resource\Form\v2\Contracts\FormBuilder as FormBuilderContract;

class FormBuilder implements FormBuilderContract
{
    /** @var \SuperV\Platform\Domains\Resource\Form\FormModel */
    protected $formEntry;

    /**
     * @var \SuperV\Platform\Domains\Resource\Form\v2\Contracts\Form
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

    public function setFormData($data): FormBuilderContract
    {
        $this->formData = $data;

        return $this;
    }

    public function build()
    {
        if (! empty($this->formData)) {
            $this->fields->fill($this->formData);
        }
        $form = Form::resolve($this->fields, $this->formIdentifier);

        $form->setUrl($this->formUrl);

        return $form;
    }

    public function getForm(): v2\Contracts\Form
    {
        return $this->build();
    }

    public function setFormIdentifier($formIdentifier): FormBuilderContract
    {
        $this->formIdentifier = $formIdentifier;

        return $this;
    }

    public function setFormEntry(FormModel $formEntry): FormBuilderContract
    {
        $this->formIdentifier = $formEntry->getIdentifier();

        $formEntry->getFormFields()->map(function (FieldModel $fieldEntry) {
            $this->fields->addFromFieldEntry($fieldEntry);
        });

        $this->formUrl = sv_route('sv::forms.show', ['identifier' => $this->formIdentifier]);

        return $this;
    }

    public function setFormUrl($formUrl): FormBuilderContract
    {
        $this->formUrl = $formUrl;

        return $this;
    }

    public function getFormIdentifier(): string
    {
        return $this->formIdentifier;
    }
}
