<?php

namespace SuperV\Platform\Domains\Resource\Resource;

use Closure;
use Illuminate\Support\Collection;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesForm;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesTable;
use SuperV\Platform\Domains\Resource\Contracts\ProvidesUIComponent;
use SuperV\Platform\Domains\Resource\Field\Contracts\Field;
use SuperV\Platform\Domains\Resource\Field\FieldComposer;
use SuperV\Platform\Domains\Resource\Relation\Relation;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\UI\Components\Component;
use SuperV\Platform\Domains\UI\Components\ComponentContract;

class ResourceView implements ProvidesUIComponent
{
    /**
     * @var \SuperV\Platform\Domains\Resource\Resource
     */
    protected $resource;

    /** @var Closure */
    protected $headingResolver;

    /**
     * @var \SuperV\Platform\Domains\Database\Model\Contracts\EntryContract
     */
    protected $entry;

    protected $actions = [];

    protected $sections;

    public function __construct(Resource $resource, EntryContract $entry)
    {
        $this->resource = $resource;
        $this->entry = $entry;
        $this->sections = collect();
    }

    public function makeComponent(): ComponentContract
    {
        return Component::make('sv-resource-view')
                        ->setProps([
                            'entry'    => sv_compose($this->entry),
                            'edit-url' => sv_url($this->resource->route('edit', $this->entry)),
                            'heading'  => [
                                'imageUrl' => $imageUrl ?? '',
                                'header'   => $this->resource->getEntryLabel($this->entry),
                            ],
                            'sections' => $this->getSections(),
                            'fields' => $this->getFieldsForView(),
                        ]);
    }

    public function resolveHeadingUsing(Closure $callback)
    {
        $this->headingResolver = $callback;

        return $this;
    }

    public function addSection(array $section): ResourceView
    {
        $this->sections[] = $section;

        return $this;
    }

    public function getSections(): Collection
    {
        return $this->sections;
    }

    public function setSections($sections): ResourceView
    {
        $this->sections = wrap_collect($sections);

        return $this;
    }

    protected function buildSections()
    {
        $this->sections = $this->sections->merge($this->getRelationsSections());

        $this->sections->transform(function ($section) {
            return sv_parse($section, ['entry' => $this->entry]);
        });
    }

    protected function getFieldsForView()
    {
        return $this->resource->fields()
                              ->keyByName()
//                              ->filter(function (Field $field) {
//                                  return ! in_array($field->getName(), ['deleted_at']);
//                              })
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


//
//public function getHeading_Xxxxxxxxxxx()
//{
//    if ($this->headingResolver) {
//        $callback = Closure::bind($this->headingResolver, $this, get_class());
//
//        return app()->call($callback, ['entry' => $this->entry, 'resource' => $this->resource]);
//    }
//
//    $label = $this->resource->getEntryLabel($this->entry);
//
//    return Component::make($this->resource->getConfigValue('view.header', 'sv-header'))
//                    ->addClass('p-2')->card()
//                    ->setProp('image-url', '')
//                    ->setProp('header', $label)
//                    ->setProp('actions', $this->getActions());
//}

//        return RowComponent::make()
//                           ->addClass('p-8 h-32')->card()
//                           ->addColumn(Image::make()
//                                            ->setProp('src', $avatarUrl)
//                           )
//                           ->addColumn(Html::make()
//                                           ->addClass('ml-12 text-2xl')
//                                           ->setProp('content', '<p class="text-4xl">'.$label.'</p>')
//                           );

//        $grid = new SvGrid;
//        $grid->threeColumns()->singleRow();
//
//        $firstRow = $grid->firstRow();
//
//        $firstRow->firstColumn()->width(25)->content($thing);
//        $firstRow->secondColumn()->width(45)->content($thing);
//        $firstRow->thirdColumn()->width(30)->content($thing);