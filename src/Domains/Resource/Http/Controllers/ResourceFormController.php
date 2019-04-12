<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers;

use SuperV\Platform\Domains\Resource\Form\Form;
use SuperV\Platform\Domains\Resource\Form\FormBuilder;
use SuperV\Platform\Domains\Resource\Http\ResolvesResource;
use SuperV\Platform\Domains\UI\Page\Page;
use SuperV\Platform\Http\Controllers\BaseApiController;

class ResourceFormController extends BaseApiController
{
    use ResolvesResource;

    public function create(FormBuilder $builder)
    {
        $resource = $this->resolveResource();

        $form = $builder->setResource($resource)->build();
        $form->setUrl($resource->route('store'))
             ->setRequest($this->request)
             ->make();

        if ($callback = $resource->getCallback('creating')) {
            app()->call($callback, ['form' => $form]);
        }

        $page = Page::make('Create new '.$resource->getSingularLabel());
        $page->addBlock($form);

        return $page->build();
    }

    public function store(FormBuilder $builder)
    {
        $form = $builder->setResource($this->resolveResource())
                        ->setEntry($this->entry)
                        ->build();

        return $form->setRequest($this->request)
                    ->make()
                    ->save();
    }

    public function edit(FormBuilder $builder)
    {
        $resource = $this->resolveResource();
        $form = $builder->setEntry($this->entry)
                        ->build();

        $form->setUrl($resource->route('update', $this->entry))
             ->setRequest($this->request)
             ->make();

        if ($callback = $resource->getCallback('editing')) {
            app()->call($callback, ['form' => $form, 'entry' => $this->entry]);
        }

        return $form->makeComponent();
    }

    public function update(FormBuilder $builder)
    {
        $this->resolveResource();

        $form = $builder->setEntry($this->entry)
                        ->build();

        return $form->setRequest($this->request)
                    ->make()
                    ->save();
    }
}