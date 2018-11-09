<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers;

use SuperV\Platform\Domains\Resource\Form\FormBuilder;
use SuperV\Platform\Http\Controllers\BaseApiController;

class FormsController extends BaseApiController
{
    public function post($uuid)
    {
        $builder = FormBuilder::wakeup($uuid);
        $builder->setRequest($this->request);
        $form = $builder->build()->getForm();
        $form->save();

        return response()->json([], 201);
    }
}