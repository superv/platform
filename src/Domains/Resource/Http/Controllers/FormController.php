<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers;

use SuperV\Platform\Domains\Resource\Database\Entry\EntryRepository;
use SuperV\Platform\Domains\Resource\Field\Contracts\HandlesRpc;
use SuperV\Platform\Domains\Resource\Field\FieldComposer;
use SuperV\Platform\Domains\Resource\Field\FieldFactory;
use SuperV\Platform\Domains\Resource\Form\FormFactory;
use SuperV\Platform\Domains\Resource\Form\FormField;
use SuperV\Platform\Http\Controllers\BaseController;
use SuperV\Platform\Http\Middleware\PlatformAuthenticate;

class FormController extends BaseController
{
    public function display(string $formIdentifier, int $entryId = null)
    {
        $builder = $this->resolveFormBuilder($formIdentifier);
        $resource = $builder->getFormEntry()->getOwnerResource();

        if ($entryId) {
            $builder->setEntry(EntryRepository::for($resource)->find($entryId));
        }

        return $builder->getForm()
                       ->makeComponent();
    }

    public function submit(string $formIdentifier, int $entryId = null)
    {
        $builder = $this->resolveFormBuilder($formIdentifier);
        $resource = $builder->getFormEntry()->getOwnerResource();

        if ($entryId) {
            $entry = EntryRepository::for($resource)->find($entryId);
        } else {
            $entry = EntryRepository::for($resource)->newInstance();
        }
        $builder->setEntry($entry)
                ->setRequest($this->request);

        return $builder->getForm()
                       ->save();
    }

    public function fields($formIdentifier, $field)
    {
        $builder = $this->resolveFormBuilder($formIdentifier);

        $form = $builder->getForm();

        $fieldEntry = $builder->getFormEntry()->getFormField($field);

        $field = FieldFactory::createFromEntry($fieldEntry, FormField::class);
        $field->setForm($form);

//        if ($resource = $builder->getResource()) {
//            $field->setResource($resource);
//        }

        if (! $rpcMethod = $this->route->parameter('rpc')) {
            return [
                'data' => (new FieldComposer($field))->forForm()->get(),
            ];
        }

        if ($field->getFieldType() instanceof HandlesRpc) {
            return $field->getFieldType()
                         ->getRpcResult(['method' => $rpcMethod], $this->request->toArray());
        }

        abort(404);
    }

    /**
     * @param $formIdentifier
     * @return \SuperV\Platform\Domains\Resource\Form\Contracts\FormBuilderInterface
     */
    protected function resolveFormBuilder($formIdentifier
    ): \SuperV\Platform\Domains\Resource\Form\Contracts\FormBuilderInterface {
        $builder = FormFactory::builderFromFormEntry($formIdentifier);

        if (! $builder->getFormEntry()->isPublic()) {
            app(PlatformAuthenticate::class)->guard($this->request, 'sv-api');
        }

        return $builder;
    }
}
