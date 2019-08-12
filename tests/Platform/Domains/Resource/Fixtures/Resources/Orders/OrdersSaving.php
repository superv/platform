<?php

namespace Tests\Platform\Domains\Resource\Fixtures\Resources\Orders;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Contracts\Hook\AfterSavedHook;
use SuperV\Platform\Domains\Resource\Resource;

class OrdersSaving implements AfterSavedHook
{
    public function after(EntryContract $entry, Resource $resource)
    {
        $entry->update(['title' => $entry->title.' After']);
    }
}