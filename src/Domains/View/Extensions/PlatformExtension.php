<?php

namespace SuperV\Platform\Domains\View\Extensions;

use Illuminate\Foundation\Bus\DispatchesJobs;
use SuperV\Platform\Contracts\Navigation\Navigation;
use SuperV\Platform\Domains\Model\EloquentCriteria;
use SuperV\Platform\Domains\Setting\JSON;
use SuperV\Platform\Domains\Task\Model\TaskModel;
use SuperV\Platform\Domains\Task\Task;
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
                                                  ->where('parent_id', null)
                                                  ->orderBy('id', 'DESC'))
                );
            }),
            new Twig_SimpleFunction('buttons', function ($buttons) {
                return $this->dispatch(new MakeButtons($buttons));
            }, [
                'is_safe' => ['html'],
            ]),
            new \Twig_SimpleFunction(
                    'asset_*',
                    function ($name) {
                        $arguments = array_slice(func_get_args(), 1);

                        return call_user_func_array([superv('assets'), camel_case($name)], $arguments);
                    }, ['is_safe' => ['html']]
                ),
            new Twig_SimpleFunction('navigation', function ($key = null) {
                if (! $key) {
                    return app(Navigation::class);
                }

                $id =  (new JSON(storage_path("superv/compiled/navigation/index.json")))->get($key);
                $nav = (new JSON(storage_path("superv/compiled/navigation/{$id}.json")));

                return $nav->get('sections');
            }),
        ];
    }
}
