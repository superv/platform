<?php

namespace SuperV\Platform\Http\Controllers\Entry;

use SuperV\Platform\Domains\Entry\Generic\GenericEntryModel;
use SuperV\Platform\Http\Controllers\BasePlatformController;

class EntriesController extends BasePlatformController
{
    public function show($entryId, GenericEntryModel $entries)
    {
        /** @var GenericEntryModel $entry */
        $genericEntry = $entries->find($entryId);

        $model = $genericEntry->_model->model;

        return response()->json(['data' => ['entry' => $model::find($genericEntry->link_id)]]);
    }

    public function patch($entryId, GenericEntryModel $entries)
    {
        /** @var GenericEntryModel $entry */
        $genericEntry = $entries->find($entryId);

        $model = $genericEntry->_model->model;

        $entry = $model::find($genericEntry->link_id);

        $entry->fill($this->request->except(['entry_id', 'id']))->save();



        return [];
    }
}