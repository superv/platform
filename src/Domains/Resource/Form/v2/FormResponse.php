<?php

namespace SuperV\Platform\Domains\Resource\Form\v2;

use Illuminate\Contracts\Support\Responsable;

class FormResponse implements Responsable
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Form\v2\Contracts\Form
     */
    protected $form;

    public function __construct(Contracts\Form $form)
    {
        $this->form = $form;
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function toResponse($request)
    {
    }
}
