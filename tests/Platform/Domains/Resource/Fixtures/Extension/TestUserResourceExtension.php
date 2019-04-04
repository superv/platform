<?php

namespace Tests\Platform\Domains\Resource\Fixtures\Extension;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Extension\Contracts\ExtendsResource;
use SuperV\Platform\Domains\Resource\Extension\Contracts\ObservesEntryRetrieved;
use SuperV\Platform\Domains\Resource\Extension\Contracts\ObservesEntrySaved;
use SuperV\Platform\Domains\Resource\Extension\Contracts\ObservesEntrySaving;
use SuperV\Platform\Domains\Resource\Resource;

class TestUserResourceExtension implements ExtendsResource, ObservesEntryRetrieved, ObservesEntrySaving, ObservesEntrySaved
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
        static::$called['retrieved'] = $entry->getTable() === $this->extends() ? $entry : null;
    }

    public function saving(EntryContract $entry)
    {
        static::$called['saving'] = $entry->getTable() === $this->extends() ? $entry : null;
    }

    public function saved(EntryContract $entry)
    {
        static::$called['saved'] = $entry->getTable() === $this->extends() ? $entry : null;
    }
}