<?php

namespace SuperV\Platform\Domains\Resource\Form\Jobs;

use Illuminate\Http\Request;
use SuperV\Platform\Domains\Resource\Form\Form;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Support\Dispatchable;

class PostForm
{
    use Dispatchable;

    /**
     * @var \SuperV\Platform\Domains\Resource\Form\Form
     */
    protected $form;

    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    public function __construct(Form $form, Request $request)
    {
        $this->form = $form;
        $this->request = $request;
    }

    public function handle()
    {
        $this->form->setFieldValues($this->request);

        $this->form->getResources()->map(function (Resource $resource) {
            $resource->saveEntry(['form' => $this->form]);
        });

        $this->form->applyPostSaveCallbacks();
    }

}