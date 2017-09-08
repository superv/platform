<?php

namespace SuperV\Platform\Domains\Task;

use SuperV\Platform\Domains\Task\Table\TaskTableBuilder;

class TaskManifest
{
    public function handle()
    {
        return [
            'port'  => 'acp',
            'pages' => [
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
            ],

        ];
    }
}