<?php

namespace SuperV\Platform\Domains\Resource\Resource;

use Closure;
use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\UI\Components\Component;
use SuperV\Platform\Domains\UI\Components\Html;
use SuperV\Platform\Domains\UI\Components\Image;
use SuperV\Platform\Domains\UI\Components\Layout\RowComponent;

class ResourceView
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

//        $avatar = $this->entry->getField('logo');
//        $avatarUrl = $avatar->compose()->get('config.url');

        $label = $this->resource->getEntryLabel($this->entry);

        return Component::make('sv-header')
            ->addClass('p-8 h-32')->card()
            ->setProp('image-url', $avatarUrl ?? '')
            ->setProp('header', $label);

        return RowComponent::make()
                           ->addClass('p-8 h-32')->card()
                           ->addColumn(Image::make()
                                            ->setProp('src', $avatarUrl)
                           )
                           ->addColumn(Html::make()
                                           ->addClass('ml-12 text-2xl')
                                           ->setProp('content', '<p class="text-4xl">'.$label.'</p>')
                           );

//        $grid = new SvGrid;
//        $grid->threeColumns()->singleRow();
//
//        $firstRow = $grid->firstRow();
//
//        $firstRow->firstColumn()->width(25)->content($thing);
//        $firstRow->secondColumn()->width(45)->content($thing);
//        $firstRow->thirdColumn()->width(30)->content($thing);
    }
}