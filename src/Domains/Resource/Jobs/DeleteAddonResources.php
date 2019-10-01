<?php

namespace SuperV\Platform\Domains\Resource\Jobs;

use SuperV\Platform\Domains\Addon\Events\AddonUninstallingEvent;
use SuperV\Platform\Domains\Auth\Access\Action;
use SuperV\Platform\Domains\Resource\Nav\Section;
use SuperV\Platform\Domains\Resource\ResourceModel;

class DeleteAddonResources
{
    public function handle(AddonUninstallingEvent $event)
    {
        $addon = $event->addon;

        ResourceModel::query()
                     ->where('namespace', $addon->getIdentifier())
                     ->get()->map(function (ResourceModel $resource) {
                DeleteResource::dispatch($resource);
            });

        Section::query()
               ->where('namespace', $addon->getIdentifier())
               ->get()->map->delete();

        Action::query()->where('namespace', $addon->getIdentifier())->delete();
    }
}
