<?php

namespace SuperV\Platform\Domains\Resource\Form\Contracts;

use Illuminate\Http\Request;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Form\FormBuilder;
use SuperV\Platform\Domains\Resource\Form\FormModel;
use SuperV\Platform\Domains\Resource\Resource;

interface FormBuilderInterface
{
    public function setEntry(?EntryContract $entry = null): FormBuilder;

    public function getRequest(): ?Request;

    public function getResource(): ?Resource;

    public function getFormEntry(): FormModel;

    public function getEntry(): ?EntryContract;

    public function getForm(): FormInterface;

    public function resolveForm(): FormInterface;

    public function setRequest($request): FormBuilder;

    public function setFormEntry(FormModel $formEntry): FormBuilder;
}