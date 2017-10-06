<?php

namespace SuperV\Platform\Http\Controllers\Entry\Relations;

use SuperV\Modules\Supreme\Domains\Server\Model\AccountModel;
use SuperV\Platform\Domains\Entry\Generic\GenericEntryModel;
use SuperV\Platform\Http\Controllers\BasePlatformController;

class OptionsController extends BasePlatformController
{
    public function show($entryId, $relation, GenericEntryModel $entries)
    {
        $options = [];

        $genericEntry = $entries->find($entryId);
        $model = $genericEntry->_model->model;
        $entry = $model::find($genericEntry->link_id);

        // $entry->getRelation($relation);
        $related = AccountModel::class;
        $related = new $related;
        $options = $related->newQuery()
                           ->get()
                           ->map(function ($item) {
                               return [
                                   'text'  => $item->getAttribute($item->getTitleColumn()),
                                   'value' => $item->id,
                               ];
                           })
                           ->toArray();

        return ['data' => compact('options')];
    }
}