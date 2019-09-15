<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers;

use SuperV\Platform\Domains\Resource\Field\Contracts\HandlesRpc;
use SuperV\Platform\Domains\Resource\Field\FieldComposer;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Form\FormBuilder;
use SuperV\Platform\Domains\Resource\Form\FormField;
use SuperV\Platform\Domains\Resource\Form\FormModel;
use SuperV\Platform\Domains\Resource\Form\Jobs\MakeForm;
use SuperV\Platform\Domains\Resource\Form\Jobs\SaveForm;
use SuperV\Platform\Domains\Resource\Form\ResourceFormBuilder;
use SuperV\Platform\Http\Controllers\BaseController;
use SuperV\Platform\Http\Middleware\PlatformAuthenticate;

class FormController extends BaseController
{
    public function fields($uuid, $field, $rpc = null, ResourceFormBuilder $builder)
    {
        if (! $formEntry = $this->getFormEntry($uuid)) {
            abort(404, 'Form entry not found');
        }

        $builder->setResource($resource = $formEntry->getOwnerResource());

        $form = $builder->build();
        $form->make($formEntry->uuid);

        $fieldEntry = $formEntry->getFormField($field);
        $field = FieldFactory::createFromEntry($fieldEntry, FormField::class);
        $field->setForm($form);

        if ($resource) {
            $field->setResource($resource);
        }

        if (! $rpcMethod = $this->route->parameter('rpc')) {
            return [
                'data' => (new FieldComposer($field))->forForm()->get(),
            ];
        }

        if ($field->getFieldType() instanceof HandlesRpc) {
            return $field->getFieldType()->getRpcResult(['method' => $rpcMethod], $this->request->toArray());
        }

        abort(404);
    }

    public function show($namespace, $name)
    {
        if (! $formEntry = FormModel::withIdentifier($namespace.'::forms.'.$name)) {
            abort(404, 'Form entry not found');
        }

        if (! $formEntry->isPublic()) {
            app(PlatformAuthenticate::class)->guard($this->request, 'sv-api');
        }

        $builder = FormBuilder::resolve();
        $builder->setFormEntry($formEntry)
                ->setRequest($this->request);

        $form = $builder->build();

        return $form->makeComponent();
    }

    public function create($uuid)
    {
        if (! $formEntry = $this->getFormEntry($uuid)) {
            abort(404, 'Form entry not found');
        }

        /** @var \SuperV\Platform\Domains\Resource\Form\Form $form */
        $form = MakeForm::dispatch($formEntry, $this->request);


        return $form->makeComponent();
    }

    public function edit($uuid, $entryId, ResourceFormBuilder $builder)
    {
        if (! $formEntry = $this->getFormEntry($uuid)) {
            abort(404, 'Form entry not found');
        }

        $resource = $formEntry->getOwnerResource();
        $entry = $resource->find($entryId);
        if ($keyName = $resource->config()->getKeyName()) {
            $entry->setKeyName($keyName);
        }
        $formFields = $builder->buildFields($formEntry->getFormFields());

        $form = ResourceFormBuilder::buildFromEntry($entry);
        $form->setUrl($resource->route('forms.edit', $entry))
             ->setRequest($this->request)
             ->setFields($formFields)
             ->make($formEntry->uuid);

        if ($callback = $resource->getCallback('editing')) {
            app()->call($callback, ['form' => $form, 'entry' => $entry]);
        }

        return $form->makeComponent();

    }

    public function store($uuid)
    {
        $formEntry = $this->getFormEntry($uuid);

        return SaveForm::dispatch($formEntry, $this->request);
    }

    public function update($uuid, $entryId)
    {
        if (! $formEntry = $this->getFormEntry($uuid)) {
            abort(404, 'Form entry not found');
        }

        $resource = $formEntry->getOwnerResource();
        $entry = $resource->find($entryId);
        if ($keyName = $resource->config()->getKeyName()) {
            $entry->setKeyName($keyName);
        }

        $form = ResourceFormBuilder::buildFromEntry($entry);

        return $form->setRequest($this->request)
                    ->make()
                    ->save();
    }

    protected function getFormEntry($uuid): ?FormModel
    {
        if (! $formEntry = FormModel::findByUuid($uuid)) {
            return null;
        }

        if (! $formEntry->isPublic()) {
            app(PlatformAuthenticate::class)->guard($this->request, 'sv-api');
        }

        return $formEntry;
    }
}
