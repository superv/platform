<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers;

use SuperV\Platform\Domains\Resource\Field\Jobs\HandleFieldRpc;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormBuilderInterface;
use SuperV\Platform\Domains\Resource\Form\FormFactory;
use SuperV\Platform\Http\Controllers\BaseController;

class PublicFormController extends BaseController
{
    public function display(string $formIdentifier)
    {
        return $this->resolveFormBuilder($formIdentifier)
                    ->resolveForm()
                    ->makeComponent();
    }

    public function submit(string $formIdentifier)
    {
        return $this->resolveFormBuilder($formIdentifier)
                    ->setRequest($this->request)
                    ->resolveForm()
                    ->save();
    }

    public function fields($formIdentifier, $field)
    {
        $builder = $this->resolveFormBuilder($formIdentifier);

        $form = $builder->resolveForm();
        $fieldEntry = $builder->getFormEntry()->getFormField($field);

        $response = (new HandleFieldRpc($form, $fieldEntry))
            ->handle(
                $this->request->toArray(),
                $this->route->parameter('rpc')
            );

        return $response ?? abort(404);
    }

    protected function resolveFormBuilder($formIdentifier
    ): FormBuilderInterface {
        $builder = FormFactory::builderFromFormEntry($formIdentifier);

        if (! $builder->getFormEntry()->isPublic()) {
            abort(401);
        }

        return $builder;
    }
}
