<?php

namespace SuperV\Platform\Domains\UI\Page;

use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesForm;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesTable;
use SuperV\Platform\Domains\Resource\Contracts\RequiresEntry;
use SuperV\Platform\Domains\Resource\Field\Contracts\Field;
use SuperV\Platform\Domains\Resource\Field\FieldComposer;
use SuperV\Platform\Domains\Resource\Relation\Relation;
use SuperV\Platform\Domains\Resource\Resource\ResourceView;
use SuperV\Platform\Domains\UI\Components\ComponentContract;

class EntryPage extends ResourcePage
{
    /** @var \Illuminate\Support\Collection */
    protected $sections;

    public function build($tokens = [])
    {
//        $this->buildView();

        $this->buildSections();

        $this->buildActions();

        return parent::build($tokens);
    }

    public function makeComponent(): ComponentContract
    {
        return parent::makeComponent()
                     ->setName('sv-entry-page')
                     ->setProp('sections', $this->sections)
                     ->setProp('edit-url', sv_url($this->resource->route('edit', $this->entry)))
                     ->setProp('view-url', sv_url($this->resource->route('view', $this->entry)))
            ;
    }

    protected function buildActions(): void
    {
        $this->actions = collect($this->actions)->map(function ($action) {
            if (is_string($action)) {
                if (! $action = $this->resource->getAction($action)) {
                    return null;
                }
            }

            if ($action instanceof RequiresEntry) {
                $action->setEntry($this->entry);
            }

            return $action;
        })->filter()->values()->all();
    }

    protected function buildView(): void
    {
        $view = new ResourceView($this->resource, $this->entry);

        $this->addBlock($view);
    }

    protected function buildSections()
    {
        $this->sections = collect($this->getRelationsSections())
            ->transform(function ($section) {
                return sv_parse($section, ['entry' => $this->entry]);
            });
    }

    protected function getFieldsForView()
    {
        return $this->resource->fields()
                              ->keyByName()
                              ->filter(function (Field $field) {
                                  return ! in_array($field->getName(), ['deleted_at']);
                              })
                              ->map(function (Field $field) {
                                  return (new FieldComposer($field))->forView($this->entry);
                              });
    }

    protected function getRelationsSections(): Collection
    {
        return $this->resource->getRelations()
                              ->map(function (Relation $relation) {
                                  if ($url = $relation->getConfigValue('view.url')) {
                                      $portal = true;
                                  } elseif ($relation instanceof ProvidesTable) {
                                      $url = $relation->indexRoute($this->entry);
                                  } elseif ($relation instanceof ProvidesForm) {
                                      $url = $relation->route('edit', $this->entry);
                                  } else {
                                      return null;
                                  }

                                  return [
                                      'url'    => $url,
                                      'portal' => $portal ?? false,
                                      'title'  => str_unslug($relation->getName()),
                                  ];
                              })
                              ->filter()->values();
    }
}