<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers;

use SuperV\Platform\Domains\Resource\Field\Contracts\HandlesRpc;
use SuperV\Platform\Domains\Resource\Field\FieldComposer;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Form\FormFactory;
use SuperV\Platform\Domains\Resource\Form\FormField;
use SuperV\Platform\Http\Controllers\BaseController;
use SuperV\Platform\Http\Middleware\PlatformAuthenticate;

class FormController extends BaseController
{
    public function fields($formIdentifier, $field)
    {
        $builder = FormFactory::builderFromFormEntry($formIdentifier);

        if (! $builder->getFormEntry()->isPublic()) {
            app(PlatformAuthenticate::class)->guard($this->request, 'sv-api');
        }

        $form = $builder->getForm();

        $fieldEntry = $builder->getFormEntry()->getFormField($field);

        $field = FieldFactory::createFromEntry($fieldEntry, FormField::class);
        $field->setForm($form);

        if ($resource = $builder->getResource()) {
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

    public function display(string $formIdentifier, int $entryId = null)
    {
        $builder = FormFactory::builderFromFormEntry($formIdentifier);

        if (! $builder->getFormEntry()->isPublic()) {
            app(PlatformAuthenticate::class)->guard($this->request, 'sv-api');
        }

        if ($entryId) {
//        if ($callback = $resource->getCallback('editing')) {
//            app()->call($callback, ['form' => $form, 'entry' => $entry]);
//        }
            $builder->setEntryId($entryId);
        }

        $form = $builder->getForm();

        return $form->makeComponent();
    }

    public function submit(string $formIdentifier, int $entryId = null)
    {
        $builder = FormFactory::builderFromFormEntry($formIdentifier);

        if (! $builder->getFormEntry()->isPublic()) {
            app(PlatformAuthenticate::class)->guard($this->request, 'sv-api');
        }

        if ($entryId) {
            //        if ($callback = $resource->getCallback('editing')) {
//            app()->call($callback, ['form' => $form, 'entry' => $entry]);
//        }
            $builder->setEntryId($entryId);
        }

        $form = $builder->getForm();

        return $form->save();
    }


//    protected function ___store($uuid, ResourceFormBuilder $builder)
//    {
//        $formEntry = $this->getFormEntry($uuid);
//
//        if ($resource = $formEntry->getOwnerResource()) {
//            $builder->setResource($resource);
//        }
//        $form = $builder->build();x
//
//        return $form->setRequest($this->request)
//                    ->make()
//                    ->save();
//    }
//
//    protected function ___edit($uuid, $entryId, ResourceFormBuilder $builder)
//    {
//        if (! $formEntry = $this->getFormEntry($uuid)) {
//            abort(404, 'Form entry not found');
//        }
//
//        $resource = $formEntry->getOwnerResource();
//        $entry = $resource->find($entryId);
//        if ($keyName = $resource->config()->getKeyName()) {
//            $entry->setKeyName($keyName);
//        }
//        $formFields = $builder->buildFields($formEntry->getFormFields());
//
//        $form = ResourceFormBuilder::buildFromEntry($entry);
//        $form->setUrl($resource->route('forms.edit', $entry))
//             ->setRequest($this->request)
//             ->setFields($formFields)
//             ->make($formEntry->uuid);
//
//        if ($callback = $resource->getCallback('editing')) {
//            app()->call($callback, ['form' => $form, 'entry' => $entry]);
//        }
//
//        return $form->makeComponent();
//    }
//
//    protected function ___update($uuid, $entryId)
//    {
//        if (! $formEntry = $this->getFormEntry($uuid)) {
//            abort(404, 'Form entry not found');
//        }
//
//        $resource = $formEntry->getOwnerResource();
//        $entry = $resource->find($entryId);
//        if ($keyName = $resource->config()->getKeyName()) {
//            $entry->setKeyName($keyName);
//        }
//
//        $form = ResourceFormBuilder::buildFromEntry($entry);
//
//        return $form->setRequest($this->request)
//                    ->make()
//                    ->save();
//    }
//
//    protected function ___getFormEntry($uuid): ?FormModel
//    {
//        if (! $formEntry = FormModel::findByUuid($uuid)) {
//            return null;
//        }
//
//        if (! $formEntry->isPublic()) {
//            app(PlatformAuthenticate::class)->guard($this->request, 'sv-api');
//        }
//
//        return $formEntry;
//    }
//
//    protected function ___create($uuid)
//    {
//        if (! $formEntry = $this->getFormEntry($uuid)) {
//            abort(404, 'Form entry not found');
//        }
//
//        $builder = FormBuilder::resolve();
//        $builder->setFormEntry($formEntry)
//                ->setRequest($this->request);
//
//        $form = $builder->getForm();
//
//        return $form->makeComponent();
//    }
//
//    protected function ____fields($uuid, $field, $rpc = null, ResourceFormBuilder $builder)
//    {
//        if (! $formEntry = $this->getFormEntry($uuid)) {
//            abort(404, 'Form entry not found');
//        }
//
//        $builder->setResource($resource = $formEntry->getOwnerResource());
//
//        $form = $builder->build();
//        $form->make($formEntry->uuid);
//
//        $fieldEntry = $formEntry->getFormField($field);
//        $field = FieldFactory::createFromEntry($fieldEntry, FormField::class);
//        $field->setForm($form);
//
//        if ($resource) {
//            $field->setResource($resource);
//        }
//
//        if (! $rpcMethod = $this->route->parameter('rpc')) {
//            return [
//                'data' => (new FieldComposer($field))->forForm()->get(),
//            ];
//        }
//
//        if ($field->getFieldType() instanceof HandlesRpc) {
//            return $field->getFieldType()->getRpcResult(['method' => $rpcMethod], $this->request->toArray());
//        }
//
//        abort(404);
//    }
}
