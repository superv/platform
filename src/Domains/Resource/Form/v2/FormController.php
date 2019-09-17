<?php

namespace SuperV\Platform\Domains\Resource\Form\v2;

use SuperV\Platform\Domains\Resource\Form\FormModel;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use SuperV\Platform\Http\Controllers\BaseApiController;
use SuperV\Platform\Http\Middleware\PlatformAuthenticate;

class FormController extends BaseApiController
{
    public function show($identifier)
    {
        if (! $formEntry = FormModel::withIdentifier($identifier)) {
            abort(404, 'Form entry not found');
        }

        if (! $formEntry->isPublic()) {
            app(PlatformAuthenticate::class)->guard($this->request, 'sv-api');
        }

//        $formUrl = sv_route('resource.forms.store', ['uuid' => $formEntry->uuid]);
        $builder = Factory::createBuilder()
                          ->setFormEntry($formEntry)
                          ->setFormUrl(sv_route(Form::ROUTE, ['identifier' => $formEntry->getIdentifier()]));

        $data = [];
        if ($entries = $this->request->get('entry')) {
            foreach ($entries as $entryResourceIdentifier => $entryId) {
                if ($entry = ResourceFactory::make($entryResourceIdentifier)->find($entryId)) {
                    foreach ($entry->toArray() as $key => $value) {
                        $data[$entryResourceIdentifier.'.fields.'.$key] = $value;
                    }
//                    $data[] = $entry;
                }
            }
        }

        if (! empty($data)) {
            $builder->setFormData($data);
        }

        $form = $builder->getForm();

        return $form->render();
    }

    public function edit($identifier, $entryId)
    {
        if (! $formEntry = FormModel::withIdentifier($identifier)) {
            abort(404, 'Form entry not found');
        }

        if (! $formEntry->isPublic()) {
            app(PlatformAuthenticate::class)->guard($this->request, 'sv-api');
        }

        $resource = $formEntry->getOwnerResource();
        $entry = $resource->find($entryId);
        if ($keyName = $resource->config()->getKeyName()) {
            $entry->setKeyName($keyName);
        }

        $builder = Factory::createBuilder()
                          ->setFormEntry($formEntry)
                          ->setFormUrl(sv_url()->path())
                          ->setFormData($entry);

        return $builder->getForm()->render();
    }
}
