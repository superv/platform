<?php

namespace SuperV\Platform\Domains\Resource\Form\Jobs;

use Illuminate\Http\Request;
use SuperV\Platform\Domains\Resource\Form\FormModel;
use SuperV\Platform\Domains\Resource\Form\ResourceFormBuilder;
use SuperV\Platform\Support\Dispatchable;

class MakeForm
{
    use Dispatchable;

    /**
     * @var \SuperV\Platform\Domains\Resource\Form\FormModel
     */
    protected $formEntry;

    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    public function __construct(FormModel $formEntry, ?Request $request = null)
    {
        $this->formEntry = $formEntry;
        $this->request = $request;
    }

    public function handle(ResourceFormBuilder $builder)
    {
        $builder->setResource($resource = $this->formEntry->getOwnerResource());

        $form = $builder->build()->setRequest($this->request);

        $formFields = $builder->buildFields($this->formEntry->getFormFields());

        $form->setFields($formFields)
             ->setUrl(sv_url()->path())
             ->make($this->formEntry->uuid);

        if ($resource && $callback = $resource->getCallback('creating')) {
            app()->call($callback, ['form' => $form]);
        }

        return $form;
    }
}
