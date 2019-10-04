<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers\Relation;

use SuperV\Platform\Domains\Resource\Http\ResolvesResource;
use SuperV\Platform\Http\Controllers\BaseApiController;

class RelationFormController extends BaseApiController
{
    use ResolvesResource;

    public function create()
    {
        $relation = $this->resolveRelation();

        /** @var \SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface $form */
        $form = $relation->makeForm();
        $form->setUrl(str_replace_last('/create', '', sv_url()->current()));

        if ($callback = $relation->getCallback('create.displaying')) {
            $callback($form);
        }

        return $form
            ->resolve()
            ->makeComponent();
    }

    public function store()
    {
        $relation = $this->resolveRelation();

        /** @var \SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface $form */
        $form = $relation->makeForm($this->request);

        if ($callback = $relation->getCallback('create.storing')) {
            app()->call($callback, ['form' => $form, 'request' => $this->request, 'entry' => $this->entry]);
        }

        $formResponse = $form->resolve()->save();

        return $formResponse->setEvents(['create_'.$relation->getName().':complete']);
    }

    public function edit()
    {
        return $this->resolveRelation()
                    ->makeForm()
                    ->setUrl(str_replace_last('/edit', '', url()->current()))
                    ->makeComponent();
    }
}
