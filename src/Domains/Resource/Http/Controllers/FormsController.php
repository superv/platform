<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers;

use SuperV\Platform\Domains\Resource\Form\Form;
use SuperV\Platform\Http\Controllers\BaseApiController;

class FormsController extends BaseApiController
{
    public function post($uuid)
    {
        $form = Form::fromCache($uuid);

        $form->post($this->request);

        return response()->json([], 201);
    }
}