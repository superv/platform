<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers;

use SuperV\Platform\Domains\Resource\Form\FormBuilder;
use SuperV\Platform\Domains\Resource\Form\FormModel;
use SuperV\Platform\Domains\Resource\Http\ResolvesResource;
use SuperV\Platform\Domains\UI\Page\Page;
use SuperV\Platform\Http\Controllers\BaseApiController;

class ResourceFormController extends BaseApiController
{
    use ResolvesResource;

    public function create(FormBuilder $builder)
    {
        $resource = $this->resolveResource();

//        $form = $builder->setResource($resource)->build();
//        $formData = FormModel::findByResource($resource->id());
//
//        $form->setUrl($resource->route('store'))
//             ->setRequest($this->request);
//
//        if ($callback = $resource->getCallback('creating')) {
//            app()->call($callback, ['form' => $form]);
//        }
//
//        $form->make($formData ? $formData->uuid : null);

        $page = Page::make('Create new '.$resource->getSingularLabel());
        $page->addBlock(sv_loader('sv/forms/'.$resource->getHandle()));

        return $page->build();
    }

    public function create__X(FormBuilder $builder)
    {
        $resource = $this->resolveResource();

        $form = $builder->setResource($resource)->build();
        $formData = FormModel::findByResource($resource->id());

        $form->setUrl($resource->route('store'))
             ->setRequest($this->request);

        if ($callback = $resource->getCallback('creating')) {
            app()->call($callback, ['form' => $form]);
        }

        $form->make($formData ? $formData->uuid : null);

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
        $form = $builder->setEntry($this->entry)->build();
        $formData = FormModel::findByResource($resource->id());

        $form->setUrl($resource->route('update', $this->entry))
             ->setRequest($this->request)
             ->make($formData ? $formData->uuid : null);

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