<?php

namespace SuperV\Platform\Domains\Resource\Form\v2\Contracts;

use SuperV\Platform\Domains\Resource\Form\Contracts\FormField;
use SuperV\Platform\Domains\Resource\Form\FormModel;

interface FormBuilderInterface
{
    public function setFormEntry(FormModel $formEntry): FormBuilderInterface;

    public function getForm(): FormInterface;

    public function addField(FormField $field);

    public function addFields(array $fields);

    public function build();

    public function getFormIdentifier(): string;

    public function setFormIdentifier($formIdentifier): FormBuilderInterface;

    public function setFormUrl($formUrl): FormBuilderInterface;

    public function setFormData($data): FormBuilderInterface;
}
