<?php

namespace SuperV\Platform\Domains\UI\Page;

use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesForm;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesTable;
use SuperV\Platform\Domains\Resource\Field\Contracts\FieldInterface;
use SuperV\Platform\Domains\Resource\Relation\Relation;
use SuperV\Platform\Domains\UI\Components\ComponentContract;

class EntryPage extends ResourcePage
{
    protected $editable = true;

    protected $viewable = true;

    public function build($tokens = [])
    {
        $this->buildSections();

        $this->buildActions();

        return parent::build($tokens);
    }

    public function makeComponent(): ComponentContract
    {
        if ($imageField = $this->resource->fields()->getHeaderImage()) {
//            $imageUrl = (new FieldComposer($imageField))
//                ->forView($this->entry)
//                ->get('image_url');

            $imageUrl = $imageField->getComposer()->toView($this->entry)->get('image_url');
        }

        return parent::makeComponent()
                     ->setName('sv-page')
                     ->mergeProps([
                         'sections' => $this->buildSections(),
                         'links'    => array_filter_null(['image' => $imageUrl ?? '']),
                     ]);
    }

    public function notEditable(): EntryPage
    {
        $this->editable = false;

        return $this;
    }

    public function notViewable(): EntryPage
    {
        $this->viewable = false;

        return $this;
    }

    public function isViewable(): bool
    {
        return $this->viewable;
    }

    public function isEditable(): bool
    {
        return $this->editable;
    }

    protected function buildSections()
    {
        return collect($this->getRelationsSections())
            ->merge($this->getFieldsSections())
            ->merge(collect($this->sections))
            ->map(function ($section) {
                return sv_parse($section, ['entry' => $this->entry]);
            })->map(function ($section) {
                if ($section['identifier'] === $this->getSelectedSection()) {
                    $section['default'] = true;
                }

                if (! $this->getSelectedSection() && $section['identifier'] == $this->getDefaultSection()) {
                    $section['default'] = true;
                }

                return $section;
            });
    }

    protected function getFieldsSections(): Collection
    {
        return $this->resource->fields()
                              ->keyByName()
                              ->filter(function (FieldInterface $field) {
                                  return $field->type() instanceof ProvidesTable;
                              })
                              ->map(function (FieldInterface $field) {
                                  return [
                                      'identifier' => $field->getHandle(),
                                      'url'        => $this->entry->router()->fields($field->getHandle()),
                                      'target'     => 'portal:'.$this->resource->getIdentifier().':'.$this->entry->getId(),
                                      'title'      => sv_trans($field->getLabel()),
                                  ];
                              })->filter()->values();
    }

    protected function getRelationsSections(): Collection
    {
        return $this->resource->getRelations()
                              ->filter(function (Relation $relation) {
                                  return ! $relation->hasFlag('view.hide');
                              })
                              ->map(function (Relation $relation) {
                                  if ($relation instanceof ProvidesTable) {
                                      $url = $relation->indexRoute($this->entry);
                                  } elseif ($relation instanceof ProvidesForm) {
                                      $url = $relation->route('edit', $this->entry);
                                  } else {
                                      return null;
                                  }

                                  return [
                                      'identifier' => $relation->getName(),
                                      'url'        => $url,
                                      'target'     => 'portal:'.$this->resource->getIdentifier().':'.$this->entry->getId(),
                                      'title'      => sv_trans(str_unslug($relation->getName())),
                                  ];
                              })
                              ->filter()->values();
    }
}
