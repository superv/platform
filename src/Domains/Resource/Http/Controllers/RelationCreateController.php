<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers;

use SuperV\Platform\Domains\Resource\Form\FormConfig;
use SuperV\Platform\Domains\Resource\Http\ResolvesResource;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\UI\Page\Page;
use SuperV\Platform\Http\Controllers\BaseApiController;

class RelationCreateController extends BaseApiController
{
    use ResolvesResource;

    public function create()
    {
        $relation = $this->resolveRelation();
        $resource = Resource::of($relation->getConfig()->getRelatedResource());

        $form = FormConfig::make()
                          ->setUrl(str_replace_last('/create', '', url()->current()))
                          ->addGroup(
                              $resource->getFields(),
                              $this->entry->{$relation->getName()}()->make(),
                              $resource->getHandle()
                          )
                          ->hideField($this->resolveResource()->getResourceKey().'_id')
                          ->makeForm();

        $page = Page::make('Create new '.$resource->getSingularLabel());
        $page->addBlock($form);

        return $page->build();
    }

    public function store()
    {
        $relation = $this->resolveRelation();
        $resource = Resource::of($relation->getConfig()->getRelatedResource());

     FormConfig::make()
                          ->setUrl(str_replace_last('/create', '', url()->current()))
                          ->addGroup(
                              $resource->getFields(),
                              $this->entry->{$relation->getName()}()->make(),
                              $resource->getHandle()
                          )
                          ->makeForm()->setRequest($this->request)
                          ->save();

        return response()->json([]);
    }
}