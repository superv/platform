<?php

namespace SuperV\Platform\Domains\Resource\Listeners;

use SuperV\Platform\Domains\Resource\Form\FormFactory;
use SuperV\Platform\Domains\Resource\ResourceModel;

class CreatePlatformResourceForms
{
    public function handle()
    {
        $platformResources = ResourceModel::query()->where('handle', 'LIKE', 'sv_%')->get();

        $platformResources->map(function (ResourceModel $model) {
            FormFactory::createForResource($model->getHandle());
        });
    }
}
