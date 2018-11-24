<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers;

use SuperV\Platform\Domains\Resource\Form\FormConfig;
use SuperV\Platform\Domains\Resource\Http\ResolvesResource;
use SuperV\Platform\Http\Controllers\BaseApiController;

class ResourceCreateController extends BaseApiController
{
    use ResolvesResource;

    public function __invoke()
    {
        $resource = $this->resolveResource();

        FormConfig::make()
                  ->addGroup(
                      $resource->getFields(),
                      $resource->newEntryInstance(),
                      $resource->getHandle()
                  )
                  ->makeForm()
                  ->setRequest($this->request)
                  ->save();

        return response()->json([]);
    }
}