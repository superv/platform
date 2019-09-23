<?php

namespace SuperV\Platform\Resources;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Hook\Contracts\ListConfigHook;
use SuperV\Platform\Domains\Resource\Resource\IndexFields;
use SuperV\Platform\Domains\Resource\Table\Contracts\Table;
use SuperV\Platform\Support\Composer\Payload;

class MediaList implements ListConfigHook
{
    public static $identifier = 'platform.media.lists:default';

    public function config(Table $table, IndexFields $fields)
    {
        $fields->hideLabel();

        $fields->showFirst('owner')->copyToFilters();

        $fields->add(['type' => 'file', 'name' => 'image', 'identifier' => 'image'])
               ->setCallback('table.composing', function (Payload $payload, EntryContract $entry) {
                   $payload->set('image_url', url('storage/'.$entry->filename));
               });
    }
}
