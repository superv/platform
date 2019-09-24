<?php

namespace SuperV\Platform\Domains\Resource\Form\v2;

use SuperV\Platform\Domains\Resource\Form\FormModel;
use SuperV\Platform\Http\Controllers\BaseApiController;
use SuperV\Platform\Http\Middleware\PlatformAuthenticate;

class FormController extends BaseApiController
{
    public function handle($identifier, $entryId = null)
    {
        if (! $formEntry = FormModel::withIdentifier($identifier)) {
            abort(404, 'Form entry not found');
        }

        if (! $formEntry->isPublic()) {
            app(PlatformAuthenticate::class)->guard($this->request, 'sv-api');
        }

        $builder = FormFactory::createBuilder()
                              ->setFormEntry($formEntry)
                              ->setFormUrl(sv_route(Form::ROUTE, ['identifier' => $formEntry->getIdentifier()]));


        $form = $builder->getForm();

        if ($entryId) {
            $this->request->merge(['entries' => [$form->identifier()->getParent().':'.$entryId]]);
        }

        $form->setRequest($this->request)->handle();

        if ($form->isMethod('POST')) {
            $form->submit();

            return ['data' => $form->getResponse()->toArray()];
        }

        return $form->render();
    }

}
