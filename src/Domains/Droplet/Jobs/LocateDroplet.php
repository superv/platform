<?php

namespace SuperV\Platform\Domains\Droplet\Jobs;

use SuperV\Platform\Domains\Droplet\Model\DropletModel;

class LocateDroplet
{
    /** @var \SuperV\Platform\Domains\Droplet\Model\DropletModel */
    protected $model;

    protected $paths = [
        'workbench',
        'droplets',
    ];

    public function __construct(DropletModel $model)
    {
        $this->model = $model;
    }

    public function handle()
    {
        $clues = [$this->model->path];

        foreach ($this->paths as $path) {
            foreach ($clues as $clue) {
                $path = starts_with($clue, $this->paths) ? $clue : "{$path}/{$clue}";
                if (is_dir(base_path($path))) {
                    $this->model->setPath($path);

                    return;
                }
            }
        }

        throw new \Exception("Droplet could not be located: {$this->model->name}");
    }
}
