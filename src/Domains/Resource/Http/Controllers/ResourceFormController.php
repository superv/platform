<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers;

use SuperV\Platform\Domains\Resource\Form\FormModel;
use SuperV\Platform\Domains\Resource\Form\ResourceFormBuilder;
use SuperV\Platform\Domains\Resource\Http\ResolvesResource;
use SuperV\Platform\Http\Controllers\BaseApiController;

class ResourceFormController extends BaseApiController
{
    use ResolvesResource;

//    public function create()
//    {
//        $resource = $this->resolveResource();
//
//        $page = Page::make(__('Create New', ['object' => $resource->getSingularLabel()]));
//        $page->addBlock(sv_loader('sv/forms/'.$resource->getHandle()));
//
//        return $page->build();
//    }

//    public function store(FormBuilder $builder)
//    {
//        $form = $builder->setResource($this->resolveResource())
//                        ->setEntry($this->entry)
//                        ->build();
//
//        return $form->setRequest($this->request)
//                    ->make()
//                    ->save();
//    }

    public function edit(ResourceFormBuilder $builder)
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

    public function update(ResourceFormBuilder $builder)
    {
        $this->resolveResource();

        $form = $builder->setEntry($this->entry)
                        ->build();

        return $form->setRequest($this->request)
                    ->make()
                    ->save();
    }
}
