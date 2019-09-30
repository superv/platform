<?php

namespace Tests\Platform\Domains\Resource\Http\Controllers;

use SuperV\Platform\Domains\Database\Model\Contracts\EntryContract;
use SuperV\Platform\Testing\HelperComponent;

trait ResponseHelper
{
    public function getResourceView(EntryContract $entry): HelperComponent
    {
        $response = $this->getJsonUser($entry->router()->view());

        return HelperComponent::fromArray($response->decodeResponseJson('data'));
    }
}
