<?php

namespace SuperV\Platform\Http\Controllers;

use SuperV\Platform\Domains\Resource\Form\Form;

class FormsController extends BaseApiController
{
    public function store($uuid)
    {
        $form = Form::fromCache($uuid);

        $form->post($this->request);

        return response()->json([], 201);
    }
}