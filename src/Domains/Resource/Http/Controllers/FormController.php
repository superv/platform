<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers;

use SuperV\Platform\Domains\Resource\Field\Contracts\HandlesRpc;
use SuperV\Platform\Domains\Resource\Field\FieldComposer;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Form\FormBuilder;
use SuperV\Platform\Domains\Resource\Form\FormModel;
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

    public function show($uuid, FormBuilder $builder)
    {
        $formEntry =  $this->getFormEntry($uuid);

        if ($resource = $formEntry->getOwnerResource()) {
            $builder->setResource($resource);
        }
        $form = $builder->build();
        $form->setFields($formEntry->compileFields())
             ->setUrl(sv_url()->path())
             ->setRequest($this->request)
             ->make($formEntry->uuid);

        if ($callback = $resource->getCallback('creating')) {
            app()->call($callback, ['form' => $form]);
        }

        $page = Page::make($formEntry->title);
        $page->addBlock($form);

        return $page->build();
    }

    public function post($uuid, FormBuilder $builder)
    {
        $formEntry = $this->getFormEntry($uuid);

        if ($resource = $formEntry->getOwnerResource()) {
            $builder->setResource($resource);
        }
        $form = $builder->build();

        $form->setRequest($this->request)
                    ->make()
                    ->save();

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