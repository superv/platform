<?php

namespace SuperV\Platform\Domains\UI\Page;

use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesForm;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesTable;
use SuperV\Platform\Domains\Resource\Contracts\RequiresEntry;
use SuperV\Platform\Domains\Resource\Field\Contracts\Field;
use SuperV\Platform\Domains\Resource\Field\FieldComposer;
use SuperV\Platform\Domains\Resource\Relation\Relation;
use SuperV\Platform\Domains\UI\Components\ComponentContract;

class EntryPage extends ResourcePage
{
    public function build($tokens = [])
    {
        $this->buildSections();

        $this->buildActions();

        return parent::build($tokens);
    }

    public function makeComponent(): ComponentContract
    {
        if ($imageField = $this->resource->fields()->getHeaderImage()) {
            $imageUrl = (new FieldComposer($imageField))
                ->forView($this->entry)
                ->get('image_url');
        }

        return parent::makeComponent()
                     ->setName('sv-page')
                     ->mergeProps([
                         'sections' => $this->buildSections(),
                         'image-url' => $imageUrl ?? '',
                         'create-url' =>$this->resource->route('create'),
                         'edit-url' => sv_url($this->resource->route('edit', $this->entry)),
                         'view-url' => sv_url($this->resource->route('view', $this->entry)),
                         'index-url' => $this->resource->route('index'),
                     ]);
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

    protected function buildSections()
    {
        return collect($this->getRelationsSections())
            ->map(function ($section) {
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