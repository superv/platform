<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers;

use SuperV\Platform\Domains\Resource\Form\FormConfig;
use SuperV\Platform\Domains\Resource\Http\ResolvesResource;
use SuperV\Platform\Http\Controllers\BaseApiController;

class ResourceUpdateController extends BaseApiController
{
    use ResolvesResource;

    public function __invoke()
    {
        $this->resolveResource();

        FormConfig::make($this->entry)
                  ->setUrl($this->entry->route('update'))
                  ->makeForm()
                  ->setRequest($this->request)
                  ->save();

        return response()->json([]);
    }
}