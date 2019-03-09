<?php

namespace SuperV\Platform\Extensions;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Domains\Resource\Extension\Contracts\ExtendsResource;
use SuperV\Platform\Support\Composer\Payload;

class MediaExtension implements ExtendsResource
{
    public function extend(\SuperV\Platform\Domains\Resource\Resource $resource)
    {
        $fields = $resource->indexFields();
        $fields->hideLabel();

        $fields->showFirst('owner')->copyToFilters();

        $fields->add(['type' => 'file', 'name' => 'image'])
               ->setCallback('table.composing', function (Payload $payload, EntryContract $entry) {
                   $payload->set('image_url', url('storage/'.$entry->filename));
               });
//        $fields->show('email');
//        $fields->show('account')->copyToFilters(['default_value' => 1]);
//
//        $resource->searchable(['email']);
    }

    public function extends(): string
    {
        return 'sv_media';
    }
}