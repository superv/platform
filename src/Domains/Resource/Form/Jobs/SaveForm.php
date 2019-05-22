<?php

namespace SuperV\Platform\Domains\Resource\Form\Jobs;

use Illuminate\Http\Request;
use SuperV\Platform\Domains\Resource\Form\FormBuilder;
use SuperV\Platform\Domains\Resource\Form\FormModel;
use SuperV\Platform\Support\Dispatchable;

class SaveForm
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

    public function __construct(FormModel $formData, Request $request)
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

        return $form->setRequest($this->request)
             ->make()
             ->save();
    }
}