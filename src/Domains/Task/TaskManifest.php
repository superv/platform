<?php

namespace SuperV\Platform\Domains\Task;

use SuperV\Platform\Domains\Manifest\ModelManifest;
use SuperV\Platform\Domains\Task\Model\TaskModel;
use SuperV\Platform\Domains\Task\Table\TaskTableBuilder;
use SuperV\Platform\Domains\UI\Table\TableBuilder;

class TaskManifest extends ModelManifest
{
    public function getPages()
    {
        return [
            'index' => [
                'navigation' => true,
                'icon'       => 'server',
                'title'      => 'Tasks',
                'route'      => 'superv::tasks.index',
                'url'        => 'platform/tasks',
                'handler'    => function (TaskTableBuilder $builder) {

                    return $builder->render();
                },
                'buttons'    => [
                    'create',
                ],
            ],

        ];
    }
}