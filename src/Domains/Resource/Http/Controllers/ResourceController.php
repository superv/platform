<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers;

use SuperV\Modules\Nucleo\Domains\UI\SvBlock;
use SuperV\Modules\Nucleo\Domains\UI\SvCard;
use SuperV\Platform\Domains\Context\Context;
use SuperV\Platform\Domains\Context\Negotiator;
use SuperV\Platform\Domains\Resource\Action\CreateEntryAction;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesForm;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesTable;
use SuperV\Platform\Domains\Resource\Contracts\Requirements\AcceptsParentEntry;
use SuperV\Platform\Domains\Resource\Form\FormConfig;
use SuperV\Platform\Domains\Resource\Http\ResolvesResource;
use SuperV\Platform\Domains\Resource\Relation\Relation;
use SuperV\Platform\Domains\Resource\Table\Table;
use SuperV\Platform\Domains\Resource\Table\TableConfig;
use SuperV\Platform\Domains\UI\Page\Page;
use SuperV\Platform\Http\Controllers\BaseApiController;

class ResourceController extends BaseApiController
{
    use ResolvesResource;

    public function index()
    {
        $uri = url()->current();
        $resource = $this->resolveResource();

        $createAction = CreateEntryAction::make();
        Negotiator::deal($createAction, $resource);

        $config = TableConfig::make()
                             ->setDataUrl(url()->current().'/data')
                             ->setFields($resource)
                             ->setQuery($resource)
                             ->setContext(new Context($resource))
                             ->build();

        if ($this->route->parameter('data')) {
            return ['data' => Table::config($config)->build()->compose()];
        }

        $card = SvCard::make()->block(
            SvBlock::make('sv-table')->setProps($config->compose())
        );

        $page = Page::make($resource->getLabel());
        $page->addBlock($card);
        $page->setActions([$createAction->makeComponent()]);

        return ['data' => sv_compose($page->makeComponent())];
    }

    public function create()
    {
        $form = FormConfig::make()
                          ->setUrl($this->resolveResource()->route('store'))
                          ->addGroup(
                              $this->resolveResource()->getFields(),
                              $this->resolveResource()->newEntryInstance(),
                              $this->resolveResource()->getHandle()
                          )
                          ->makeForm();

        $page = Page::make('Create new '.$this->resource->getSingularLabel());
        $page->addBlock(
            SvBlock::make('sv-form')->setProps($form->compose())
        );

        return ['data' => sv_compose($page->makeComponent())];
    }

    public function store()
    {
        FormConfig::make()
                  ->addGroup(
                      $this->resolveResource()->getFields(),
                      $this->resolveResource()->newEntryInstance(),
                      $this->resolveResource()->getHandle()
                  )
                  ->makeForm()
                  ->setRequest($this->request)
                  ->save();

        return response()->json([]);
    }

    public function edit()
    {
        $this->resolveResource();
        $form = FormConfig::make()
                          ->setUrl($this->entry->route('update'))
                          ->addGroup(
                              $fields = $this->resolveResource()->getFields(),
                              $entry = $this->entry,
                              $handle = $this->resolveResource()->getHandle()
                          )
                          ->makeForm();

        // main edit form
        $editorTab = SvBlock::make('sv-form')->setProps($form->compose());

        $tabs = sv_tabs()->addTab(sv_tab('Edit', $editorTab)->autoFetch());

        // make forms
        $this->resource->getRelations()
                       ->filter(function (Relation $relation) { return $relation instanceof ProvidesForm; })
                       ->map(function (ProvidesForm $formProvider) use ($tabs) {
                           if ($formProvider instanceof AcceptsParentEntry) {
                               $formProvider->acceptParentEntry($this->entry);
                           }
                           $form = $formProvider->makeForm();

                           return $tabs->addTab(sv_tab($formProvider->getFormTitle(), SvBlock::make('sv-form')->setProps($form->compose())));
                       });

        // make tables
        $this->resource->getRelations()
                       ->filter(function (Relation $relation) { return $relation instanceof ProvidesTable; })
                       ->map(function (Relation $relation) use ($tabs) {
                           $card = SvBlock::make('sv-loader')->setProps([
                               'url' => sv_url(
                                   sprintf(
                                       'sv/res/%s/%s/%s/table',
                                       $this->resource->getHandle(),
                                       $this->entry->getId(),
                                       $relation->getName()
                                   )
                               ),
                           ]);

                           return $tabs->addTab(sv_tab($relation->getName(), $card));
                       });

        $page = Page::make($entry->getLabel());
        $page->addBlock($tabs);

        return ['data' => sv_compose($page->makeComponent())];
    }

    public function update()
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