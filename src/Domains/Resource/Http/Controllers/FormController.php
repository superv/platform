<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers;

use SuperV\Platform\Domains\Resource\Field\Contracts\HandlesRpc;
use SuperV\Platform\Domains\Resource\Field\FieldComposer;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Form\FormBuilder;
use SuperV\Platform\Domains\Resource\Form\FormModel;
use SuperV\Platform\Domains\Resource\Form\Jobs\MakeForm;
use SuperV\Platform\Domains\Resource\Form\Jobs\SaveForm;
use SuperV\Platform\Domains\UI\Page\Page;
use SuperV\Platform\Http\Controllers\BaseController;
use SuperV\Platform\Http\Middleware\PlatformAuthenticate;

class FormController extends BaseController
{
    public function fields($uuid, $field, $rpc = null, FormBuilder $builder)
    {
        $formEntry =  $this->getFormEntry($uuid);

        if ($resource = $formEntry->getOwnerResource()) {
            $builder->setResource($resource);
        }
        $form = $builder->build();
        $form->make($formEntry->uuid);

        $fieldEntry = $formEntry->getFormField($field);
        $field = FieldFactory::createFromEntry($fieldEntry);
        $field->setForm($form);

        if (! $rpcMethod = $this->route->parameter('rpc')) {
            return [
                'data' => (new FieldComposer($field))->forForm()->get()
            ];
        }

        if ($field->getFieldType() instanceof HandlesRpc) {
            return $field->getFieldType()->getRpcResult(['method' => $rpcMethod], $this->request->toArray());
        }

        return abort(404);
    }

    public function show($uuid)
    {
        $formEntry =  $this->getFormEntry($uuid);

        $form =  MakeForm::dispatch($formEntry, $this->request);

        return $form->makeComponent();
    }

    public function post($uuid)
    {
        $formEntry = $this->getFormEntry($uuid);

        SaveForm::dispatch($formEntry, $this->request);

        return ['status' => 'ok'];
    }

    protected function getFormEntry($uuid): FormModel
    {
        if (!$formEntry = FormModel::findByUuid($uuid))
            abort(404);

        if (! $formEntry->isPublic()) {
            app(PlatformAuthenticate::class)->guard($this->request, 'sv-api');
        }

        return $formEntry;
    }
}