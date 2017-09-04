<?php

namespace SuperV\Platform\Domains\Task\Model;

use SuperV\Platform\Domains\Task\Task;
use SuperV\Platform\Domains\Entry\EntryPresenter;

class TaskPresenter extends EntryPresenter
{
    public function statusLabel($class = '')
    {
        $status = $this->object->getStatus();

        switch ($status) {
            case Task::COMPLETED:
                $color = 'success';
                $label = 'Completed';
                $icon = 'fa fa-check';
                break;

            case Task::RUNNING:
                $color = 'warning';
                $label = 'Running';
                $icon = 'fa fa-spinner fa-pulse';
                break;

            case Task::PENDING:
                $color = 'info';
                $label = 'Pending';
                $icon = 'fa fa-clock-o';
                break;

            case Task::FAILED:
                $color = 'danger';
                $label = 'Failed';

                $icon = 'fa fa-warning';
                break;

            default:
                $color = 'default';
                $label = 'Unknown';
                $icon = 'fa fa-question';
        }

        $icon = "<i class='{$icon}'></i>";
        $label = "<span>{$label}</span>";

        return "<span class='{$class} status label label-{$color}'>{$icon} {$label}</span>";
    }
}
