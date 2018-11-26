<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers;

use SuperV\Platform\Domains\Resource\Http\ResolvesResource;
use SuperV\Platform\Http\Controllers\BaseApiController;

class RelationCreateController extends BaseApiController
{
    use ResolvesResource;

    public function create()
    {
        return $this->resolveRelation()
                    ->makeForm()
                    ->setUrl(str_replace_last('/create', '', url()->current()))
                    ->makeComponent();
    }

    public function store()
    {
        $form = $this->resolveRelation()->makeForm();
        $form->setRequest($this->request)->save();

        return response()->json([]);
    }
}