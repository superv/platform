<?php

namespace Tests\Platform\Domains\Resource\Fixtures\Extension;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Extension\Contracts\ExtendsResource;
use SuperV\Platform\Domains\Resource\Extension\Contracts\ObservesRetrieved;
use SuperV\Platform\Domains\Resource\Extension\Contracts\ObservesSaved;
use SuperV\Platform\Domains\Resource\Extension\Contracts\ObservesSaving;
use SuperV\Platform\Domains\Resource\Resource;

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