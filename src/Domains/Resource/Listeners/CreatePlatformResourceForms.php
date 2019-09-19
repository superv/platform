<?php

namespace SuperV\Platform\Domains\Resource\Listeners;

use SuperV\Platform\Domains\Resource\Form\FormRepository;
use SuperV\Platform\Domains\Resource\ResourceModel;

class CreatePlatformResourceForms
{
    public function handle()
    {
        $platformResources = ResourceModel::query()->where('namespace', 'platform')->get();

        $platformResources->map(function (ResourceModel $model) {
            if ($model->getIdentifier() === 'platform.users') {
                return;
            }
            FormRepository::createForResource($model->getIdentifier());
        });
    }
}
