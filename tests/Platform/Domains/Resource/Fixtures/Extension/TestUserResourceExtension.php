<?php

namespace Tests\Platform\Domains\Resource\Fixtures\Extension;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Extension\Contracts\ExtendsResource;
use SuperV\Platform\Domains\Resource\Extension\Contracts\ObservesRetrieved;
use SuperV\Platform\Domains\Resource\Extension\Contracts\ObservesSaved;
use SuperV\Platform\Domains\Resource\Extension\Contracts\ObservesSaving;
use SuperV\Platform\Domains\Resource\Resource;
use SuperV\Platform\Domains\Resource\Resource\ResourceView;
use SuperV\Platform\Domains\UI\Components\Html;
use SuperV\Platform\Domains\UI\Components\Image;
use SuperV\Platform\Domains\UI\Components\Layout\RowComponent;

class TestUserResourceExtension implements ExtendsResource, ObservesRetrieved, ObservesSaving, ObservesSaved
{
    public static $called = [];

    public function extends(): string
    {
        return 't_users';
    }

    public function extend(Resource $resource)
    {
        $resource->getField('name')->setConfigValue('extended', true);

        $resource->resolveViewUsing(function (EntryContract $entry) use ($resource) {
            return (new ResourceView($resource, $entry))
                ->resolveHeadingUsing(
                    function () {
                        $avatar = $this->entry->getField('avatar');
                        $avatarUrl = $avatar->compose()->get('config.url');

                        return RowComponent::make()
                                           ->addColumn(Image::make()->setProp('src', $avatarUrl))
                                           ->addColumn(Html::make()->setProp('content', $this->resource->getEntryLabel($this->entry)));
                    });
        });
    }

    public function retrieved(EntryContract $entry)
    {
        static::$called['retrieved'] = $entry;
    }

    public function saving(EntryContract $entry)
    {
        static::$called['saving'] = $entry;
    }

    public function saved(EntryContract $entry)
    {
        static::$called['saved'] = $entry;
    }
}