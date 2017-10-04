<?php

namespace SuperV\Platform\Domains\Entry\Generic;

use SuperV\Platform\Domains\Entry\EntryModel as BaseEntryModel;

class GenericEntryModel extends BaseEntryModel
{
    protected $table = 'platform_entries';

    protected $hasUUID = true;

    public $incrementing = false;

    public function _model()
    {
        return $this->belongsTo(GenericModelModel::class, 'model_id');
    }

}