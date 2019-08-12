<?php

namespace SuperV\Platform\Domains\Resource\Form\Jobs;

use Illuminate\Http\Request;
use SuperV\Platform\Domains\Resource\Form\Contracts\FormField;
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
        $builder->setResource($resource = $this->formData->getOwnerResource());

        $form = $builder->build()->setRequest($this->request);

        // wrap field with formField
        //
//        $formFields = $this->formData->compileFields()
//                                     ->map(function (FormField $field) use ($form) {
//                                         $field->setForm($form);
//
//                                         return $field;
//                                     });

        $formFields = $builder->buildFields($this->formData->getFormFields());

        $form->setFields($formFields)
             ->setUrl(sv_url()->path())
             ->make($this->formData->uuid);

        if ($resource && $callback = $resource->getCallback('creating')) {
            app()->call($callback, ['form' => $form]);
        }

        return $form;
    }
}