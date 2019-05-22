<?php

namespace SuperV\Platform\Domains\Resource\Form\Jobs;

use Illuminate\Http\Request;
use SuperV\Platform\Domains\Resource\Form\FormBuilder;
use SuperV\Platform\Domains\Resource\Form\FormModel;
use SuperV\Platform\Support\Dispatchable;

class MakeForm
{
    use Dispatchable;

    /**
     * @var \SuperV\Platform\Domains\Resource\Form\FormModel
     */
    protected $formData;

    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    public function __construct(FormModel $formData, ?Request $request = null)
    {
        $this->formData = $formData;
        $this->request = $request;
    }

    public function handle(FormBuilder $builder)
    {
        if ($resource = $this->formData->getOwnerResource()) {
            $builder->setResource($resource);
        }
        $form = $builder->build();
        if ($this->request) {
            $form->setRequest($this->request);
        }

        $form->setFields($this->formData->compileFields())
             ->setUrl(sv_url()->path())
             ->make($this->formData->uuid);

        if ($resource && $callback = $resource->getCallback('creating')) {
            app()->call($callback, ['form' => $form]);
        }

        return $form;
    }
}