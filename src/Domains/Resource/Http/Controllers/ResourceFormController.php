<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers;

use SuperV\Platform\Domains\Resource\Form\Form;
use SuperV\Platform\Domains\Resource\Form\FormConfig;
use SuperV\Platform\Domains\Resource\Http\ResolvesResource;
use SuperV\Platform\Domains\UI\Page\Page;
use SuperV\Platform\Http\Controllers\BaseApiController;

class ResourceFormController extends BaseApiController
{
    use ResolvesResource;

    public function create()
    {
        $resource = $this->resolveResource();
        $form = FormConfig::make($resource->newEntryInstance())
                          ->setUrl($resource->route('store'))
                          ->makeForm()
                          ->setResource($resource);

        if ($callback = $resource->getCallback('creating')) {
            app()->call($callback, ['form' => $form]);
        }

        $page = Page::make('Create new '.$resource->getSingularLabel());
        $page->addBlock($form);

        return $page->build();
    }

    public function store()
    {
        $resource = $this->resolveResource();

        return Form::forResource($resource)
                   ->setRequest($this->request)
                   ->save();
//
//        return response()->json([
//            'data' => [
//                'redirect_to' => $this->resource->route('view', $form->getDefaultEntry()),
//            ],
//        ]);
    }

    public function edit()
    {
        $resource = $this->resolveResource();
        $form = FormConfig::make($this->entry)
                          ->setUrl($this->entry->route('update'))
                          ->makeForm()
                          ->setRequest($this->request)
                          ->setResource($resource);

        if ($callback = $resource->getCallback('editing')) {
            app()->call($callback, ['form' => $form]);
        }

        return $form->makeComponent();
    }

    public function update()
    {
        $resource = $this->resolveResource();

        return FormConfig::make($this->entry)
                         ->setUrl($this->entry->route('update'))
                         ->makeForm()
                         ->setResource($resource)
                         ->setRequest($this->request)
                         ->save();
    }
}