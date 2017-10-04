<?php

namespace SuperV\Platform\Http\Controllers\Entry;

use SuperV\Platform\Domains\Entry\Generic\GenericEntryModel;
use SuperV\Platform\Http\Controllers\BasePlatformController;

class EntriesController extends BasePlatformController
{
    public function show($entryId, GenericEntryModel $entries)
    {
        /** @var GenericEntryModel $entry */
        $entry = $entries->find($entryId);

        $model = $entry->_model->model;

        return response()->json(['data' => ['entry' => $model::find($entry->link_id)]]);
    }
}