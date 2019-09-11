<?php

namespace SuperV\Platform\Domains\Resource\Jobs;

use SuperV\Platform\Domains\Resource\Nav\Section;
use SuperV\Platform\Domains\Resource\ResourceModel;
use SuperV\Platform\Support\Dispatchable;

class DeleteResource
{
    use Dispatchable;

    /**
     * @var string
     */
    protected $resourceHandle;

    public function __construct(string $resourceHandle)
    {
        $this->resourceHandle = $resourceHandle;
    }

    public function handle()
    {
        if (! $resourceEntry = ResourceModel::withHandle($this->resourceHandle)) {
            return;
        }
        $resourceEntry->delete();
        $resourceEntry->wipeCache();

        Section::query()->where('resource_id', $resourceEntry->getId())->delete();
    }
}
