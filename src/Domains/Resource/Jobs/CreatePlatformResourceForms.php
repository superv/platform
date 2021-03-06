<?php

namespace SuperV\Platform\Domains\Resource\Jobs;

use SuperV\Platform\Domains\Resource\Form\FormRepository;
use SuperV\Platform\Domains\Resource\ResourceModel;
use SuperV\Platform\Support\Dispatchable;

class CreatePlatformResourceForms
{
    use Dispatchable;

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
