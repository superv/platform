<?php

namespace SuperV\Platform\Domains\Resource\Listeners;

use SuperV\Platform\Domains\Resource\Form\FormFactory;
use SuperV\Platform\Domains\Resource\ResourceModel;

class CreatePlatformResourceForms
{
    public function handle()
    {
        $platformResources = ResourceModel::query()->where('namespace', 'platform')->get();

        $platformResources->map(function (ResourceModel $model) {
            if ($model->getIdentifier() === 'platform::users') {
                return;
            }
            FormFactory::createForResource($model->getIdentifier());
        });
    }
}
