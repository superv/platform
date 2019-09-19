<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers;

use SuperV\Platform\Domains\Resource\Http\ResolvesResource;
use SuperV\Platform\Http\Controllers\BaseApiController;

class RelationCreateController extends BaseApiController
{
    use ResolvesResource;

    public function create()
    {
        $relation = $this->resolveRelation();
        $form = $relation->makeForm();

        if ($callback = $relation->getCallback('create.displaying')) {
            $callback($form);
        }

        return $form
            ->setUrl(str_replace_last('/create', '', sv_url()->current()))
            ->makeComponent();
    }

    public function store()
    {
        $relation = $this->resolveRelation();
        $form = $relation->makeForm();

        if ($callback = $relation->getCallback('create.storing')) {
            app()->call($callback, ['form' => $form, 'request' => $this->request, 'entry' => $this->entry]);
        }

        $form->setRequest($this->request)->save();

        return response()->json(['status' => 'ok', 'events' => ['create_'.$relation->getName().':complete']]);
    }
}
