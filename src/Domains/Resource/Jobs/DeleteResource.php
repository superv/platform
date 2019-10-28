<?php

namespace SuperV\Platform\Domains\Resource\Jobs;

use SuperV\Platform\Domains\Auth\Access\Action;
use SuperV\Platform\Domains\Resource\Nav\Section;
use SuperV\Platform\Domains\Resource\ResourceModel;
use SuperV\Platform\Support\Dispatchable;

class DeleteResource
{
    use Dispatchable;

    /**
     * @var \SuperV\Platform\Domains\Resource\ResourceModel
     */
    protected $resourceEntry;

    public function __construct($resource)
    {
        if (is_string($resource)) {
            $resource = ResourceModel::withIdentifier($resource);
        }

        $this->resourceEntry = $resource;
    }

    public function handle()
    {
        if (! $this->resourceEntry) {
            return;
        }

        if ($this->resourceEntry->identifier()->isNamespace('platform')) {
            return;
        }

        $this->resourceEntry->delete();
        $this->resourceEntry->wipeCache();

        Section::query()->where('resource_id', $this->resourceEntry->getId())->delete();

        Action::query()->where('slug', 'LIKE', $this->resourceEntry->getIdentifier().'%')->delete();
        Action::query()->where('namespace', $this->resourceEntry->getIdentifier().'.fields')->delete();
    }
}
