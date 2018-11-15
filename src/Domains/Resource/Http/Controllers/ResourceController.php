<?php

namespace SuperV\Platform\Domains\Resource\Http\Controllers;

use SuperV\Modules\Nucleo\Domains\UI\Page\SvPage;
use SuperV\Modules\Nucleo\Domains\UI\SvBlock;
use SuperV\Modules\Nucleo\Domains\UI\SvCard;
use SuperV\Platform\Domains\Resource\Contracts\NeedsEntry;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesForm;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesTable;
use SuperV\Platform\Domains\Resource\Contracts\Requirements\AcceptsParentResourceEntry;
use SuperV\Platform\Domains\Resource\Form\FormBuilder;
use SuperV\Platform\Domains\Resource\Form\Jobs\BuildFormDeprecated;
use SuperV\Platform\Domains\Resource\Relation\Relation;
use SuperV\Platform\Domains\Resource\ResourceFactory;
use SuperV\Platform\Domains\Resource\ResourceModel;
use SuperV\Platform\Domains\Resource\Table\Table;
use SuperV\Platform\Domains\Resource\Table\TableConfig;
use SuperV\Platform\Http\Controllers\BaseApiController;

class ResourceController extends BaseApiController
{
    /** @var \SuperV\Platform\Domains\Resource\Resource */
    protected $resource;

    /** @var \SuperV\Platform\Domains\Resource\Model\ResourceEntry */
    protected $entry;

    public function index()
    {
        $this->resource();

        $config = new TableConfig();
        $config->setFieldsProvider($this->resource);
        $config->setQueryProvider($this->resource);

        $card = SvCard::make()->block(
            SvBlock::make('sv-table-v2')->setProps($config->build()->compose())
        );

        $page = SvPage::make('')->addBlock($card);
        $page->hydrate([
                'title' => $this->resource->getLabel(),
            ]
        );
        $page->build();

        return sv_compose($page);
    }

    public function table($uuid)
    {
        $config = TableConfig::fromCache($uuid);

        return ['data' => Table::config($config)->build()->compose()];
    }

    public function create()
    {
        $form = (new FormBuilder)
            ->addGroup(
                $this->resource()->getHandle(),
                $this->resource()->newEntryInstance(),
                $this->resource()->getFields()
            )
            ->prebuild()
            ->getForm();

        $page = SvPage::make('')->addBlock(
            SvBlock::make('sv-form-v2')->setProps($form->compose())
        );

        $page->hydrate([
            'title' => 'Create new '.$this->resource->getSingularLabel(),
        ]);
        $page->build();

        return sv_compose($page);
    }

    public function edit()
    {
        $form = (new FormBuilder)
            ->addGroup(
                $handle = $this->resource()->getHandle(),
                $entry = $this->entry,
                $fields = $this->resource()->getFields()
//                $fields = ResourceModel::withSlug($this->resource()->getHandle())
            )
            ->prebuild()
            ->getForm();

        // main edit form
        $editorTab = SvBlock::make('sv-form-v2')->setProps($form->compose());

        $tabs = sv_tabs()->addTab(sv_tab('Edit', $editorTab)->autoFetch());

        // make forms
        $this->resource->getRelations()
                       ->filter(function (Relation $relation) { return $relation instanceof ProvidesForm; })
                       ->map(function (ProvidesForm $formProvider) use ($tabs) {
                           if ($formProvider instanceof AcceptsParentResourceEntry) {
                               $formProvider->acceptParentResourceEntry($this->entry);
                           }
                           $form = $formProvider->makeForm();

                           return $tabs->addTab(sv_tab($formProvider->getFormTitle(), SvBlock::make('sv-form-v2')->setProps($form->compose())));
                       });

        // make tables
        $this->resource->getRelations()
                       ->filter(function (Relation $relation) { return $relation instanceof ProvidesTable; })
                       ->map(function (ProvidesTable $tableProvider) use ($tabs) {
                           if ($tableProvider instanceof AcceptsParentResourceEntry) {
                               $tableProvider->acceptParentResourceEntry($this->entry);
                           }
                           $config = $tableProvider->makeTableConfig();

                           $card = SvCard::make()->block(
                               SvBlock::make('sv-table-v2')->setProps($config->compose())
                           );

                           return $tabs->addTab(sv_tab($config->getTitle(), $card));
                       });

        $page = SvPage::make()->addBlock($tabs);

        $page->hydrate([
            'title'   => $entry->getLabel(),
            'actions' => ['create'],
        ]);

        $page->build();

        return sv_compose($page);
    }

    /** @return \SuperV\Platform\Domains\Resource\Resource */
    protected function resource()
    {
        if ($this->resource) {
            return $this->resource;
        }
        $resource = request()->route()->parameter('resource');
        $this->resource = ResourceFactory::make(str_replace('-', '_', $resource));

        if (! $this->resource) {
            throw new \Exception("Resource not found [{$resource}]");
        }

        if ($id = request()->route()->parameter('id')) {
            $this->entry = $this->resource()->find($id);
        }

        return $this->resource;
    }
}