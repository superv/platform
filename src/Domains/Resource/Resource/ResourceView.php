<?php

namespace SuperV\Platform\Domains\Resource\Resource;

use Closure;
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

    public function __construct(Resource $resource, EntryContract $entry)
    {
        $this->resource = $resource;
        $this->entry = $entry;
    }

    public function resolveHeadingUsing(Closure $callback)
    {
        $this->headingResolver = $callback;

        return $this;
    }

    public function getHeading()
    {
        if ($this->headingResolver) {
            $callback = Closure::bind($this->headingResolver, $this, get_class());

            return app()->call($callback, ['entry' => $this->entry, 'resource' => $this->resource]);
        }

        $label = $this->resource->getEntryLabel($this->entry);

        return Component::make($this->resource->getConfigValue('view.header', 'sv-header'))
                        ->addClass('p-2')->card()
                        ->setProp('image-url', $avatarUrl ?? '')
                        ->setProp('header', $label)
                        ->setProp('actions', $this->getActions());
    }

    protected function getActions()
    {
        $relationActions = $this->resource->getRelations()
                                          ->map(function (Relation $relation) {
                                              if ($relation instanceof ProvidesTable) {
                                                  return [
                                                      'url'   => $relation->indexRoute($this->entry),
                                                      'title' => str_unslug($relation->getName()),
                                                  ];
                                              } elseif ($relation instanceof ProvidesForm) {
                                                  return [
                                                      'url'   => $relation->route('edit', $this->entry),
                                                      'title' => str_unslug($relation->getName()),
                                                  ];
                                              }
                                          })
                                          ->filter()->values()->all();

        return array_merge([
            ['url' => $this->resource->route('edit', $this->entry), 'title' => 'Edit'],
        ], $relationActions);
    }

    public function makeComponent(): ComponentContract
    {
        return Component::make('sv-resource-view')
                        ->setProps([
                            'entry'   => $this->entry->toArray(),
                            'heading' => [
                                'imageUrl' => 'https://www.gravatar.com/avatar/'.md5(strtolower('maselcuk@gmail.com')).'?s=300',
                                'header'   => $this->resource->getEntryLabel($this->entry),
                                'actions'  => $this->getActions(),
                            ],

                            'fields' => $this->resource->fields()->keyByName()->map(function (Field $field) {
                                return (new FieldComposer($field))->forView($this->entry);
                            }),

                        ]);
    }
}


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