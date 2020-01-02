<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers;

use SuperV\Platform\Domains\Resource\Contracts\AcceptsParentEntry;
use SuperV\Platform\Domains\Resource\Field\FieldType;
use SuperV\Platform\Domains\Resource\Http\ResolvesResource;
use SuperV\Platform\Domains\UI\Jobs\MakeComponentTree;
use SuperV\Platform\Http\Controllers\BaseApiController;

class FieldController extends BaseApiController
{
    use ResolvesResource;

    public function route($fieldType, $route)
    {
        $fieldType = FieldType::resolveType($fieldType);

        if (! $controller = $fieldType->resolveController()) {
            abort(404);
        }

        if (! method_exists($controller, $route)) {
            abort(404);
        }

        return app()->call([$controller, $route]);
    }

    public function index()
    {
        $field = $this->resolveResource()->getField($this->route->parameter('field'));
        $fieldType = $field->type();

        if ($fieldType instanceof AcceptsParentEntry) {
            $fieldType->acceptParentEntry($this->entry);
        }

        $table = $fieldType->makeTable();

        if ($this->route->parameter('data')) {
            return $table->setRequest($this->request)->build();
        }

        return MakeComponentTree::dispatch($table)->withTokens(['res' => $fieldType->getRelated()->toArray()]);
    }

    public function create()
    {
        $field = $this->resolveResource()->getField($this->route->parameter('field'));
        $fieldType = $field->type();

        if ($fieldType instanceof AcceptsParentEntry) {
            $fieldType->acceptParentEntry($this->entry);
        }

        /** @var \SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface $form */
        $form = $fieldType->makeForm();
        $form->setUrl(str_replace_last('/create', '', sv_url()->current()));

//        if ($callback = $relation->getCallback('create.displaying')) {
//            $callback($form);
//        }

        return $form
            ->resolve()
            ->makeComponent();
    }

    public function store()
    {
        $field = $this->resolveResource()->getField($this->route->parameter('field'));
        $fieldType = $field->type();

        if ($fieldType instanceof AcceptsParentEntry) {
            $fieldType->acceptParentEntry($this->entry);
        }

        /** @var \SuperV\Platform\Domains\Resource\Form\Contracts\FormInterface $form */
        $form = $fieldType->makeForm($this->request);

//        if ($callback = $relation->getCallback('create.storing')) {
//            app()->call($callback, ['form' => $form, 'request' => $this->request, 'entry' => $this->entry]);
//        }

        $formResponse = $form->resolve()->save();

        return $formResponse->setEvents(['create_'.$fieldType->getHandle().':complete']);
    }
}