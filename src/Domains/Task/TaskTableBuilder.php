<?php

namespace SuperV\Platform\Domains\Task;

use SuperV\Platform\Domains\Table\TableBuilder;

class TaskTableBuilder extends TableBuilder
{
    protected $model = Task::class;

    protected $columns = [
        'id'     => [
            'attr'    => 'id',
            'heading' => 'ID',
        ],
        'title'  => [
            'attr'    => 'title',
            'heading' => 'Title',
        ],
        'status' => [
            'attr'    => 'status',
            'heading' => 'Status',
        ],
    ];

    public function getFilters(): array
    {
        return [
            [
                'attr'        => 'status',
                'type'        => 'select',
                'placeholder' => 'Task Status',
                'options'     => collect(TaskStatus::all())->map(
                    function ($label, $value) {
                        return [
                            'label' => $label,
                            'value' => $value,
                        ];
                    }),
            ],
        ];
    }
}