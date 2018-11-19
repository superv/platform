<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers;

use SuperV\Platform\Domains\Resource\Form\FormConfig;
use SuperV\Platform\Http\Controllers\BaseApiController;

class FormController extends BaseApiController
{
    public function post($uuid)
    {
        FormConfig::wakeup($uuid)
                  ->makeForm()
                  ->setRequest($this->request)->save();

        return response()->json([]);
    }
}