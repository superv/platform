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

        FormConfig::make()
                  ->setUrl($this->entry->route('update'))
                  ->addGroup(
                      $fields = $this->resolveResource()->getFields(),
                      $entry = $this->entry,
                      $handle = $this->resolveResource()->getHandle()
                  )
                  ->makeForm()->setRequest($this->request)
                  ->save();

        return response()->json([]);
    }
}