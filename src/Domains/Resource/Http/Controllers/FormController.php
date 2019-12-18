<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers;

use Current;
use SuperV\Platform\Domains\Resource\Database\Entry\EntryRepository;
use SuperV\Platform\Domains\Resource\Field\Jobs\HandleFieldRpc;
use SuperV\Platform\Domains\Resource\Form\FormFactory;
use SuperV\Platform\Http\Controllers\BaseApiController;

class FormController extends BaseApiController
{
    public function display(string $formIdentifier, int $entryId = null)
    {
        $builder = FormFactory::builderFromFormEntry($formIdentifier);
        $resource = $builder->getFormEntry()->getOwnerResource();

        if ($resource) {
            Current::user()->canOrFail($resource->getChildIdentifier('actions', $entryId ? 'edit' : 'create'));
        }

        if ($entryId) {
            $builder->setEntry(EntryRepository::for($resource)->find($entryId));
        }

        return $builder->resolveForm()
                       ->makeComponent();
    }

    public function submit(string $formIdentifier, int $entryId = null)
    {
        $builder = FormFactory::builderFromFormEntry($formIdentifier);
        $resource = $builder->getFormEntry()->getOwnerResource();

        Current::user()->canOrFail($resource->getChildIdentifier('actions', $entryId ? 'edit' : 'create'));

        if ($entryId) {
            $entry = EntryRepository::for($resource)->find($entryId);
        } else {
            $entry = EntryRepository::for($resource)->newInstance();
        }
        $builder->setEntry($entry)
                ->setRequest($this->request);

        return $builder->resolveForm()
                       ->save();
    }

    public function fields($formIdentifier, $field)
    {
        $builder = FormFactory::builderFromFormEntry($formIdentifier);

        $form = $builder->resolveForm();
        $fieldEntry = $builder->getFormEntry()->getFormField($field);

        $response = (new HandleFieldRpc($form, $fieldEntry))
            ->handle(
                $this->request->toArray(),
                $this->route->parameter('rpc')
            );

        return $response ?? abort(404);
    }
}
