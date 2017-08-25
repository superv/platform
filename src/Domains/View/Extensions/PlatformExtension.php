<?php namespace SuperV\Platform\Domains\View\Extensions;

use Illuminate\Foundation\Bus\DispatchesJobs;
use SuperV\Platform\Domains\Model\EloquentCriteria;
use SuperV\Platform\Domains\Task\Model\TaskModel;
use SuperV\Platform\Domains\Task\Task;
use SuperV\Platform\Domains\UI\Button\Features\MakeButtons;
use SuperV\Platform\Support\Decorator;
use Twig_SimpleFunction;

class PlatformExtension extends \Twig_Extension
{
    use DispatchesJobs;

    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('tasks', function () {
                return (new Decorator())->decorate(
                    new EloquentCriteria(TaskModel::query()
                                                  ->where('status', '!=', Task::COMPLETED)
                                                  ->where('parent_id',null)->orderBy('id', 'DESC'))
                );
            }),
            new Twig_SimpleFunction('buttons', function ($buttons) {
                return $this->dispatch(new MakeButtons($buttons));
            }, [
                'is_safe' => ['html'],
            ]),
        ];
    }
}